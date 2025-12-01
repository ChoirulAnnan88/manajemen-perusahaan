<?php
namespace App\Models;

use CodeIgniter\Model;

class HrgaAbsensiModel extends Model
{
    protected $table = 'hrga_absensi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'tanggal', 'jam_masuk', 'jam_pulang',
        'status', 'keterangan', 'created_at'
    ];
    
    // HAPUS useTimestamps karena tabel tidak ada updated_at
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $dateFormat = 'datetime';
    
    public function getAbsensiHariIni()
    {
        return $this->where('tanggal', date('Y-m-d'))->findAll();
    }
    
    public function getRekapAbsensi($bulan, $tahun)
    {
        return $this->where('MONTH(tanggal)', $bulan)
                    ->where('YEAR(tanggal)', $tahun)
                    ->findAll();
    }
    
    public function getAbsensiKaryawan($karyawan_id, $start_date, $end_date)
    {
        return $this->where('karyawan_id', $karyawan_id)
                    ->where('tanggal >=', $start_date)
                    ->where('tanggal <=', $end_date)
                    ->findAll();
    }

    // FIX: Tambahkan method untuk join dengan karyawan
    public function getAbsensiWithKaryawan()
    {
        return $this->select('hrga_absensi.*, hrga_karyawan.nama_lengkap, hrga_karyawan.nip')
                    ->join('hrga_karyawan', 'hrga_karyawan.id = hrga_absensi.karyawan_id')
                    ->findAll();
    }

    public function getAbsensiHariIniWithKaryawan()
    {
        return $this->select('hrga_absensi.*, hrga_karyawan.nama_lengkap, hrga_karyawan.nip')
                    ->join('hrga_karyawan', 'hrga_karyawan.id = hrga_absensi.karyawan_id')
                    ->where('hrga_absensi.tanggal', date('Y-m-d'))
                    ->findAll();
    }
}