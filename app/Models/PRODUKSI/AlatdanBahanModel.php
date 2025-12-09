<?php

namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class AlatdanBahanModel extends Model
{
    protected $table = 'ppic_material';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_material',
        'nama_material',
        'spesifikasi',
        'stok_aktual',
        'stok_minimal',
        'satuan',
        'status_stok',
        'keterangan',
        'lokasi',
        'harga_satuan'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    public function getAllMaterials($availableOnly = false)
    {
        $builder = $this->builder();
        if ($availableOnly) {
            $builder->where('stok_aktual >', 0)
                    ->whereIn('status_stok', ['tersedia', 'terbatas']);
        }
        $builder->orderBy('nama_material', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}