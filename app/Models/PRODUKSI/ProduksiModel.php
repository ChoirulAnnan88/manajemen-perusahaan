<?php
namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class ProduksiModel extends Model
{
    protected $table = 'produksi_hasil';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nomor_produksi', 'tanggal_produksi', 'jumlah_hasil', 'kualitas',
        'status_produksi', 'operator_id', 'alat_id', 'keterangan'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    public function getProduksi($id = null)
    {
        if ($id === null) {
            return $this->select('produksi_hasil.*, produksi_operator.nama_lengkap as operator_nama, produksi_alat.nama_alat as alat_nama')
                ->join('produksi_operator', 'produksi_operator.id = produksi_hasil.operator_id', 'left')
                ->join('produksi_alat', 'produksi_alat.id = produksi_hasil.alat_id', 'left')
                ->orderBy('produksi_hasil.created_at', 'DESC')
                ->findAll();
        }
        
        return $this->select('produksi_hasil.*, produksi_operator.nama_lengkap as operator_nama, produksi_alat.nama_alat as alat_nama')
            ->join('produksi_operator', 'produksi_operator.id = produksi_hasil.operator_id', 'left')
            ->join('produksi_alat', 'produksi_alat.id = produksi_hasil.alat_id', 'left')
            ->where('produksi_hasil.id', $id)
            ->first();
    }
}