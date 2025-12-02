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
            ->select('p.*, k.nama_lengkap, k.nip, d.nama_divisi')
            ->join('hrga_karyawan k', 'k.id = p.karyawan_id')
            ->join('divisi d', 'd.id = k.divisi_id', 'left')
            ->where('MONTH(p.bulan_tahun)', $bulan)
            ->where('YEAR(p.bulan_tahun)', $tahun)
            ->orderBy('k.nama_lengkap', 'ASC')
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
            ->select('p.*, k.nama_lengkap, k.nip, k.jabatan, d.nama_divisi')
            ->join('hrga_karyawan k', 'k.id = p.karyawan_id')
            ->join('divisi d', 'd.id = k.divisi_id', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }

    public function sudahAdaPenggajian($karyawan_id, $bulan, $tahun)
    {
        return $this->where('karyawan_id', $karyawan_id)
            ->where('MONTH(bulan_tahun)', $bulan)
            ->where('YEAR(bulan_tahun)', $tahun)
            ->countAllResults() > 0;
    }
}