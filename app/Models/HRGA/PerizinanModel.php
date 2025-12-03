<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PerizinanModel extends Model
{
    protected $table = 'hrga_perizinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'jenis_izin', 'tanggal_mulai', 'tanggal_selesai',
        'alasan', 'status', 'approved_at', 'rejected_at', 'created_at'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    public function getAllWithKaryawan()
    {
        return $this->db->table('hrga_perizinan p')
            ->select('p.*, k.nama_lengkap, k.nip, k.jabatan, k.status_karyawan')
            ->join('hrga_karyawan k', 'k.id = p.karyawan_id')
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getByIdWithKaryawan($id)
    {
        return $this->db->table('hrga_perizinan p')
            ->select('p.*, k.nama_lengkap, k.nip, k.jabatan, k.status_karyawan, k.tanggal_masuk')
            ->join('hrga_karyawan k', 'k.id = p.karyawan_id')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getPerizinanPendingCount()
    {
        return $this->db->table('hrga_perizinan')
            ->where('status', 'pending')
            ->countAllResults();
    }
}