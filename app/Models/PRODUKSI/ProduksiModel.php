<?php

namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class ProduksiModel extends Model
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
        'jumlah_target_ppic',
        'produk_ppic'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get all production with PPIC data
     */
    public function getAllWithPpic()
    {
        return $this->db->table('produksi_hasil ph')
            ->select('ph.*, pp.nomor_plan, pp.produk as produk_ppic, pp.jumlah_target, 
                     pp.status as ppic_status, u.name as operator_name, a.nama_alat')
            ->join('ppic_produksi pp', 'ph.id_ppic_produksi = pp.id', 'left')
            ->join('users u', 'ph.operator_id = u.id', 'left')
            ->join('alat a', 'ph.alat_id = a.id', 'left')
            ->orderBy('ph.tanggal_produksi', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get single production with PPIC data
     */
    public function getWithPpic($id)
    {
        return $this->db->table('produksi_hasil ph')
            ->select('ph.*, pp.nomor_plan, pp.produk, pp.jumlah_target, 
                     u.name as operator_name, a.nama_alat')
            ->join('ppic_produksi pp', 'ph.id_ppic_produksi = pp.id', 'left')
            ->join('users u', 'ph.operator_id = u.id', 'left')
            ->join('alat a', 'ph.alat_id = a.id', 'left')
            ->where('ph.id', $id)
            ->get()
            ->getRowArray();
    }
    
    /**
     * Get production for today
     */
    public function getProduksiHariIni()
    {
        $today = date('Y-m-d');
        return $this->where('DATE(tanggal_produksi)', $today)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }
    
    /**
     * Get all production
     */
    public function getAllProduksi()
    {
        return $this->orderBy('tanggal_produksi', 'DESC')
                   ->findAll();
    }
    
    /**
     * Create production with PPIC sync (SIMPLE VERSION)
     */
    public function createWithPpic($produksiData)
    {
        $this->db->transStart();
        
        try {
            // 1. Insert production
            $this->insert($produksiData);
            $produksiId = $this->db->insertID();
            
            // 2. Update PPIC progress if PPIC ID exists
            if (!empty($produksiData['id_ppic_produksi'])) {
                $this->updatePpicProgress($produksiData['id_ppic_produksi'], $produksiData['jumlah_hasil']);
            }
            
            $this->db->transComplete();
            return ['success' => true, 'produksi_id' => $produksiId];
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Update PPIC production progress (using direct database query)
     */
    private function updatePpicProgress($ppicId, $jumlahHasil)
    {
        // Get current PPIC data
        $ppic = $this->db->table('ppic_produksi')
            ->where('id', $ppicId)
            ->get()
            ->getRowArray();
        
        if (!$ppic) return false;
        
        // Calculate new progress
        $newJumlahHasil = $ppic['jumlah_hasil'] + $jumlahHasil;
        $persentase = ($newJumlahHasil / $ppic['jumlah_target']) * 100;
        
        // Determine status
        $status = $ppic['status'];
        $statusProduksi = $ppic['status_produksi'];
        
        if ($persentase >= 100) {
            $status = 'completed';
            $statusProduksi = 'selesai';
        } elseif ($newJumlahHasil > 0) {
            $status = 'progress';
            $statusProduksi = 'proses';
        }
        
        // Update PPIC
        return $this->db->table('ppic_produksi')
            ->where('id', $ppicId)
            ->update([
                'jumlah_hasil' => $newJumlahHasil,
                'persentase_selesai' => min($persentase, 100),
                'status' => $status,
                'status_produksi' => $statusProduksi,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
    
    /**
     * Delete production with PPIC rollback
     */
    public function deleteWithPpicRollback($id)
    {
        $this->db->transStart();
        
        try {
            // Get production data
            $produksi = $this->find($id);
            if (!$produksi) {
                throw new \Exception("Produksi tidak ditemukan");
            }
            
            // 1. Rollback PPIC progress if exists
            if (!empty($produksi['id_ppic_produksi'])) {
                $this->rollbackPpicProgress($produksi['id_ppic_produksi'], $produksi['jumlah_hasil']);
            }
            
            // 2. Rollback material stock if exists in produksi_material_digunakan
            $this->rollbackMaterialStock($id);
            
            // 3. Delete from produksi_material_digunakan if exists
            if ($this->db->tableExists('produksi_material_digunakan')) {
                $this->db->table('produksi_material_digunakan')
                    ->where('produksi_hasil_id', $id)
                    ->delete();
            }
            
            // 4. Delete production
            $this->delete($id);
            
            $this->db->transComplete();
            return ['success' => true, 'message' => 'Produksi dihapus dan data PPIC dikembalikan'];
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Rollback PPIC progress
     */
    private function rollbackPpicProgress($ppicId, $jumlahHasil)
    {
        // Get current PPIC data
        $ppic = $this->db->table('ppic_produksi')
            ->where('id', $ppicId)
            ->get()
            ->getRowArray();
        
        if (!$ppic) return false;
        
        // Calculate new values
        $newJumlahHasil = max(0, $ppic['jumlah_hasil'] - $jumlahHasil);
        $persentase = ($newJumlahHasil / $ppic['jumlah_target']) * 100;
        
        // Determine status
        $status = $ppic['status'];
        $statusProduksi = $ppic['status_produksi'];
        
        if ($newJumlahHasil <= 0) {
            $status = 'planned';
            $statusProduksi = 'menunggu';
        } elseif ($persentase >= 100) {
            $status = 'completed';
            $statusProduksi = 'selesai';
        } elseif ($newJumlahHasil > 0) {
            $status = 'progress';
            $statusProduksi = 'proses';
        }
        
        // Update PPIC
        return $this->db->table('ppic_produksi')
            ->where('id', $ppicId)
            ->update([
                'jumlah_hasil' => $newJumlahHasil,
                'persentase_selesai' => $persentase,
                'status' => $status,
                'status_produksi' => $statusProduksi,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
    
    /**
     * Rollback material stock from PPIC
     */
    private function rollbackMaterialStock($produksiId)
    {
        // Check if table exists
        if (!$this->db->tableExists('produksi_material_digunakan')) {
            return false;
        }
        
        // Get materials used
        $materialsUsed = $this->db->table('produksi_material_digunakan')
            ->where('produksi_hasil_id', $produksiId)
            ->get()
            ->getResultArray();
        
        foreach ($materialsUsed as $material) {
            // Get current material data
            $currentMaterial = $this->db->table('ppic_material')
                ->where('id', $material['ppic_material_id'])
                ->get()
                ->getRowArray();
            
            if ($currentMaterial) {
                // Calculate new stock
                $newStock = $currentMaterial['stok_aktual'] + $material['jumlah_digunakan'];
                $statusStok = $this->calculateStockStatus($newStock, $currentMaterial['stok_minimal']);
                
                // Update material stock
                $this->db->table('ppic_material')
                    ->where('id', $material['ppic_material_id'])
                    ->update([
                        'stok_aktual' => $newStock,
                        'status_stok' => $statusStok,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }
        }
        
        return true;
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
    
    /**
     * Get available PPIC plans for production
     */
    public function getAvailablePpicPlans()
    {
        return $this->db->table('ppic_produksi')
            ->whereIn('status', ['planned', 'progress'])
            ->orderBy('tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get PPIC material stock
     */
    public function getPpicMaterials($availableOnly = true)
    {
        $builder = $this->db->table('ppic_material');
        
        if ($availableOnly) {
            $builder->where('stok_aktual >', 0)
                   ->whereIn('status_stok', ['tersedia', 'terbatas']);
        }
        
        return $builder->orderBy('nama_material', 'ASC')
                      ->get()
                      ->getResultArray();
    }
    
    /**
     * Check if material has enough stock
     */
    public function checkMaterialStock($materialId, $jumlah)
    {
        $material = $this->db->table('ppic_material')
            ->select('stok_aktual, nama_material')
            ->where('id', $materialId)
            ->get()
            ->getRowArray();
        
        if (!$material) {
            return ['enough' => false, 'message' => 'Material tidak ditemukan'];
        }
        
        if ($material['stok_aktual'] < $jumlah) {
            return ['enough' => false, 'message' => "Stok {$material['nama_material']} tidak cukup. Stok tersedia: {$material['stok_aktual']}"];
        }
        
        return ['enough' => true];
    }
    
    /**
     * Use material and update stock
     */
    public function useMaterial($produksiId, $materialId, $jumlah, $keterangan = null)
    {
        $this->db->transStart();
        
        try {
            // Get material info
            $material = $this->db->table('ppic_material')
                ->where('id', $materialId)
                ->get()
                ->getRowArray();
            
            if (!$material) {
                throw new \Exception('Material tidak ditemukan');
            }
            
            // Check stock
            if ($material['stok_aktual'] < $jumlah) {
                throw new \Exception("Stok {$material['nama_material']} tidak cukup");
            }
            
            // Insert to produksi_material_digunakan
            $materialUsageData = [
                'produksi_hasil_id' => $produksiId,
                'ppic_material_id' => $materialId,
                'kode_material' => $material['kode_material'],
                'nama_material' => $material['nama_material'],
                'jumlah_digunakan' => $jumlah,
                'satuan' => $material['satuan'],
                'harga_satuan' => $material['harga_satuan'] ?? 0,
                'total_harga' => ($material['harga_satuan'] ?? 0) * $jumlah,
                'tanggal_penggunaan' => date('Y-m-d'),
                'keterangan' => $keterangan,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->table('produksi_material_digunakan')->insert($materialUsageData);
            
            // Update material stock
            $newStock = $material['stok_aktual'] - $jumlah;
            $statusStok = $this->calculateStockStatus($newStock, $material['stok_minimal']);
            
            $this->db->table('ppic_material')
                ->where('id', $materialId)
                ->update([
                    'stok_aktual' => $newStock,
                    'status_stok' => $statusStok,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            $this->db->transComplete();
            return ['success' => true, 'message' => 'Material berhasil digunakan'];
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}