<?php
namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class OperatorModel extends Model
{
    protected $table = 'produksi_operator';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'username', 'nama_lengkap', 'email', 'nip',
        'status_kerja', 'alat_id', 'keterangan'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    public function getOperators($limit = null)
    {
        $builder = $this->select('produksi_operator.*, produksi_alat.nama_alat')
            ->join('produksi_alat', 'produksi_alat.id = produksi_operator.alat_id', 'left');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->orderBy('produksi_operator.created_at', 'DESC')->findAll();
    }

    public function getOperator($id)
    {
        return $this->select('produksi_operator.*, produksi_alat.nama_alat, produksi_alat.kode_alat')
            ->join('produksi_alat', 'produksi_alat.id = produksi_operator.alat_id', 'left')
            ->where('produksi_operator.id', $id)
            ->first();
    }
}