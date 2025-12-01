<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['karyawan_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status', 'keterangan', 'created_at'];
    protected $useTimestamps = false;

    public function getAbsensiHariIni()
    {
        $today = date('Y-m-d');
        
        return $this->db->table('absensi a')
            ->select('a.*, k.nip, k.nama')
            ->join('karyawan k', 'k.id = a.karyawan_id', 'left')
            ->where('DATE(a.tanggal)', $today)
            ->orderBy('a.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getAbsensiHariIniCount()
    {
        $today = date('Y-m-d');
        
        return $this->db->table('absensi')
            ->where('DATE(tanggal)', $today)
            ->where('status', 'hadir')
            ->countAllResults();
    }

    public function getRiwayatAbsensi($bulan = null, $tahun = null, $karyawan_id = null)
    {
        $builder = $this->db->table('absensi a')
            ->select('a.*, k.nip, k.nama')
            ->join('karyawan k', 'k.id = a.karyawan_id', 'left');

        if ($bulan && $tahun) {
            $builder->where('MONTH(a.tanggal)', $bulan)
                   ->where('YEAR(a.tanggal)', $tahun);
        }

        if ($karyawan_id) {
            $builder->where('a.karyawan_id', $karyawan_id);
        }

        return $builder->orderBy('a.tanggal', 'DESC')
                      ->get()
                      ->getResultArray();
    }
}