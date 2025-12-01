<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;

class HrgaController extends BaseController
{
    public function index()
    {
        return redirect()->to('/hrga/dashboard');
    }

    public function dashboard()
    {
        $db = \Config\Database::connect();
        
        // Hitung statistik dengan QUERY LANGSUNG ke tabel yang benar
        $totalKaryawan = $db->table('hrga_karyawan')->countAllResults();
        
        $absensiHariIni = $db->table('hrga_absensi')
            ->where('tanggal', date('Y-m-d'))
            ->countAllResults();
        
        $bulan = date('m');
        $tahun = date('Y');
        
        // Perbaiki query penggajian - kolom 'bulan_tahun' perlu diekstrak
        $penggajianBulanIni = $db->table('hrga_penggajian')
            ->where("DATE_FORMAT(bulan_tahun, '%Y-%m')", $tahun . '-' . $bulan)
            ->countAllResults();
        
        $perizinanPending = $db->table('hrga_perizinan')
            ->where('status', 'pending')
            ->countAllResults();
        
        $data = [
            'title' => 'HRGA Dashboard',
            'stats' => [
                'total_karyawan' => $totalKaryawan,
                'absensi_hari_ini' => $absensiHariIni,
                'penggajian_bulan_ini' => $penggajianBulanIni,
                'perizinan_pending' => $perizinanPending
            ]
        ];
        
        return view('hrga/dashboard', $data);
    }
}