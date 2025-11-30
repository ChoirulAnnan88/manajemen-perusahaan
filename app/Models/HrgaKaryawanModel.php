<?php
namespace App\Models;

use CodeIgniter\Model;

class HrgaKaryawanModel extends Model
{
    protected $table = 'hrga_karyawan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nip', 'nama_lengkap', 'divisi_id', 'jabatan', 
        'tanggal_masuk', 'status_karyawan', 'gaji_pokok',
        'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    
    public function getKaryawanByDivisi($divisi_id = null)
    {
        if ($divisi_id) {
            return $this->where('divisi_id', $divisi_id)->findAll();
        }
        return $this->findAll();
    }
    
    public function getKaryawanAktif()
    {
        return $this->findAll();
    }
    
    public function getDetailKaryawan($id)
    {
        return $this->find($id);
    }
    
    public function getByNip($nip)
    {
        return $this->where('nip', $nip)->first();
    }

    // FIX: Tambahkan method untuk join dengan tabel divisi
    public function getKaryawanWithDivisi()
    {
        return $this->select('hrga_karyawan.*, divisi.nama_divisi')
                    ->join('divisi', 'divisi.id = hrga_karyawan.divisi_id')
                    ->findAll();
    }

    // FIX: Method untuk mendapatkan karyawan by ID dengan divisi
    public function getKaryawanWithDivisiById($id)
    {
        return $this->select('hrga_karyawan.*, divisi.nama_divisi')
                    ->join('divisi', 'divisi.id = hrga_karyawan.divisi_id')
                    ->where('hrga_karyawan.id', $id)
                    ->first();
    }
}