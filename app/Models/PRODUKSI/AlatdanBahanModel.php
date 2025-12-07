<?php
namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class AlatdanBahanModel extends Model
{
    protected $tableAlat = 'produksi_alat';
    protected $tableMaterial = 'produksi_material';
    protected $primaryKeyAlat = 'id';
    protected $primaryKeyMaterial = 'id';
    
    // Alat Methods
    public function getAllAlat()
    {
        $db = db_connect();
        return $db->table($this->tableAlat)
                 ->orderBy('nama_alat', 'ASC')
                 ->get()
                 ->getResultArray();
    }

    public function getAlatById($id)
    {
        $db = db_connect();
        return $db->table($this->tableAlat)
                 ->where('id', $id)
                 ->get()
                 ->getRowArray();
    }

    public function saveAlat($data)
    {
        $db = db_connect();
        return $db->table($this->tableAlat)->insert($data);
    }

    public function updateAlat($id, $data)
    {
        $db = db_connect();
        return $db->table($this->tableAlat)
                 ->where('id', $id)
                 ->update($data);
    }

    public function deleteAlat($id)
    {
        $db = db_connect();
        return $db->table($this->tableAlat)
                 ->where('id', $id)
                 ->delete();
    }

    public function countAlatByStatus($status)
    {
        $db = db_connect();
        return $db->table($this->tableAlat)
                 ->where('status', $status)
                 ->countAllResults();
    }

    public function getAlatByKondisi($kondisi)
    {
        $db = db_connect();
        return $db->table($this->tableAlat)
                 ->where('kondisi', $kondisi)
                 ->get()
                 ->getResultArray();
    }

    // Material Methods
    public function getAllMaterial()
    {
        $db = db_connect();
        return $db->table($this->tableMaterial)
                 ->orderBy('nama_material', 'ASC')
                 ->get()
                 ->getResultArray();
    }

    public function getMaterialById($id)
    {
        $db = db_connect();
        return $db->table($this->tableMaterial)
                 ->where('id', $id)
                 ->get()
                 ->getRowArray();
    }

    public function saveMaterial($data)
    {
        $db = db_connect();
        return $db->table($this->tableMaterial)->insert($data);
    }

    public function updateMaterial($id, $data)
    {
        $db = db_connect();
        return $db->table($this->tableMaterial)
                 ->where('id', $id)
                 ->update($data);
    }

    public function deleteMaterial($id)
    {
        $db = db_connect();
        return $db->table($this->tableMaterial)
                 ->where('id', $id)
                 ->delete();
    }

    public function countMaterialByStatus($status)
    {
        $db = db_connect();
        return $db->table($this->tableMaterial)
                 ->where('status_stok', $status)
                 ->countAllResults();
    }

    public function getLowStockMaterial()
    {
        $db = db_connect();
        return $db->table($this->tableMaterial)
                 ->where('stok_aktual <=', 'stok_minimal', false)
                 ->get()
                 ->getResultArray();
    }

    public function getDashboardStats()
    {
        $db = db_connect();
        
        $alatStats = $db->table($this->tableAlat)
                       ->select('status, COUNT(*) as total')
                       ->groupBy('status')
                       ->get()
                       ->getResultArray();
        
        $materialStats = $db->table($this->tableMaterial)
                          ->select('status_stok, COUNT(*) as total')
                          ->groupBy('status_stok')
                          ->get()
                          ->getResultArray();
        
        return [
            'alat' => $alatStats,
            'material' => $materialStats
        ];
    }

    // General Methods
    public function countAll()
    {
        $db = db_connect();
        return $db->table($this->tableAlat)->countAllResults();
    }
}