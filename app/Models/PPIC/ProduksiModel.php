<?php

namespace App\Models\PPIC;

use CodeIgniter\Model;

class ProduksiModel extends Model
{
    protected $table = 'ppic_produksi';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'nomor_plan', 'produk_id', 'produk', 'jumlah_target', 
        'tanggal_mulai', 'tanggal_selesai', 'status', 'keterangan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}