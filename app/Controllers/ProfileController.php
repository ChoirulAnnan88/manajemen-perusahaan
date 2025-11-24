<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\OrderModel;

class ProfileController extends BaseController
{
    protected $userModel;
    protected $orderModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->orderModel = new OrderModel();
        
        helper(['form']);
    }

    private function checkAuth()
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Anda harus login terlebih dahulu!');
            return redirect()->to('/auth/login');
        }
        return true;
    }

    public function index()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) return $authCheck;

        try {
            $userId = session()->get('user_id');
            $user = $this->userModel->find($userId);

            if (!$user) {
                session()->setFlashdata('error', 'User tidak ditemukan!');
                return redirect()->to('/');
            }

            // FIX: Simple orders query
            $orders = $this->orderModel->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->findAll(5);

            $data = [
                'title' => 'Profil Saya - Toko Online',
                'user' => $user,
                'orders' => $orders,
                'total_orders' => $this->orderModel->where('user_id', $userId)->countAllResults()
            ];

            return view('profile/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Profile error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function edit()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) return $authCheck;

        try {
            $userId = session()->get('user_id');
            $user = $this->userModel->find($userId);

            if (!$user) {
                session()->setFlashdata('error', 'User tidak ditemukan!');
                return redirect()->to('/');
            }

            $data = [
                'title' => 'Edit Profil - Toko Online',
                'user' => $user,
                'validation' => \Config\Services::validation()
            ];

            return view('profile/edit', $data);

        } catch (\Exception $e) {
            log_message('error', 'Profile edit error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function update()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) return $authCheck;

        if (!$this->request->is('post')) {
            return redirect()->back()->with('error', 'Method tidak diizinkan!');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/')->with('error', 'User tidak ditemukan!');
        }

        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|max_length[255]|is_unique[users.email,id,{$userId}]",
            'telepon' => 'permit_empty|min_length[10]|max_length[15]',
            'alamat' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'email' => $this->request->getPost('email'),
                'telepon' => $this->request->getPost('telepon'),
                'alamat' => $this->request->getPost('alamat')
            ];

            if ($this->userModel->update($userId, $data)) {
                // Update session
                $updatedUser = $this->userModel->find($userId);
                session()->set([
                    'email' => $updatedUser['email'],
                    'nama_lengkap' => $updatedUser['nama_lengkap']
                ]);

                return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal memperbarui profil.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error updating profile: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function orders()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) return $authCheck;

        try {
            $userId = session()->get('user_id');
            
            $orders = $this->orderModel->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            $data = [
                'title' => 'Riwayat Pesanan - Toko Online',
                'orders' => $orders,
                'total_orders' => count($orders)
            ];

            return view('profile/orders', $data);

        } catch (\Exception $e) {
            log_message('error', 'Orders history error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function orderDetail($orderId)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck !== true) return $authCheck;

        try {
            $userId = session()->get('user_id');
            $order = $this->orderModel->where(['id' => $orderId, 'user_id' => $userId])->first();

            if (!$order) {
                return redirect()->to('/profile/orders')->with('error', 'Pesanan tidak ditemukan!');
            }

            $data = [
                'title' => 'Detail Pesanan - Toko Online',
                'order' => $order
            ];

            return view('profile/order_detail', $data);

        } catch (\Exception $e) {
            log_message('error', 'Order detail error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}