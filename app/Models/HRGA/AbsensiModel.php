<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table = 'hrga_absensi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 
        'tanggal', 
        'jam_masuk', 
        'jam_pulang',
        'status', 
        'keterangan'
        // JANGAN tambahkan updated_at di sini!
    ];
    
    // **PERBAIKAN: Set useTimestamps ke FALSE karena tabel hanya punya created_at**
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    // HAPUS: protected $updatedField = 'updated_at';
    
    public function getAbsensiHariIni()
    {
        $today = date('Y-m-d');
        return $this->db->table('hrga_absensi a')
            ->select('a.*, k.nama_lengkap, k.nip')
            ->join('hrga_karyawan k', 'k.id = a.karyawan_id')
            ->where('a.tanggal', $today)
            ->orderBy('a.tanggal', 'DESC')
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
            ->join('hrga_karyawan k', 'k.id = a.karyawan_id');
        
        // Filter bulan dan tahun
        if ($bulan && $tahun) {
            $builder->where('MONTH(a.tanggal)', $bulan)
                    ->where('YEAR(a.tanggal)', $tahun);
        }
        
        // Filter karyawan jika dipilih
        if ($karyawan_id) {
            $builder->where('a.karyawan_id', $karyawan_id);
        }
        
        return $builder->orderBy('a.tanggal', 'DESC')
                      ->get()
                      ->getResultArray();
    }
    
    /**
     * Override insert untuk memastikan tidak ada updated_at
     */
    public function insert($data = null, bool $returnID = true)
    {
        // Hapus updated_at jika ada di data
        if (is_array($data) && array_key_exists('updated_at', $data)) {
            unset($data['updated_at']);
        }
        
        return parent::insert($data, $returnID);
    }
}