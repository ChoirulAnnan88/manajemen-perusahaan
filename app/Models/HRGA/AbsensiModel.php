<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table = 'hrga_absensi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'tanggal', 'jam_masuk', 'jam_pulang',
        'status', 'keterangan'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';

    public function getAbsensiHariIni()
    {
        $today = date('Y-m-d');
        return $this->db->table('hrga_absensi a')
            ->select('a.*, k.nama_lengkap, k.nip')
            ->join('hrga_karyawan k', 'k.id = a.karyawan_id')
            ->where('a.tanggal', $today)
            ->get()
            ->getResultArray();
    }

    public function getAbsensiHariIniCount()
    {
        $today = date('Y-m-d');
        return $this->db->table('hrga_absensi')
            ->where('tanggal', $today)
            ->countAllResults();
    }

    public function getRiwayatAbsensi($bulan, $tahun, $karyawan_id = null)
    {
        $builder = $this->db->table('hrga_absensi a')
            ->select('a.*, k.nama_lengkap, k.nip')
            ->join('hrga_karyawan k', 'k.id = a.karyawan_id')
            ->where('MONTH(a.tanggal)', $bulan)
            ->where('YEAR(a.tanggal)', $tahun);
        
        if ($karyawan_id) {
            $builder->where('a.karyawan_id', $karyawan_id);
        }
        
        return $builder->get()->getResultArray();
    }
}