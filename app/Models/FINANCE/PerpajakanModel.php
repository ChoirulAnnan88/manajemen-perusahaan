<?php
namespace App\Models\FINANCE;

use CodeIgniter\Model;

class PerpajakanModel extends Model
{
    protected $table = 'finance_pajak';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'jenis_pajak', 
        'periode', 
        'jumlah_pajak', 
        'tanggal_jatuh_tempo', 
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    
    public function getAllPajak()
    {
        return $this->orderBy('periode', 'DESC')
                    ->orderBy('tanggal_jatuh_tempo', 'ASC')
                    ->findAll();
    }

    public function getByStatus($status)
    {
        return $this->where('status', $status)
                    ->orderBy('tanggal_jatuh_tempo', 'ASC')
                    ->findAll();
    }

    public function getJatuhTempo()
    {
        $today = date('Y-m-d');
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        
        return $this->where('status', 'belum_bayar')
                    ->where('tanggal_jatuh_tempo >=', $today)
                    ->where('tanggal_jatuh_tempo <=', $nextWeek)
                    ->orderBy('tanggal_jatuh_tempo', 'ASC')
                    ->findAll();
    }

    public function getTerlambat()
    {
        $today = date('Y-m-d');
        
        return $this->where('status', 'belum_bayar')
                    ->where('tanggal_jatuh_tempo <', $today)
                    ->orderBy('tanggal_jatuh_tempo', 'ASC')
                    ->findAll();
    }

    public function getTotalPajak($status = null)
    {
        $builder = $this->selectSum('jumlah_pajak');
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->get()
                       ->getRow()
                       ->jumlah_pajak ?? 0;
    }
}