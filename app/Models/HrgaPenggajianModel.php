<?php
namespace App\Models;

use CodeIgniter\Model;

class HrgaPenggajianModel extends Model
{
    protected $table = 'hrga_penggajian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'bulan_tahun', 'gaji_pokok', 'tunjangan',
        'potongan', 'total_gaji', 'status', 'created_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $dateFormat = 'datetime';
    
    public function getPenggajianPeriode($bulan, $tahun)
    {
        $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01';
        return $this->where('bulan_tahun', $periode)->findAll();
    }
    
    public function getPenggajianKaryawan($karyawan_id)
    {
        return $this->where('karyawan_id', $karyawan_id)
                    ->orderBy('bulan_tahun', 'DESC')
                    ->findAll();
    }
    
    public function generateTotalGaji()
    {
        // Auto calculate total_gaji = gaji_pokok + tunjangan - potongan
        $data = $this->findAll();
        foreach ($data as $item) {
            $total = $item['gaji_pokok'] + $item['tunjangan'] - $item['potongan'];
            $this->update($item['id'], ['total_gaji' => $total]);
        }
    }

    // FIX: Tambahkan method untuk join dengan karyawan
    public function getPenggajianWithKaryawan($bulan, $tahun)
    {
        $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01';
        
        return $this->select('hrga_penggajian.*, hrga_karyawan.nama_lengkap, hrga_karyawan.nip')
                    ->join('hrga_karyawan', 'hrga_karyawan.id = hrga_penggajian.karyawan_id')
                    ->where('hrga_penggajian.bulan_tahun', $periode)
                    ->findAll();
    }
}