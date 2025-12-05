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
        'stok_aktual', 'satuan', 'status_stok', 'status', 'keterangan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'kode_item' => 'required|max_length[50]|is_unique[ppic_inventori.kode_item,id,{id}]',
        'nama_item' => 'required|max_length[100]',
        'kategori' => 'required|max_length[50]',
        'stok_minimal' => 'required|integer|greater_than[0]',
        'stok_aktual' => 'required|integer|greater_than_equal_to[0]',
        'satuan' => 'required|max_length[20]',
        'status_stok' => 'required|in_list[tersedia,habis,terbatas,expired,rusak]',
        'status' => 'required|in_list[active,inactive]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = true;

    public function getStokRendah()
    {
        return $this->where('stok_aktual <= stok_minimal')
                    ->orWhere('stok_aktual <', 10)
                    ->findAll();
    }
}