<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class InventarisModel extends Model
{
    protected $table = 'inventaris';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode_barang', 'nama_barang', 'kategori', 'jumlah', 'kondisi', 'lokasi', 'created_at'];
    protected $useTimestamps = false;

    public function getAllInventaris()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
}