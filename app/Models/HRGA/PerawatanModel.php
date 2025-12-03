<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PerawatanModel extends Model
{
    protected $table = 'hrga_perawatan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_perawatan', 'deskripsi', 'lokasi', 'tanggal_perawatan',
        'biaya', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    public function getAllPerawatan()
    {
        return $this->db->table('hrga_perawatan')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}