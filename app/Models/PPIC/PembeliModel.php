<?php

namespace App\Models\PPIC;

use CodeIgniter\Model;

class PembeliModel extends Model
{
    protected $table = 'ppic_pembeli';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'kode_pembeli', 'nama_perusahaan', 'contact_person', 
        'telepon', 'email', 'alamat'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}