<?php
namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class AlatdanBahanModel extends Model
{
    protected $DBGroup = 'default';
    
    // Konfigurasi untuk tabel ALAT
    protected $tableAlat = 'produksi_alat';
    protected $primaryKeyAlat = 'id';
    protected $allowedFieldsAlat = [
        'kode_alat', 'nama_alat', 'tipe', 'kategori', 'spesifikasi',
        'status', 'kondisi', 'tanggal_maintenance', 'lokasi', 'keterangan'
    ];
    
    // Konfigurasi untuk tabel MATERIAL
    protected $tableMaterial = 'produksi_material';
    protected $primaryKeyMaterial = 'id';
    protected $allowedFieldsMaterial = [
        'material_id', 'kode_material', 'nama_material', 'spesifikasi',
        'stok_aktual', 'stok_minimal', 'satuan', 'harga_satuan',
        'status_stok', 'lokasi', 'keterangan'
    ];
    
    // Konfigurasi untuk tabel PPIC MATERIAL
    protected $tablePpicMaterial = 'ppic_material';
    protected $allowedFieldsPpicMaterial = [
        'kode_material', 'nama_material', 'spesifikasi', 'stok_aktual',
        'stok_minimal', 'satuan', 'harga_satuan', 'status_stok',
        'lokasi', 'keterangan'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    // ==================== METHOD UNTUK ALAT ====================
    
    /**
     * Get semua alat
     */
    public function getAlat($id = null, $limit = null)
    {
        $builder = $this->db->table($this->tableAlat);
        
        if ($id !== null) {
            return $builder->where('id', $id)->get()->getRowArray();
        }
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
    }
    
    /**
     * Insert alat baru
     */
    public function insertAlat($data)
    {
        return $this->db->table($this->tableAlat)->insert($data);
    }
    
    /**
     * Update alat
     */
    public function updateAlat($id, $data)
    {
        return $this->db->table($this->tableAlat)->where('id', $id)->update($data);
    }
    
    /**
     * Delete alat
     */
    public function deleteAlat($id)
    {
        return $this->db->table($this->tableAlat)->where('id', $id)->delete();
    }
    
    /**
     * Count total alat
     */
    public function countAlat()
    {
        return $this->db->table($this->tableAlat)->countAllResults();
    }
    
    /**
     * Get alat dengan status tertentu
     */
    public function getAlatByStatus($status)
    {
        return $this->db->table($this->tableAlat)
            ->where('status', $status)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();
    }
    
    // ==================== METHOD UNTUK MATERIAL ====================
    
    /**
     * Get semua material dari produksi_material
     */
    public function getMaterials($id = null, $limit = null)
    {
        $builder = $this->db->table($this->tableMaterial);
        
        if ($id !== null) {
            return $builder->where('id', $id)->get()->getRowArray();
        }
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
    }
    
    /**
     * Get material dengan join ke ppic_material
     */
    public function getMaterialWithPpic($id = null)
    {
        $builder = $this->db->table($this->tableMaterial . ' pm');
        $builder->select('pm.*, pp.kode_material as kode_ppic, pp.nama_material as nama_ppic, 
                         pp.stok_aktual as stok_ppic, pp.harga_satuan as harga_ppic,
                         pp.status_stok as status_ppic');
        $builder->join($this->tablePpicMaterial . ' pp', 'pp.id = pm.material_id', 'left');
        
        if ($id !== null) {
            return $builder->where('pm.id', $id)->get()->getRowArray();
        }
        
        return $builder->orderBy('pm.created_at', 'DESC')->get()->getResultArray();
    }
    
    /**
     * Insert material baru
     */
    public function insertMaterial($data)
    {
        return $this->db->table($this->tableMaterial)->insert($data);
    }
    
    /**
     * Update material
     */
    public function updateMaterial($id, $data)
    {
        return $this->db->table($this->tableMaterial)->where('id', $id)->update($data);
    }
    
    /**
     * Delete material
     */
    public function deleteMaterial($id)
    {
        return $this->db->table($this->tableMaterial)->where('id', $id)->delete();
    }
    
    /**
     * Count total material
     */
    public function countMaterial()
    {
        return $this->db->table($this->tableMaterial)->countAllResults();
    }
    
    /**
     * Get material dengan status stok tertentu
     */
    public function getMaterialByStockStatus($status)
    {
        return $this->db->table($this->tableMaterial)
            ->where('status_stok', $status)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();
    }
    
    // ==================== METHOD UNTUK PPIC MATERIAL ====================
    
    /**
     * Get semua material dari ppic_material (untuk dropdown)
     */
    public function getPpicMaterials($limit = null)
    {
        $builder = $this->db->table($this->tablePpicMaterial);
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->orderBy('nama_material', 'ASC')->get()->getResultArray();
    }
    
    /**
     * Get material dari ppic yang belum ada di produksi_material
     */
    public function getPpicMaterialsNotInProduksi()
    {
        $subQuery = $this->db->table($this->tableMaterial)
            ->select('material_id')
            ->where('material_id IS NOT NULL');
            
        return $this->db->table($this->tablePpicMaterial . ' pp')
            ->where('pp.id NOT IN (SELECT material_id FROM ' . $this->tableMaterial . ' WHERE material_id IS NOT NULL)', null, false)
            ->orderBy('pp.nama_material', 'ASC')
            ->get()->getResultArray();
    }
    
    /**
     * Sync material dari ppic ke produksi
     */
    public function syncMaterialFromPpic($ppic_id)
    {
        // Get data from ppic_material
        $ppicData = $this->db->table($this->tablePpicMaterial)
            ->where('id', $ppic_id)
            ->get()->getRowArray();
            
        if (!$ppicData) {
            return false;
        }
        
        // Prepare data for produksi_material
        $data = [
            'material_id' => $ppicData['id'],
            'kode_material' => $ppicData['kode_material'],
            'nama_material' => $ppicData['nama_material'],
            'spesifikasi' => $ppicData['spesifikasi'],
            'stok_aktual' => $ppicData['stok_aktual'],
            'stok_minimal' => $ppicData['stok_minimal'],
            'satuan' => $ppicData['satuan'],
            'harga_satuan' => $ppicData['harga_satuan'],
            'status_stok' => $ppicData['status_stok'],
            'lokasi' => $ppicData['lokasi'],
            'keterangan' => $ppicData['keterangan']
        ];
        
        // Insert to produksi_material
        return $this->db->table($this->tableMaterial)->insert($data);
    }
    
    /**
     * Update stok material (sinkron dengan ppic)
     */
    public function updateMaterialStock($material_id, $stok_baru)
    {
        // Update di produksi_material
        $this->db->table($this->tableMaterial)
            ->where('id', $material_id)
            ->update([
                'stok_aktual' => $stok_baru,
                'status_stok' => $this->getStatusStok($stok_baru, 10) // default minimal 10
            ]);
            
        // Jika ada material_id, update juga di ppic_material
        $material = $this->db->table($this->tableMaterial)
            ->where('id', $material_id)
            ->get()->getRowArray();
            
        if ($material && $material['material_id']) {
            $this->db->table($this->tablePpicMaterial)
                ->where('id', $material['material_id'])
                ->update([
                    'stok_aktual' => $stok_baru,
                    'status_stok' => $this->getStatusStok($stok_baru, 10)
                ]);
        }
        
        return true;
    }
    
    /**
     * Helper: Get status stok berdasarkan jumlah
     */
    private function getStatusStok($stok_aktual, $stok_minimal)
    {
        if ($stok_aktual <= 0) {
            return 'habis';
        } elseif ($stok_aktual <= $stok_minimal) {
            return 'terbatas';
        } else {
            return 'tersedia';
        }
    }
    
    // ==================== METHOD UNTUK DASHBOARD ====================
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        return [
            'total_alat' => $this->countAlat(),
            'total_material' => $this->countMaterial(),
            'alat_aktif' => $this->db->table($this->tableAlat)->where('status', 'aktif')->countAllResults(),
            'alat_maintenance' => $this->db->table($this->tableAlat)->where('status', 'maintenance')->countAllResults(),
            'material_tersedia' => $this->db->table($this->tableMaterial)->where('status_stok', 'tersedia')->countAllResults(),
            'material_terbatas' => $this->db->table($this->tableMaterial)->where('status_stok', 'terbatas')->countAllResults(),
            'material_habis' => $this->db->table($this->tableMaterial)->where('status_stok', 'habis')->countAllResults()
        ];
    }
    
    /**
     * Get recent alat
     */
    public function getRecentAlat($limit = 5)
    {
        return $this->db->table($this->tableAlat)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
    
    /**
     * Get recent material
     */
    public function getRecentMaterial($limit = 5)
    {
        return $this->db->table($this->tableMaterial)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
    
    /**
     * Get alat untuk dropdown
     */
    public function getAlatForDropdown()
    {
        $results = $this->db->table($this->tableAlat)
            ->select('id, kode_alat, nama_alat, status')
            ->where('status', 'aktif')
            ->orderBy('nama_alat', 'ASC')
            ->get()->getResultArray();
            
        $dropdown = [];
        foreach ($results as $row) {
            $dropdown[$row['id']] = $row['kode_alat'] . ' - ' . $row['nama_alat'];
        }
        
        return $dropdown;
    }
    
    /**
     * Get material untuk dropdown
     */
    public function getMaterialForDropdown()
    {
        $results = $this->db->table($this->tableMaterial)
            ->select('id, kode_material, nama_material, status_stok')
            ->where('status_stok !=', 'habis')
            ->orderBy('nama_material', 'ASC')
            ->get()->getResultArray();
            
        $dropdown = [];
        foreach ($results as $row) {
            $dropdown[$row['id']] = $row['kode_material'] . ' - ' . $row['nama_material'] . ' (Stok: ' . $row['status_stok'] . ')';
        }
        
        return $dropdown;
    }
}