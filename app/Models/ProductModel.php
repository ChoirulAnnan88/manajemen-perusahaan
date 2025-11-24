<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['kategori_id', 'nama_produk', 'slug', 'deskripsi', 'harga', 'stok', 'gambar', 'berat', 'diskon', 'is_active'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getProductsWithCategory($limit = null, $offset = 0)
    {
        $builder = $this->db->table('products p');
        $builder->select('p.*, c.nama_kategori');
        $builder->join('categories c', 'c.id = p.kategori_id', 'left');
        $builder->where('p.is_active', 1);
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getProductBySlug($slug)
    {
        $builder = $this->db->table('products p');
        $builder->select('p.*, c.nama_kategori');
        $builder->join('categories c', 'c.id = p.kategori_id', 'left');
        $builder->where('p.slug', $slug);
        $builder->where('p.is_active', 1);
        
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getProductsByCategory($categoryId)
    {
        $builder = $this->db->table('products p');
        $builder->select('p.*, c.nama_kategori');
        $builder->join('categories c', 'c.id = p.kategori_id', 'left');
        $builder->where('p.kategori_id', $categoryId);
        $builder->where('p.is_active', 1);
        
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function searchProducts($keyword)
    {
        $builder = $this->db->table('products p');
        $builder->select('p.*, c.nama_kategori');
        $builder->join('categories c', 'c.id = p.kategori_id', 'left');
        $builder->groupStart()
                ->like('p.nama_produk', $keyword)
                ->orLike('p.deskripsi', $keyword)
                ->groupEnd();
        $builder->where('p.is_active', 1);
        
        $query = $builder->get();
        return $query->getResultArray();
    }

    // Method untuk mendapatkan produk terbaru
    public function getNewestProducts($limit = 8)
    {
        return $this->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit);
    }

    // Method untuk manual connection (optional)
    public function initConnection($db)
    {
        $this->db = $db;
        return $this;
    }
}