<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PerawatanModel extends Model
{
    protected $table = 'perawatan_gedung';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode_perawatan', 'deskripsi', 'lokasi', 'tanggal_perawatan', 'biaya', 'status', 'created_at'];
    protected $useTimestamps = false;

    public function getAllPerawatan()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
}