<?php

namespace App\Controllers;

use App\Models\OrderModel;

class Order extends BaseController
{
    protected $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
    }

    public function history()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userId = session()->get('user_id');
        $orders = $this->orderModel->where('user_id', $userId)
                                  ->orderBy('created_at', 'DESC')
                                  ->findAll();

        $data = [
            'title' => 'Riwayat Pesanan',
            'orders' => $orders
        ];

        return view('order/history', $data);
    }

    public function detail($orderNumber)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userId = session()->get('user_id');
        $order = $this->orderModel->getOrderWithItems($orderNumber, $userId);

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Detail Pesanan #' . $orderNumber,
            'order' => $order
        ];

        return view('order/detail', $data);
    }
}