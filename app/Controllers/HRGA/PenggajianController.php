<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\PenggajianModel;
use App\Models\HRGA\KaryawanModel;
use App\Models\DivisiModel;

class PenggajianController extends BaseController
{
    protected $penggajianModel;
    protected $karyawanModel;
    protected $divisiModel;

    public function __construct()
    {
        $this->penggajianModel = new PenggajianModel();
        $this->karyawanModel = new KaryawanModel();
        $this->divisiModel = new DivisiModel();
        helper('form');
    }

    public function index()
    {
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title' => 'Penggajian',
            'penggajian' => $this->penggajianModel->getPenggajianPeriode($bulan, $tahun),
            'bulan' => $bulan,
            'tahun' => $tahun
        ];
        return view('hrga/penggajian', $data);
    }

    public function generate()
    {
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title' => 'Generate Penggajian',
            'karyawan' => $this->karyawanModel->findAllWithDivisi(),
            'allDivisi' => $this->divisiModel->findAll(), // Tambahkan data semua divisi untuk filter
            'bulan' => $bulan,
            'tahun' => $tahun
        ];
        return view('hrga/penggajian_generate', $data);
    }

    // Helper function untuk membersihkan angka
    private function cleanNumber($value)
    {
        if (empty($value) || $value === '') return 0;
        // Hapus semua karakter non-digit
        $cleaned = preg_replace('/[^\d]/', '', $value);
        return $cleaned === '' ? 0 : (int)$cleaned;
    }

    public function edit($id)
    {
        $penggajian = $this->penggajianModel->find($id);
        if (!$penggajian) {
            return redirect()->to('/hrga/penggajian')->with('error', 'Data tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Data Penggajian',
            'penggajian' => $penggajian,
            'karyawan' => $this->karyawanModel->findAllWithDivisi(),
            'validation' => \Config\Services::validation()
        ];
        return view('hrga/penggajian_edit', $data);
    }

    public function update($id)
    {
        // Validasi sederhana
        $gaji_pokok_input = $this->request->getPost('gaji_pokok');
        $tunjangan_input = $this->request->getPost('tunjangan') ?? '0';
        $potongan_input = $this->request->getPost('potongan') ?? '0';
        $status = $this->request->getPost('status');
        
        $gaji_pokok = $this->cleanNumber($gaji_pokok_input);
        $tunjangan = $this->cleanNumber($tunjangan_input);
        $potongan = $this->cleanNumber($potongan_input);
        
        $total_gaji = $gaji_pokok + $tunjangan - $potongan;
        
        $data = [
            'gaji_pokok' => $gaji_pokok,
            'tunjangan' => $tunjangan,
            'potongan' => $potongan,
            'total_gaji' => $total_gaji,
            'status' => $status
        ];

        // Jika status dibayar, set tanggal bayar
        if ($status == 'dibayar') {
            $data['tanggal_bayar'] = date('Y-m-d');
        }

        $this->penggajianModel->update($id, $data);
        return redirect()->to('/hrga/penggajian')->with('success', 'Data penggajian berhasil diupdate');
    }

    public function hapus($id)
    {
        $penggajian = $this->penggajianModel->find($id);
        if (!$penggajian) {
            return redirect()->to('/hrga/penggajian')->with('error', 'Data tidak ditemukan');
        }

        $this->penggajianModel->delete($id);
        return redirect()->to('/hrga/penggajian')->with('success', 'Data penggajian berhasil dihapus');
    }

    public function proses()
    {
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $karyawan_ids = $this->request->getPost('karyawan') ?? [];
        $tunjangans = $this->request->getPost('tunjangan') ?? [];
        $potongans = $this->request->getPost('potongan') ?? [];

        if (empty($karyawan_ids)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 karyawan untuk digaji');
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($karyawan_ids as $karyawan_id) {
            $karyawan = $this->karyawanModel->find($karyawan_id);
            if ($karyawan) {
                // Cek apakah sudah ada
                if ($this->penggajianModel->sudahAdaPenggajian($karyawan_id, $bulan, $tahun)) {
                    $failedCount++;
                    continue;
                }

                $tunjangan = $tunjangans[$karyawan_id] ?? 0;
                $potongan = $potongans[$karyawan_id] ?? 0;
                $total_gaji = $karyawan['gaji_pokok'] + $tunjangan - $potongan;

                $this->penggajianModel->save([
                    'karyawan_id' => $karyawan_id,
                    'bulan_tahun' => $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01',
                    'gaji_pokok' => $karyawan['gaji_pokok'],
                    'tunjangan' => $tunjangan,
                    'potongan' => $potongan,
                    'total_gaji' => $total_gaji,
                    'status' => 'draft'
                ]);
                $successCount++;
            }
        }

        $message = 'Penggajian berhasil digenerate untuk ' . $successCount . ' karyawan';
        if ($failedCount > 0) {
            $message .= ' (' . $failedCount . ' karyawan sudah memiliki data penggajian)';
        }

        return redirect()->to('/hrga/penggajian?bulan=' . $bulan . '&tahun=' . $tahun)->with('success', $message);
    }

    public function bayar($id)
    {
        $penggajian = $this->penggajianModel->find($id);
        if (!$penggajian) {
            return redirect()->to('/hrga/penggajian')->with('error', 'Data tidak ditemukan');
        }

        $this->penggajianModel->update($id, [
            'status' => 'dibayar',
            'tanggal_bayar' => date('Y-m-d')
        ]);

        return redirect()->back()->with('success', 'Penggajian berhasil ditandai sebagai dibayar');
    }

    public function slip($id)
    {
        $penggajian = $this->penggajianModel->getSlipGaji($id);
        
        if (!$penggajian) {
            return redirect()->to('/hrga/penggajian')->with('error', 'Data slip gaji tidak ditemukan');
        }

        $all_ids = $this->penggajianModel
            ->select('id')
            ->orderBy('bulan_tahun', 'DESC')
            ->orderBy('karyawan_id', 'ASC')
            ->findAll();
    
        $current_index = array_search($id, array_column($all_ids, 'id'));
        $prev_id = isset($all_ids[$current_index - 1]) ? $all_ids[$current_index - 1]['id'] : null;
        $next_id = isset($all_ids[$current_index + 1]) ? $all_ids[$current_index + 1]['id'] : null;

        $data = [
            'title' => 'Slip Gaji - ' . $penggajian['nama_lengkap'],
            'penggajian' => $penggajian
        ];
        return view('hrga/slip_gaji', $data);
    }

    public function cetak_semua($bulan, $tahun)
    {
        $penggajian = $this->penggajianModel->getPenggajianPeriode($bulan, $tahun);
        
        $data = [
            'title' => 'Laporan Penggajian ' . date('F Y', strtotime($tahun . '-' . $bulan . '-01')),
            'penggajian' => $penggajian,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'total_gaji' => array_sum(array_column($penggajian, 'total_gaji'))
        ];
        return view('hrga/laporan_penggajian', $data);
    }
}