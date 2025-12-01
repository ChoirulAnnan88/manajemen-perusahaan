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
        $data = [
            'title' => 'Absensi & Waktu Kerja',
            'absensi' => $this->absensiModel->getAbsensiHariIni(),
            'karyawan' => $this->karyawanModel->getAllKaryawan(),
            'isManager' => true
        ];
        return view('hrga/absensi', $data);
    }

    public function riwayat()
    {
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $karyawan_id = $this->request->getGet('karyawan_id');

        $data = [
            'title' => 'Riwayat Absensi',
            'absensi' => $this->absensiModel->getRiwayatAbsensi($bulan, $tahun, $karyawan_id),
            'karyawans' => $this->karyawanModel->getAllKaryawan(),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'karyawan_id' => $karyawan_id
        ];
        return view('hrga/absensi_riwayat', $data);
    }

    public function simpan()
    {
        if (!$this->validate([
            'karyawan_id' => 'required',
            'tanggal' => 'required',
            'status' => 'required'
        ])) {
            return redirect()->to('/hrga/absensi')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->absensiModel->save([
            'karyawan_id' => $this->request->getPost('karyawan_id'),
            'tanggal' => $this->request->getPost('tanggal'),
            'jam_masuk' => $this->request->getPost('jam_masuk'),
            'jam_pulang' => $this->request->getPost('jam_pulang'),
            'status' => $this->request->getPost('status'),
            'keterangan' => $this->request->getPost('keterangan'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/absensi')->with('success', 'Data absensi berhasil disimpan');
    }
}