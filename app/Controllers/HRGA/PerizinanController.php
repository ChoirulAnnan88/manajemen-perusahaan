<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\PerizinanModel;
use App\Models\HRGA\KaryawanModel;

class PerizinanController extends BaseController
{
    protected $perizinanModel;
    protected $karyawanModel;

    public function __construct()
    {
        $this->perizinanModel = new PerizinanModel();
        $this->karyawanModel = new KaryawanModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Perizinan',
            'perizinan' => $this->perizinanModel->getAllWithKaryawan(),
            'karyawan' => $this->karyawanModel->getAllKaryawan()
        ];
        // PERBAIKAN: ganti perizinan_index menjadi perizinan
        return view('hrga/perizinan', $data);
    }

    public function ajukan()
    {
        if (!$this->validate([
            'karyawan_id' => 'required',
            'jenis_izin' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            'alasan' => 'required'
        ])) {
            return redirect()->to('/hrga/perizinan')->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->perizinanModel->save([
            'karyawan_id' => $this->request->getPost('karyawan_id'),
            'jenis_izin' => $this->request->getPost('jenis_izin'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'alasan' => $this->request->getPost('alasan'),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/perizinan')->with('success', 'Perizinan berhasil diajukan');
    }

    public function approve($id)
    {
        $this->perizinanModel->update($id, [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/perizinan')->with('success', 'Perizinan berhasil disetujui');
    }

    public function reject($id)
    {
        $this->perizinanModel->update($id, [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/perizinan')->with('success', 'Perizinan berhasil ditolak');
    }
}