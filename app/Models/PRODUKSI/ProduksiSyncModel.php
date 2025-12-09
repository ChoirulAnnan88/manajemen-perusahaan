<?php

namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class ProduksiSyncModel extends Model
{
    protected $table = 'produksi_hasil';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_ppic_produksi',
        'nomor_produksi',
        'tanggal_produksi',
        'jumlah_hasil',
        'kualitas',
        'status_produksi',
        'operator_id',
        'alat_id',
        'keterangan',
        'produk_ppic'
    ];
    
    protected $useTimestamps = true;
    
    /**
     * Create production with PPIC sync
     */
    public function createWithPpic($produksiData, $materials = [])
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // 1. Insert production
            $db->table('produksi_hasil')->insert($produksiData);
            $produksiId = $db->insertID();
            
            // 2. Update PPIC progress
            if (!empty($produksiData['id_ppic_produksi'])) {
                $ppicModel = new PpicProduksiModel();
                $ppicModel->updateProgress($produksiData['id_ppic_produksi'], $produksiData['jumlah_hasil']);
            }
            
            // 3. Process materials
            if (!empty($materials)) {
                $materialModel = new PpicMaterialModel();
                
                foreach ($materials as $material) {
                    // Insert material usage
                    $db->table('produksi_material_digunakan')->insert([
                        'produksi_hasil_id' => $produksiId,
                        'ppic_material_id' => $material['material_id'],
                        'jumlah_digunakan' => $material['jumlah']
                    ]);
                    
                    // Reduce PPIC stock
                    $materialModel->reduceStock($material['material_id'], $material['jumlah']);
                }
            }
            
            $db->transComplete();
            return ['success' => true, 'produksi_id' => $produksiId];
            
        } catch (\Exception $e) {
            $db->transRollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get production with PPIC data
     */
    public function getProduksiWithPpic()
    {
        $db = \Config\Database::connect();
        
        return $db->table('produksi_hasil ph')
            ->select('ph.*, 
                     pp.nomor_plan, pp.produk as nama_produk_ppic,
                     u.name as operator_name,
                     a.nama_alat')
            ->join('ppic_produksi pp', 'ph.id_ppic_produksi = pp.id', 'left')
            ->join('users u', 'ph.operator_id = u.id', 'left')
            ->join('alat a', 'ph.alat_id = a.id', 'left')
            ->orderBy('ph.tanggal_produksi', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Delete production with PPIC rollback
     */
    public function deleteWithRollback($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Get production data
            $produksi = $db->table('produksi_hasil')
                          ->where('id', $id)
                          ->get()
                          ->getRowArray();
            
            if (!$produksi) {
                throw new \Exception("Produksi tidak ditemukan");
            }
            
            // 1. Rollback PPIC progress
            if (!empty($produksi['id_ppic_produksi'])) {
                $ppicModel = new PpicProduksiModel();
                $ppicModel->rollbackProgress($produksi['id_ppic_produksi'], $produksi['jumlah_hasil']);
            }
            
            // 2. Rollback material stock
            $materialUsage = $db->table('produksi_material_digunakan')
                              ->where('produksi_hasil_id', $id)
                              ->get()
                              ->getResultArray();
            
            if ($materialUsage) {
                $materialModel = new PpicMaterialModel();
                foreach ($materialUsage as $usage) {
                    $materialModel->restoreStock($usage['ppic_material_id'], $usage['jumlah_digunakan']);
                }
            }
            
            // 3. Delete material usage records
            $db->table('produksi_material_digunakan')
               ->where('produksi_hasil_id', $id)
               ->delete();
            
            // 4. Delete production
            $db->table('produksi_hasil')
               ->where('id', $id)
               ->delete();
            
            $db->transComplete();
            return ['success' => true, 'message' => 'Produksi berhasil dihapus'];
            
        } catch (\Exception $e) {
            $db->transRollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}