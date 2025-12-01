<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PenggajianModel extends Model
{
    protected $table = 'hrga_penggajian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'bulan_tahun', 'gaji_pokok', 'tunjangan',
        'potongan', 'total_gaji', 'status', 'tanggal_bayar'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getPenggajianPeriode($bulan, $tahun)
    {
        return $this->db->table('hrga_penggajian p')
            ->select('p.*, k.nama_lengkap, k.nip')
            ->join('hrga_karyawan k', 'k.id = p.karyawan_id')
            ->where('MONTH(p.bulan_tahun)', $bulan)
            ->where('YEAR(p.bulan_tahun)', $tahun)
            ->get()
            ->getResultArray();
    }

    public function getPenggajianBulanIniCount($bulan, $tahun)
    {
        return $this->db->table('hrga_penggajian')
            ->where('MONTH(bulan_tahun)', $bulan)
            ->where('YEAR(bulan_tahun)', $tahun)
            ->countAllResults();
    }

    public function getSlipGaji($id)
    {
        return $this->db->table('hrga_penggajian p')
            ->select('p.*, k.nama_lengkap, k.nip, k.jabatan')
            ->join('hrga_karyawan k', 'k.id = p.karyawan_id')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }
}