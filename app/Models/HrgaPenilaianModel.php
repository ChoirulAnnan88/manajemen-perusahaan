<?php
namespace App\Models;

use CodeIgniter\Model;

class HrgaPenilaianModel extends Model
{
    protected $table = 'hrga_penilaian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'periode', 'nilai_produktivitas', 'nilai_kedisiplinan',
        'nilai_kerjasama', 'nilai_total', 'catatan', 'created_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $dateFormat = 'datetime';
    
    public function getPenilaianKaryawan($karyawan_id)
    {
        return $this->where('karyawan_id', $karyawan_id)
                    ->orderBy('periode', 'DESC')
                    ->findAll();
    }
    
    public function getPenilaianPeriode($periode)
    {
        return $this->where('periode', $periode)->findAll();
    }
    
    public function calculateNilaiTotal($produktivitas, $kedisiplinan, $kerjasama)
    {
        // Calculate average of three scores
        return ($produktivitas + $kedisiplinan + $kerjasama) / 3;
    }
    
    public function getRataRataKaryawan($karyawan_id)
    {
        $result = $this->selectAvg('nilai_total', 'rata_rata')
                      ->where('karyawan_id', $karyawan_id)
                      ->first();
        return $result ? $result['rata_rata'] : 0;
    }

    // FIX: Tambahkan method untuk join dengan karyawan
    public function getPenilaianWithKaryawan()
    {
        return $this->select('hrga_penilaian.*, hrga_karyawan.nama_lengkap, hrga_karyawan.nip')
                    ->join('hrga_karyawan', 'hrga_karyawan.id = hrga_penilaian.karyawan_id')
                    ->orderBy('hrga_penilaian.periode', 'DESC')
                    ->findAll();
    }
}