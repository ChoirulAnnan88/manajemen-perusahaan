<?php
namespace App\Models\FINANCE;

use CodeIgniter\Model;

class AnggaranModel extends Model
{
    protected $table = 'finance_anggaran';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tahun', 
        'divisi_id', 
        'kategori_anggaran', 
        'jumlah_anggaran', 
        'realisasi'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    
    public function getAllAnggaran()
    {
        return $this->orderBy('tahun', 'DESC')
                    ->orderBy('divisi_id', 'ASC')
                    ->findAll();
    }

    public function getByTahun($tahun)
    {
        return $this->where('tahun', $tahun)
                    ->orderBy('divisi_id', 'ASC')
                    ->findAll();
    }

    public function getByDivisi($divisi_id)
    {
        return $this->where('divisi_id', $divisi_id)
                    ->orderBy('tahun', 'DESC')
                    ->findAll();
    }

    public function getDistinctYears()
    {
        return $this->distinct()
                    ->select('tahun')
                    ->orderBy('tahun', 'DESC')
                    ->findAll();
    }

    public function getTotalAnggaran($tahun = null)
    {
        $builder = $this->selectSum('jumlah_anggaran');
        
        if ($tahun) {
            $builder->where('tahun', $tahun);
        }
        
        return $builder->get()
                       ->getRow()
                       ->jumlah_anggaran ?? 0;
    }

    public function getTotalRealisasi($tahun = null)
    {
        $builder = $this->selectSum('realisasi');
        
        if ($tahun) {
            $builder->where('tahun', $tahun);
        }
        
        return $builder->get()
                       ->getRow()
                       ->realisasi ?? 0;
    }

    public function getBudgetUtilization($tahun = null)
    {
        $builder = $this->select('*, ((realisasi / jumlah_anggaran) * 100) as persentase');
        
        if ($tahun) {
            $builder->where('tahun', $tahun);
        }
        
        return $builder->get()
                       ->getResultArray();
    }
}