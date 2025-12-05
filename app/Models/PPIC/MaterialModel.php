<?php

namespace App\Models\PPIC;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table = 'ppic_material';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'kode_material', 'nama_material', 'spesifikasi', 
        'stok_aktual', 'satuan', 'keterangan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}