<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PenilaianModel extends Model
{
    protected $table = 'penilaian_kinerja';
    protected $primaryKey = 'id';
    protected $allowedFields = ['karyawan_id', 'periode', 'produktivitas', 'kedisiplinan', 'kerjasama', 'total_nilai', 'catatan', 'created_at'];
    protected $useTimestamps = false;

    public function getAllWithKaryawan()
    {
        return $this->db->table('penilaian_kinerja p')
            ->select('p.*, k.nip, k.nama')
            ->join('karyawan k', 'k.id = p.karyawan_id', 'left')
            ->orderBy('p.periode', 'DESC')
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}