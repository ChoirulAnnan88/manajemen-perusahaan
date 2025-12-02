<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\AbsensiModel;
use App\Models\HRGA\KaryawanModel;

class AbsensiController extends BaseController
{
    protected $absensiModel;
    protected $karyawanModel;
    
    public function __construct()
    {
        $this->absensiModel = new AbsensiModel();
        $this->karyawanModel = new KaryawanModel();
    }

    public function index()
    {
        // Cek apakah user adalah manager
        $session = session();
        $isManager = ($session->get('role') == 'manager');
        
        // Ambil data absensi hari ini
        $absensi = $this->absensiModel->getAbsensiHariIni();
        
        // Ambil data karyawan untuk dropdown (hanya untuk manager)
        $karyawan = [];
        if ($isManager) {
            $karyawan = $this->karyawanModel->findAll();
        }
        
        $data = [
            'title' => 'Absensi HRGA',
            'absensi' => $absensi,
            'karyawan' => $karyawan,
            'isManager' => $isManager
        ];
        
        return view('hrga/absensi', $data);
    }

    public function simpan()
    {
        // **PERBAIKAN UTAMA: Hapus updated_at dari data**
        $data = [
            'karyawan_id' => $this->request->getPost('karyawan_id'),
            'tanggal'     => $this->request->getPost('tanggal'),
            'jam_masuk'   => $this->request->getPost('jam_masuk'),
            'jam_pulang'  => $this->request->getPost('jam_pulang'),
            'status'      => $this->request->getPost('status'),
            'keterangan'  => $this->request->getPost('keterangan')
            // TIDAK ADA updated_at di sini!
        ];
        
        // Validasi
        if (empty($data['karyawan_id']) || empty($data['tanggal']) || empty($data['status'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Karyawan, Tanggal, dan Status wajib diisi!');
        }
        
        try {
            $this->absensiModel->insert($data);
            
            return redirect()->to('/hrga/absensi')
                ->with('success', 'Data absensi berhasil disimpan!');
                
        } catch (\Exception $e) {
            // Log error untuk debugging
            log_message('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function riwayat()
    {
        // Ambil filter dari GET
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $karyawan_id = $this->request->getGet('karyawan_id');
        
        // Ambil data karyawan untuk dropdown filter
        $karyawans = $this->karyawanModel->findAll();
        
        // Ambil data riwayat absensi
        $absensi = $this->absensiModel->getRiwayatAbsensi($bulan, $tahun, $karyawan_id);
        
        $data = [
            'title' => 'Riwayat Absensi HRGA',
            'absensi' => $absensi,
            'karyawans' => $karyawans,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'karyawan_id' => $karyawan_id
        ];
        
        return view('hrga/absensi_riwayat', $data);
    }
}