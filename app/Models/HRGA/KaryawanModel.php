<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class KaryawanModel extends Model
{
    protected $table = 'hrga_karyawan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nip', 'nama_lengkap', 'divisi_id', 'jabatan',
        'tanggal_masuk', 'status_karyawan', 'gaji_pokok'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    public function findAllWithDivisi()
    {
        return $this->db->table('hrga_karyawan k')
            ->select('k.*, d.nama_divisi')
            ->join('divisi d', 'd.id = k.divisi_id', 'left')
            ->orderBy('k.nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();
    }
}