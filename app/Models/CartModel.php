<?php

namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table            = 'cart';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'product_id', 'quantity'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    public function getCartItems($userId)
    {
        $builder = $this->db->table('cart c');
        $builder->select('c.*, p.nama_produk, p.harga, p.diskon, p.gambar, p.stok');
        $builder->join('products p', 'p.id = c.product_id');
        $builder->where('c.user_id', $userId);
        
        return $builder->get()->getResultArray();
    }

    public function getCartTotal($userId)
    {
        $items = $this->getCartItems($userId);
        $total = 0;
        
        foreach ($items as $item) {
            $harga = $item['diskon'] > 0 ? 
                    $item['harga'] * (1 - $item['diskon']/100) : 
                    $item['harga'];
            $total += $harga * $item['quantity'];
        }
        
        return $total;
    }

    public function updateCartItem($userId, $productId, $quantity)
    {
        if ($quantity <= 0) {
            return $this->where('user_id', $userId)
                       ->where('product_id', $productId)
                       ->delete();
        }

        return $this->where('user_id', $userId)
                   ->where('product_id', $productId)
                   ->set('quantity', $quantity)
                   ->update();
    }
}