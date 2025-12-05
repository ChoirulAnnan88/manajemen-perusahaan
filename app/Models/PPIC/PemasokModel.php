<?php

namespace App\Models\PPIC;

use CodeIgniter\Model;

class PemasokModel extends Model
{
    protected $table = 'ppic_pemasok';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'kode_pemasok', 'nama_perusahaan', 'contact_person', 
        'telepon', 'email', 'alamat'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}