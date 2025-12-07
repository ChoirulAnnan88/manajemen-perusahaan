<?php
namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class ProduksiModel extends Model
{
    protected $table = 'v_produksi_terpadu';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nomor_produksi', 'tanggal_produksi', 'jumlah_hasil', 'kualitas', 'status_produksi', 'persentase_selesai', 'biaya_produksi'];
    protected $useTimestamps = false;

    public function getAllProduksi()
    {
        return $this->orderBy('tanggal_produksi', 'DESC')->findAll();
    }

    public function getProduksiHariIni()
    {
        $today = date('Y-m-d');
        return $this->where('tanggal_produksi', $today)->findAll();
    }

    public function getProduksiByStatus($status)
    {
        return $this->where('status_produksi', $status)->findAll();
    }

    public function getProduksiByDateRange($startDate, $endDate)
    {
        return $this->where('tanggal_produksi >=', $startDate)
                   ->where('tanggal_produksi <=', $endDate)
                   ->orderBy('tanggal_produksi', 'DESC')
                   ->findAll();
    }

    public function getProduksiSummary()
    {
        $db = db_connect();
        $query = $db->query("
            SELECT 
                COUNT(*) as total_produksi,
                SUM(jumlah_hasil) as total_unit,
                AVG(persentase_selesai) as rata_rata_selesai
            FROM v_produksi_terpadu
            WHERE status_produksi = 'completed'
        ");
        
        return $query->getRow();
    }
}