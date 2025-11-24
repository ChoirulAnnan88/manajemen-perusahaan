<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\CartModel;

class Checkout extends BaseController
{
    protected $orderModel;
    protected $orderItemModel;
    protected $cartModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->cartModel = new CartModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userId = session()->get('user_id');
        $cartItems = $this->cartModel->getCartItems($userId);
        
        if (empty($cartItems)) {
            return redirect()->to('/cart')->with('error', 'Keranjang belanja kosong');
        }

        $data = [
            'title' => 'Checkout',
            'cartItems' => $cartItems,
            'cartTotal' => $this->cartModel->getCartTotal($userId)
        ];

        return view('checkout/index', $data);
    }

    public function process()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userId = session()->get('user_id');
        $cartItems = $this->cartModel->getCartItems($userId);

        if (empty($cartItems)) {
            return redirect()->to('/cart')->with('error', 'Keranjang belanja kosong');
        }

        // Generate order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Calculate total
        $totalAmount = $this->cartModel->getCartTotal($userId);

        // Create order
        $orderData = [
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'total_amount' => $totalAmount,
            'alamat_pengiriman' => $this->request->getPost('alamat'),
            'catatan' => $this->request->getPost('catatan')
        ];

        if ($orderId = $this->orderModel->insert($orderData)) {
            // Create order items
            foreach ($cartItems as $item) {
                $harga = $item['diskon'] > 0 ? 
                        $item['harga'] * (1 - $item['diskon']/100) : 
                        $item['harga'];
                
                $this->orderItemModel->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'harga' => $harga,
                    'subtotal' => $harga * $item['quantity']
                ]);
            }

            // Clear cart
            $this->cartModel->where('user_id', $userId)->delete();

            return redirect()->to("/order/complete/$orderNumber")->with('success', 'Pesanan berhasil dibuat!');
        }

        return redirect()->back()->with('error', 'Gagal membuat pesanan');
    }

    public function complete($orderNumber)
    {
        $order = $this->orderModel->where('order_number', $orderNumber)->first();
        
        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Order Complete',
            'order' => $order
        ];

        return view('checkout/complete', $data);
    }
}