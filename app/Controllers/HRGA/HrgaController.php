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
        // Inisialisasi models
        $karyawanModel = new \App\Models\HRGA\KaryawanModel();
        $absensiModel = new \App\Models\HRGA\AbsensiModel();
        $penggajianModel = new \App\Models\HRGA\PenggajianModel();
        $perizinanModel = new \App\Models\HRGA\PerizinanModel();
        
        $bulan = date('m');
        $tahun = date('Y');
        
        $data = [
            'title' => 'HRGA Dashboard',
            'stats' => [
                'total_karyawan' => $karyawanModel->countAll(),
                'absensi_hari_ini' => $absensiModel->getAbsensiHariIniCount(),
                'penggajian_bulan_ini' => $penggajianModel->getPenggajianBulanIniCount($bulan, $tahun),
                'perizinan_pending' => $perizinanModel->getPerizinanPendingCount()
            ]
        ];
        return view('hrga/dashboard', $data);
    }
}