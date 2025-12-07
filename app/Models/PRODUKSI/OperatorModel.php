<?php
namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class OperatorModel extends Model
{
    protected $table = 'produksi_operator';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'username', 'nama_lengkap', 'email', 'nip', 'status_kerja', 'alat_id', 'keterangan'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAllOperator()
    {
        $db = db_connect();
        return $db->table($this->table)
                 ->select('produksi_operator.*, produksi_alat.nama_alat')
                 ->join('produksi_alat', 'produksi_alat.id = produksi_operator.alat_id', 'left')
                 ->orderBy('produksi_operator.nama_lengkap', 'ASC')
                 ->get()
                 ->getResultArray();
    }

    public function getOperatorById($id)
    {
        return $this->find($id);
    }

    public function getOperatorWithAlat($id)
    {
        $db = db_connect();
        return $db->table($this->table)
                 ->select('produksi_operator.*, produksi_alat.nama_alat, produksi_alat.kode_alat, produksi_alat.tipe, produksi_alat.kondisi')
                 ->join('produksi_alat', 'produksi_alat.id = produksi_operator.alat_id', 'left')
                 ->where('produksi_operator.id', $id)
                 ->get()
                 ->getRowArray();
    }

    public function getOperatorByStatus($status)
    {
        return $this->where('status_kerja', $status)->findAll();
    }

    public function countOperatorByStatus($status)
    {
        return $this->where('status_kerja', $status)->countAllResults();
    }

    public function getOperatorDashboardStats()
    {
        $db = db_connect();
        $query = $db->query("
            SELECT 
                status_kerja,
                COUNT(*) as total
            FROM produksi_operator
            GROUP BY status_kerja
        ");
        
        return $query->getResultArray();
    }

    public function searchOperator($keyword)
    {
        return $this->like('nama_lengkap', $keyword)
                   ->orLike('nip', $keyword)
                   ->orLike('email', $keyword)
                   ->findAll();
    }

    public function countAll()
    {
        return $this->countAllResults();
    }

    public function getActiveOperators()
    {
        return $this->where('status_kerja', 'aktif')->findAll();
    }
}