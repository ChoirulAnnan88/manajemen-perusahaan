<?php

namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class PpicMaterialModel extends Model
{
    protected $table = 'ppic_material';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_material',
        'nama_material',
        'stok_aktual',
        'stok_minimal',
        'satuan',
        'status_stok'
    ];
    
    /**
     * Get available materials
     */
    public function getAvailableMaterials()
    {
        return $this->where('stok_aktual >', 0)
                   ->whereIn('status_stok', ['tersedia', 'terbatas'])
                   ->orderBy('nama_material', 'ASC')
                   ->findAll();
    }
    
    /**
     * Reduce material stock
     */
    public function reduceStock($id, $jumlah)
    {
        $material = $this->find($id);
        if (!$material) return false;
        
        $newStock = $material['stok_aktual'] - $jumlah;
        $statusStok = $this->calculateStockStatus($newStock, $material['stok_minimal']);
        
        return $this->update($id, [
            'stok_aktual' => $newStock,
            'status_stok' => $statusStok
        ]);
    }
    
    /**
     * Restore material stock
     */
    public function restoreStock($id, $jumlah)
    {
        $material = $this->find($id);
        if (!$material) return false;
        
        $newStock = $material['stok_aktual'] + $jumlah;
        $statusStok = $this->calculateStockStatus($newStock, $material['stok_minimal']);
        
        return $this->update($id, [
            'stok_aktual' => $newStock,
            'status_stok' => $statusStok
        ]);
    }
    
    /**
     * Calculate stock status
     */
    private function calculateStockStatus($stok, $stokMinimal)
    {
        if ($stok <= 0) return 'habis';
        if ($stok <= $stokMinimal) return 'terbatas';
        return 'tersedia';
    }
}