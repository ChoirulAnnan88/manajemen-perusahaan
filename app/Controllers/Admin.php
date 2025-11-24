<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\OrderModel;
use App\Models\UserModel;

class Admin extends BaseController
{
    protected $productModel;
    protected $categoryModel;
    protected $orderModel;
    protected $userModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->orderModel = new OrderModel();
        $this->userModel = new UserModel();
        
        helper(['form', 'text']);
    }

    /**
     * Check if user is authenticated and has admin role
     */
    private function checkAdminAccess()
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Anda harus login terlebih dahulu!');
            return redirect()->to('/auth/login');
        }

        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'Akses ditolak! Hanya administrator yang dapat mengakses halaman ini.');
            return redirect()->to('/');
        }

        return true;
    }

    public function dashboard()
    {
        $accessCheck = $this->checkAdminAccess();
        if ($accessCheck !== true) return $accessCheck;

        try {
            // FIX: Simple stats tanpa query complex
            $data = [
                'title' => 'Admin Dashboard - Toko Online',
                'total_products' => $this->productModel->countAll(),
                'total_categories' => $this->categoryModel->countAll(),
                'total_orders' => $this->orderModel->countAll(),
                'total_users' => $this->userModel->where('role', 'customer')->countAllResults(),
                'recent_orders' => $this->orderModel->orderBy('created_at', 'DESC')->findAll(5),
                'pending_orders' => $this->orderModel->where('status', 'pending')->countAllResults(),
                'today_orders' => $this->orderModel->where('DATE(created_at)', date('Y-m-d'))->countAllResults()
            ];
            
            return view('admin/dashboard', $data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function products()
    {
        $accessCheck = $this->checkAdminAccess();
        if ($accessCheck !== true) return $accessCheck;

        try {
            $search = $this->request->getGet('search');
            $category = $this->request->getGet('category');
            
            $productModel = $this->productModel;
            
            if ($search) {
                $productModel->like('nama_produk', $search);
            }
            
            if ($category) {
                $productModel->where('kategori_id', $category);
            }
            
            // FIX: Gunakan findAll() sederhana dulu
            $data = [
                'title' => 'Kelola Produk - Admin',
                'products' => $productModel->findAll(),
                'categories' => $this->categoryModel->findAll(),
                'search' => $search,
                'selected_category' => $category
            ];
            
            return view('admin/products', $data);
        } catch (\Exception $e) {
            log_message('error', 'Products error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data produk.');
        }
    }

    public function categories()
    {
        $accessCheck = $this->checkAdminAccess();
        if ($accessCheck !== true) return $accessCheck;

        try {
            // FIX: Simple categories data
            $data = [
                'title' => 'Kelola Kategori - Admin',
                'categories' => $this->categoryModel->findAll()
            ];
            
            return view('admin/categories', $data);
        } catch (\Exception $e) {
            log_message('error', 'Categories error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data kategori.');
        }
    }

    public function orders()
    {
        $accessCheck = $this->checkAdminAccess();
        if ($accessCheck !== true) return $accessCheck;

        try {
            $status = $this->request->getGet('status');
            
            $orderModel = $this->orderModel;
            
            if ($status && $status !== 'all') {
                $orderModel->where('status', $status);
            }
            
            $data = [
                'title' => 'Kelola Pesanan - Admin',
                'orders' => $orderModel->orderBy('created_at', 'DESC')->findAll(),
                'status_filter' => $status
            ];
            
            return view('admin/orders', $data);
        } catch (\Exception $e) {
            log_message('error', 'Orders error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pesanan.');
        }
    }

    public function users()
    {
        $accessCheck = $this->checkAdminAccess();
        if ($accessCheck !== true) return $accessCheck;

        try {
            $search = $this->request->getGet('search');
            $role = $this->request->getGet('role');
            
            $userModel = $this->userModel;
            
            if ($search) {
                $userModel->groupStart()
                    ->like('username', $search)
                    ->orLike('email', $search)
                    ->orLike('nama_lengkap', $search)
                    ->groupEnd();
            }
            
            if ($role && $role !== 'all') {
                $userModel->where('role', $role);
            }
            
            $data = [
                'title' => 'Kelola Users - Admin',
                'users' => $userModel->orderBy('created_at', 'DESC')->findAll(),
                'search' => $search,
                'role_filter' => $role
            ];
            
            return view('admin/users', $data);
        } catch (\Exception $e) {
            log_message('error', 'Users error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data users.');
        }
    }

    /**
     * Update order status - AJAX endpoint
     */
    public function updateOrderStatus($orderId)
    {
        $accessCheck = $this->checkAdminAccess();
        if ($accessCheck !== true) return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        try {
            $newStatus = $this->request->getJSON()->status;
            $allowedStatus = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            
            if (!in_array($newStatus, $allowedStatus)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Status tidak valid']);
            }

            $updateData = ['status' => $newStatus];
            
            if ($newStatus === 'processing') {
                $updateData['processed_at'] = date('Y-m-d H:i:s');
            } elseif ($newStatus === 'shipped') {
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            } elseif ($newStatus === 'delivered') {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
            }

            $result = $this->orderModel->update($orderId, $updateData);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Status berhasil diupdate',
                    'new_status' => $newStatus
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal update status']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Update order error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }
}