<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'order_number', 'total_amount', 'status', 'alamat_pengiriman', 'catatan'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function createOrder($userId, $cartItems, $alamat, $catatan = '')
    {
        $this->db->transStart();

        try {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $harga = $item['diskon'] > 0 ? 
                        $item['harga'] * (1 - $item['diskon']/100) : 
                        $item['harga'];
                $totalAmount += $harga * $item['quantity'];
            }

            $orderData = [
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'alamat_pengiriman' => $alamat,
                'catatan' => $catatan,
                'status' => 'pending'
            ];

            $orderId = $this->insert($orderData);

            $orderItems = [];
            foreach ($cartItems as $item) {
                $harga = $item['diskon'] > 0 ? 
                        $item['harga'] * (1 - $item['diskon']/100) : 
                        $item['harga'];
                
                $orderItems[] = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'harga' => $harga,
                    'subtotal' => $harga * $item['quantity']
                ];
            }

            $this->db->table('order_items')->insertBatch($orderItems);

            $this->db->transComplete();

            return $this->db->transStatus() ? $orderId : false;

        } catch (\Exception $e) {
            $this->db->transRollback();
            return false;
        }
    }

    public function getUserOrders($userId)
    {
        return $this->where('user_id', $userId)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    public function getOrderWithItems($orderId)
    {
        $order = $this->find($orderId);
        if (!$order) {
            return null;
        }

        $builder = $this->db->table('order_items oi');
        $builder->select('oi.*, p.nama_produk, p.slug, p.gambar');
        $builder->join('products p', 'p.id = oi.product_id');
        $builder->where('oi.order_id', $orderId);
        
        $order['items'] = $builder->get()->getResultArray();

        return $order;
    }
}