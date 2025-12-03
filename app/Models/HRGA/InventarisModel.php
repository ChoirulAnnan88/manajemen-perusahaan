<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class InventarisModel extends Model
{
    protected $table = 'hrga_inventaris';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_inventaris', 'nama_barang', 'kategori', 'jumlah',
        'kondisi', 'lokasi'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    public function getAllInventaris()
    {
        return $this->db->table('hrga_inventaris')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}