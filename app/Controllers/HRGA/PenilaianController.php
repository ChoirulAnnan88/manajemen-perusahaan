<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\PenilaianModel;
use App\Models\HRGA\KaryawanModel;

class PenilaianController extends BaseController
{
    protected $penilaianModel;
    protected $karyawanModel;

    public function __construct()
    {
        $this->penilaianModel = new PenilaianModel();
        $this->karyawanModel = new KaryawanModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Penilaian Kinerja',
            'penilaian' => $this->penilaianModel->getAllWithKaryawan(),
            'karyawan' => $this->karyawanModel->getAllKaryawan()
        ];
        // PERBAIKAN: ganti penilaian_index menjadi penilaian
        return view('hrga/penilaian', $data);
    }

    public function simpan()
    {
        if (!$this->validate([
            'karyawan_id' => 'required',
            'periode' => 'required',
            'nilai_produktivitas' => 'required|numeric',
            'nilai_kedisiplinan' => 'required|numeric',
            'nilai_kerjasama' => 'required|numeric'
        ])) {
            return redirect()->to('/hrga/penilaian')->withInput()->with('errors', $this->validator->getErrors());
        }

        $nilai_produktivitas = (float)$this->request->getPost('nilai_produktivitas');
        $nilai_kedisiplinan = (float)$this->request->getPost('nilai_kedisiplinan');
        $nilai_kerjasama = (float)$this->request->getPost('nilai_kerjasama');
        $nilai_total = ($nilai_produktivitas + $nilai_kedisiplinan + $nilai_kerjasama) / 3;

        $this->penilaianModel->save([
            'karyawan_id' => $this->request->getPost('karyawan_id'),
            'periode' => $this->request->getPost('periode'),
            'nilai_produktivitas' => $nilai_produktivitas,
            'nilai_kedisiplinan' => $nilai_kedisiplinan,
            'nilai_kerjasama' => $nilai_kerjasama,
            'nilai_total' => $nilai_total,
            'catatan' => $this->request->getPost('catatan'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/penilaian')->with('success', 'Data penilaian berhasil disimpan');
    }
}