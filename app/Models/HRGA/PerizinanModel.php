<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PerizinanModel extends Model
{
    protected $table = 'hrga_perizinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'jenis_izin', 'tanggal_mulai', 'tanggal_selesai',
        'alasan', 'status', 'approved_at', 'rejected_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    public function getAllWithKaryawan()
    {
        return $this->db->table('hrga_perizinan p')
            ->select('p.*, k.nama_lengkap, k.nip')
            ->join('hrga_karyawan k', 'k.id = p.karyawan_id')
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPerizinanPendingCount()
    {
        return $this->db->table('hrga_perizinan')
            ->where('status', 'pending')
            ->countAllResults();
    }
}