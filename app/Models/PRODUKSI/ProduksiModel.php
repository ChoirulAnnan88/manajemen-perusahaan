<?php
namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class ProduksiModel extends Model
{
    protected $table = 'v_produksi_terpadu';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nomor_produksi', 'tanggal_produksi', 'jumlah_hasil', 'kualitas', 'status_produksi', 'persentase_selesai', 'biaya_produksi'];
    protected $useTimestamps = false;
    protected $realTable = 'produksi_produksi';

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

    // TAMBAHKAN METHOD INI:
    public function find($id = null)
    {
        if ($id === null) {
            return $this->findAll();
        }
        
        return $this->where($this->primaryKey, $id)->first();
    }

    // TAMBAHKAN METHOD INI UNTUK SAVE KE TABEL
    public function saveToTable($data)
    {
        $db = db_connect();
        
        // Map data untuk tabel produksi_produksi
        $tableData = [
            'nomor_produksi' => $data['nomor_produksi'],
            'tanggal_produksi' => $data['tanggal_produksi'],
            'jumlah_hasil' => $data['jumlah_hasil'],
            'kualitas' => $data['kualitas'],
            'status' => $data['status_produksi'], // Konversi: status_produksi -> status
            'operator_id' => $data['operator_id'] ?? null,
            'alat_id' => $data['alat_id'] ?? null,
            'keterangan' => $data['keterangan'] ?? null
        ];
        
        // Jika ada ID, berarti UPDATE
        if (isset($data['id'])) {
            return $db->table($this->realTable)
                     ->where('id', $data['id'])
                     ->update($tableData);
        } else {
            // INSERT
            return $db->table($this->realTable)
                     ->insert($tableData);
        }
    }

    // TAMBAHKAN METHOD INI UNTUK DELETE DARI TABEL
    public function deleteFromTable($id)
    {
        $db = db_connect();
        return $db->table($this->realTable)
                 ->where('id', $id)
                 ->delete();
    }
}