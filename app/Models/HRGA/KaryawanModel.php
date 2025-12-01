<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class KaryawanModel extends Model
{
    protected $table = 'karyawan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nip', 'nama', 'divisi', 'jabatan', 'tanggal_masuk', 'status', 'gaji_pokok', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function getAllKaryawan()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    public function getDivisi()
    {
        // Jika tabel divisi ada di database Anda
        if ($this->db->tableExists('divisi')) {
            return $this->db->table('divisi')
                ->orderBy('nama_divisi', 'ASC')
                ->get()
                ->getResultArray();
        }
        
        // Fallback jika tidak ada tabel divisi
        return [
            ['id' => 1, 'nama_divisi' => 'HRGA'],
            ['id' => 2, 'nama_divisi' => 'IT'],
            ['id' => 3, 'nama_divisi' => 'Finance'],
            ['id' => 4, 'nama_divisi' => 'Marketing'],
            ['id' => 5, 'nama_divisi' => 'Operations']
        ];
    }

    public function getWithDivisi($id)
    {
        return $this->find($id);
    }

    public function countAllKaryawan()
    {
        return $this->countAll();
    }
}