<?php
namespace App\Models;

use CodeIgniter\Model;

class HrgaPerawatanModel extends Model
{
    protected $table = 'hrga_perawatan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_perawatan', 'deskripsi', 'lokasi', 'tanggal_perawatan',
        'biaya', 'status', 'created_at'
    ];
    
    // HAPUS useTimestamps karena tabel tidak ada updated_at
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $dateFormat = 'datetime';
    
    public function getJadwalPerawatan()
    {
        return $this->where('status', 'planned')->findAll();
    }
    
    public function getPerawatanSelesai()
    {
        return $this->where('status', 'completed')->findAll();
    }
    
    public function getByKode($kode_perawatan)
    {
        return $this->where('kode_perawatan', $kode_perawatan)->first();
    }
    
    public function getByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }
    
    public function getPerawatanBulanIni()
    {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        return $this->where('tanggal_perawatan >=', $start)
                    ->where('tanggal_perawatan <=', $end)
                    ->findAll();
    }

    // FIX: Generate kode perawatan otomatis
    public function generateKodePerawatan()
    {
        $prefix = 'PRW';
        $lastItem = $this->orderBy('id', 'DESC')->first();
        
        if ($lastItem) {
            $lastCode = $lastItem['kode_perawatan'];
            $lastNumber = intval(substr($lastCode, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}