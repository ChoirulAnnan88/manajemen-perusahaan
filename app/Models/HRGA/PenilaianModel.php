<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PenilaianModel extends Model
{
    protected $table = 'hrga_penilaian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'periode', 'nilai_produktivitas', 'nilai_kedisiplinan',
        'nilai_kerjasama', 'nilai_total', 'catatan'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    public function getAllWithKaryawan()
    {
        return $this->db->table('hrga_penilaian p')
            ->select('p.*, k.nama_lengkap, k.nip')
            ->join('hrga_karyawan k', 'k.id = p.karyawan_id')
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}