<?php
namespace App\Models;
use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['order_id', 'product_id', 'quantity', 'harga', 'subtotal'];
    protected $useTimestamps = false;

    public function getOrderItems($orderId)
    {
        $builder = $this->db->table('order_items oi');
        $builder->select('oi.*, p.nama_produk, p.slug, p.gambar');
        $builder->join('products p', 'p.id = oi.product_id');
        $builder->where('oi.order_id', $orderId);
        return $builder->get()->getResultArray();
    }
}