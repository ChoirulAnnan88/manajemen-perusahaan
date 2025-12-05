<?php

namespace App\Models\PPIC;

use CodeIgniter\Model;

class InventoriModel extends Model
{
    protected $table = 'ppic_inventori';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'kode_item', 'nama_item', 'kategori', 'stok_minimal', 
        'stok_aktual', 'satuan', 'keterangan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getStokRendah()
    {
        return $this->where('stok_aktual <= stok_minimal')
                    ->orWhere('stok_aktual <', 10)
                    ->findAll();
    }
}