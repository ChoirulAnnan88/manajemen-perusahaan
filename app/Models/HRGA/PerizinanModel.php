<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PerizinanModel extends Model
{
    protected $table = 'perizinan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['karyawan_id', 'jenis_izin', 'tanggal_mulai', 'tanggal_selesai', 'alasan', 'status', 'approved_at', 'rejected_at', 'created_at'];
    protected $useTimestamps = false;

    public function getAllWithKaryawan()
    {
        return $this->db->table('perizinan p')
            ->select('p.*, k.nip, k.nama')
            ->join('karyawan k', 'k.id = p.karyawan_id', 'left')
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPerizinanPendingCount()
    {
        return $this->db->table('perizinan')
            ->where('status', 'pending')
            ->countAllResults();
    }
}