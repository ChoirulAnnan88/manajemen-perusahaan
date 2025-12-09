<?php

namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class ProduksiMaterialUsageModel extends Model
{
    protected $table = 'produksi_material_digunakan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'produksi_hasil_id',
        'ppic_material_id',
        'kode_material',
        'nama_material',
        'jumlah_digunakan',
        'satuan',
        'harga_satuan',
        'total_harga',
        'tanggal_penggunaan',
        'keterangan'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get materials used by production
     */
    public function getByProduksiId($produksiId)
    {
        return $this->where('produksi_hasil_id', $produksiId)
                   ->findAll();
    }
    
    /**
     * Get materials with PPIC info
     */
    public function getWithPpicInfo($produksiId)
    {
        return $this->db->table('produksi_material_digunakan pmd')
            ->select('pmd.*, pm.stok_aktual as current_stock, pm.stok_minimal')
            ->join('ppic_material pm', 'pmd.ppic_material_id = pm.id')
            ->where('pmd.produksi_hasil_id', $produksiId)
            ->get()
            ->getResultArray();
    }
}