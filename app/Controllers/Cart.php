<?php

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\ProductModel;

class Cart extends BaseController
{
    protected $cartModel;
    protected $productModel;
    protected $session;

    public function __construct()
    {
        $this->cartModel = new CartModel();
        $this->productModel = new ProductModel();
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->session->get('user_id');
        $cartItems = $this->cartModel->getCartItems($userId);
        $cartTotal = $this->cartModel->getCartTotal($userId);

        $data = [
            'title' => 'Keranjang Belanja',
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal
        ];

        return view('cart/index', $data);
    }

    public function add()
    {
    if (!$this->session->get('logged_in')) {
        return redirect()->to('/auth/login');
    }

    $userId = $this->session->get('user_id');
    $productId = $this->request->getPost('product_id');
    $quantity = $this->request->getPost('quantity') ?? 1;

    // Validasi product exists
    $product = $this->productModel->find($productId);
    if (!$product) {
        return redirect()->back()->with('error', 'Produk tidak ditemukan');
    }

    // Cek stok
    if ($product['stok'] < $quantity) {
        return redirect()->back()->with('error', 'Stok tidak mencukupi');
    }

    // Cek apakah produk sudah ada di keranjang
    $existingItem = $this->cartModel->where('user_id', $userId)
                                   ->where('product_id', $productId)
                                   ->first();

    if ($existingItem) {
        $newQuantity = $existingItem['quantity'] + $quantity;
        $this->cartModel->updateCartItem($userId, $productId, $newQuantity);
    } else {
        $this->cartModel->save([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
    }

    return redirect()->to('/cart')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    } 

    public function update()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->session->get('user_id');
        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');

        $this->cartModel->updateCartItem($userId, $productId, $quantity);

        return redirect()->to('/cart')->with('success', 'Keranjang berhasil diperbarui');
    }

    public function remove($productId)
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $userId = $this->session->get('user_id');
        
        $this->cartModel->where('user_id', $userId)
                       ->where('product_id', $productId)
                       ->delete();

        return redirect()->to('/cart')->with('success', 'Produk berhasil dihapus dari keranjang');
    }
}