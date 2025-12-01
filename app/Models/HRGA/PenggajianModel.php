<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class PenggajianModel extends Model
{
    protected $table = 'penggajian';
    protected $primaryKey = 'id';
    protected $allowedFields = ['karyawan_id', 'bulan', 'tahun', 'gaji_pokok', 'tunjangan', 'potongan', 'total_gaji', 'status', 'tanggal_bayar', 'created_at'];
    protected $useTimestamps = false;

    public function getPenggajianPeriode($bulan, $tahun)
    {
        return $this->db->table('penggajian p')
            ->select('p.*, k.nip, k.nama')
            ->join('karyawan k', 'k.id = p.karyawan_id', 'left')
            ->where('p.bulan', $bulan)
            ->where('p.tahun', $tahun)
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPenggajianBulanIniCount($bulan, $tahun)
    {
        return $this->db->table('penggajian')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->countAllResults();
    }

    public function getSlipGaji($id)
    {
        return $this->db->table('penggajian p')
            ->select('p.*, k.*')
            ->join('karyawan k', 'k.id = p.karyawan_id', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }
}