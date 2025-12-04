<?php
namespace App\Models\FINANCE;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table = 'finance_transaksi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nomor_transaksi', 
        'tanggal_transaksi', 
        'jenis', 
        'kategori', 
        'jumlah', 
        'keterangan'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    
    public function getAllTransaksi()
    {
        return $this->orderBy('tanggal_transaksi', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getPemasukan()
    {
        return $this->where('jenis', 'pemasukan')
                    ->orderBy('tanggal_transaksi', 'DESC')
                    ->findAll();
    }

    public function getPengeluaran()
    {
        return $this->where('jenis', 'pengeluaran')
                    ->orderBy('tanggal_transaksi', 'DESC')
                    ->findAll();
    }

    public function getByPeriod($startDate, $endDate)
    {
        return $this->where('tanggal_transaksi >=', $startDate)
                    ->where('tanggal_transaksi <=', $endDate)
                    ->orderBy('tanggal_transaksi', 'DESC')
                    ->findAll();
    }

    public function getTotalPemasukan()
    {
        return $this->selectSum('jumlah')
                    ->where('jenis', 'pemasukan')
                    ->get()
                    ->getRow()
                    ->jumlah ?? 0;
    }

    public function getTotalPengeluaran()
    {
        return $this->selectSum('jumlah')
                    ->where('jenis', 'pengeluaran')
                    ->get()
                    ->getRow()
                    ->jumlah ?? 0;
    }
}