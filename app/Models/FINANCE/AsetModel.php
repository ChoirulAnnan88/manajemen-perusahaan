<?php
namespace App\Models\FINANCE;

use CodeIgniter\Model;

class AsetModel extends Model
{
    protected $table = 'finance_aset';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_aset', 
        'nama_aset', 
        'kategori', 
        'nilai_aset', 
        'tanggal_perolehan', 
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    
    public function getAllAset()
    {
        return $this->orderBy('nama_aset', 'ASC')
                    ->findAll();
    }

    public function getByKategori($kategori)
    {
        return $this->where('kategori', $kategori)
                    ->orderBy('nama_aset', 'ASC')
                    ->findAll();
    }

    public function getByStatus($status)
    {
        return $this->where('status', $status)
                    ->orderBy('nama_aset', 'ASC')
                    ->findAll();
    }

    public function getTotalNilaiAset()
    {
        return $this->selectSum('nilai_aset')
                    ->get()
                    ->getRow()
                    ->nilai_aset ?? 0;
    }

    public function getAsetByStatus()
    {
        return $this->select('status, COUNT(*) as jumlah, SUM(nilai_aset) as total_nilai')
                    ->groupBy('status')
                    ->get()
                    ->getResultArray();
    }

    public function generateKodeAset($kategori)
    {
        $prefix = '';
        switch($kategori) {
            case 'kantor': $prefix = 'OF'; break;
            case 'produksi': $prefix = 'PR'; break;
            case 'kendaraan': $prefix = 'VE'; break;
            case 'bangunan': $prefix = 'BU'; break;
            case 'tanah': $prefix = 'LA'; break;
            default: $prefix = 'AS';
        }
        
        $year = date('Y');
        $month = date('m');
        
        $last = $this->like('kode_aset', $prefix . $year . $month . '%')
                     ->orderBy('kode_aset', 'DESC')
                     ->first();
        
        if ($last) {
            $lastNumber = intval(substr($last['kode_aset'], -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $prefix . $year . $month . $newNumber;
    }
}