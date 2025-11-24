<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\OrderModel;
use App\Models\UserModel;
use App\Models\CategoryModel;

class Dashboard extends BaseController
{
    protected $productModel;
    protected $orderModel;
    protected $userModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->orderModel = new OrderModel();
        $this->userModel = new UserModel();
        $this->categoryModel = new CategoryModel();
        
        helper(['number', 'text']);
    }

    public function index()
    {
        // Check admin role
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        try {
            // 🆕 TAMBAHAN: Enhanced statistics
            $monthlyRevenue = $this->orderModel->select("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as revenue")
                ->where('status', 'delivered')
                ->groupBy('month')
                ->orderBy('month', 'DESC')
                ->findAll(6);

            $topProducts = $this->productModel->select('products.*, COUNT(order_items.id) as total_orders')
                ->join('order_items', 'order_items.product_id = products.id', 'left')
                ->groupBy('products.id')
                ->orderBy('total_orders', 'DESC')
                ->findAll(5);

            $recentActivities = $this->orderModel->select('orders.*, users.username, users.email')
                ->join('users', 'users.id = orders.user_id')
                ->orderBy('orders.created_at', 'DESC')
                ->findAll(8);

            $data = [
                'title' => 'Admin Dashboard',
                'totalProducts' => $this->productModel->countAll(),
                'totalOrders' => $this->orderModel->countAll(),
                'totalUsers' => $this->userModel->where('role', 'customer')->countAllResults(),
                'totalCategories' => $this->categoryModel->countAll(),
                'recentOrders' => $this->orderModel->orderBy('created_at', 'DESC')->findAll(5),
                // 🆕 TAMBAHAN DATA BARU
                'monthlyRevenue' => array_reverse($monthlyRevenue),
                'topProducts' => $topProducts,
                'recentActivities' => $recentActivities,
                'pendingOrders' => $this->orderModel->where('status', 'pending')->countAllResults(),
                'lowStockProducts' => $this->productModel->where('stok <', 5)->countAllResults(),
                'todayRevenue' => $this->orderModel->selectSum('total_amount')
                    ->where('DATE(created_at)', date('Y-m-d'))
                    ->where('status', 'delivered')
                    ->get()
                    ->getRow()->total_amount ?? 0
            ];

            return view('admin/dashboard', $data);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard error: ' . $e->getMessage());
            return redirect()->to('/admin')->with('error', 'Terjadi kesalahan saat memuat dashboard');
        }
    }
    
    // 🆕 TAMBAHAN METHOD BARU
    
    /**
     * Get dashboard statistics via AJAX
     */
    public function getStats()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        try {
            $stats = [
                'total_products' => $this->productModel->countAll(),
                'total_orders' => $this->orderModel->countAll(),
                'total_users' => $this->userModel->where('role', 'customer')->countAllResults(),
                'pending_orders' => $this->orderModel->where('status', 'pending')->countAllResults(),
                'today_orders' => $this->orderModel->where('DATE(created_at)', date('Y-m-d'))->countAllResults(),
                'low_stock' => $this->productModel->where('stok <', 5)->countAllResults(),
                'total_revenue' => $this->orderModel->selectSum('total_amount')
                    ->where('status', 'delivered')
                    ->get()
                    ->getRow()->total_amount ?? 0
            ];
            
            return $this->response->setJSON(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get chart data for dashboard
     */
    public function getChartData()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        try {
            // Sales data for the last 6 months
            $salesData = $this->orderModel->select("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as orders, SUM(total_amount) as revenue")
                ->where('status', 'delivered')
                ->groupBy('month')
                ->orderBy('month', 'DESC')
                ->findAll(6);

            // Order status distribution
            $statusData = $this->orderModel->select('status, COUNT(*) as count')
                ->groupBy('status')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'sales_data' => array_reverse($salesData),
                'status_data' => $statusData
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}