<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\PenggajianModel;
use App\Models\HRGA\KaryawanModel;

class PenggajianController extends BaseController
{
    protected $penggajianModel;
    protected $karyawanModel;

    public function __construct()
    {
        $this->penggajianModel = new PenggajianModel();
        $this->karyawanModel = new KaryawanModel();
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
        // PERBAIKAN: ganti penggajian_index menjadi penggajian
        return view('hrga/penggajian', $data);
    }

    public function generate()
    {
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title' => 'Generate Penggajian',
            'karyawan' => $this->karyawanModel->getAllKaryawan(),
            'bulan' => $bulan,
            'tahun' => $tahun
        ];
        return view('hrga/penggajian_generate', $data);
    }

    public function proses()
    {
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $karyawan_ids = $this->request->getPost('karyawan') ?? [];
        $tunjangans = $this->request->getPost('tunjangan') ?? [];
        $potongans = $this->request->getPost('potongan') ?? [];

        foreach ($karyawan_ids as $karyawan_id) {
            $karyawan = $this->karyawanModel->find($karyawan_id);
            if ($karyawan) {
                $tunjangan = $tunjangans[$karyawan_id] ?? 0;
                $potongan = $potongans[$karyawan_id] ?? 0;
                $total_gaji = $karyawan['gaji_pokok'] + $tunjangan - $potongan;

                $this->penggajianModel->save([
                    'karyawan_id' => $karyawan_id,
                    'bulan_tahun' => $tahun . '-' . $bulan . '-01',
                    'gaji_pokok' => $karyawan['gaji_pokok'],
                    'tunjangan' => $tunjangan,
                    'potongan' => $potongan,
                    'total_gaji' => $total_gaji,
                    'status' => 'draft',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return redirect()->to('/hrga/penggajian?bulan=' . $bulan . '&tahun=' . $tahun)->with('success', 'Penggajian berhasil digenerate');
    }

    public function bayar($id)
    {
        $this->penggajianModel->update($id, [
            'status' => 'dibayar',
            'tanggal_bayar' => date('Y-m-d')
        ]);

        return redirect()->to('/hrga/penggajian')->with('success', 'Penggajian berhasil ditandai sebagai dibayar');
    }

    public function slip($id)
    {
        $data = [
            'title' => 'Slip Gaji',
            'penggajian' => $this->penggajianModel->getSlipGaji($id)
        ];
        return view('hrga/slip_gaji', $data);
    }
}