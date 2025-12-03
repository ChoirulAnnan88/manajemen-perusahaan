<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\PerawatanModel;

class PerawatanController extends BaseController
{
    protected $perawatanModel;

    public function __construct()
    {
        $this->perawatanModel = new PerawatanModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $data = [
            'title' => 'Perawatan Gedung',
            'perawatan' => $this->perawatanModel->getAllPerawatan()
        ];
        return view('hrga/perawatan', $data);
    }

    public function simpan()
    {
        if (!$this->validate([
            'deskripsi' => 'required',
            'lokasi' => 'required',
            'tanggal_perawatan' => 'required'
        ])) {
            return redirect()->to('/hrga/perawatan')->withInput()->with('errors', $this->validator->getErrors());
        }

        $kode_perawatan = 'PRW-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $data = [
            'kode_perawatan' => $kode_perawatan,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'lokasi' => $this->request->getPost('lokasi'),
            'tanggal_perawatan' => $this->request->getPost('tanggal_perawatan'),
            'biaya' => $this->request->getPost('biaya') ?? 0,
            'status' => $this->request->getPost('status') ?? 'planned',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->perawatanModel->insert($data);

        return redirect()->to('/hrga/perawatan')->with('success', 'Data perawatan berhasil disimpan');
    }

    // TAMBAHKAN FUNGSI UPDATE
    public function update($id)
    {
        // Cek apakah data ada
        $perawatan = $this->perawatanModel->find($id);
        if (!$perawatan) {
            session()->setFlashdata('error', 'Data perawatan tidak ditemukan');
            return redirect()->to('/hrga/perawatan');
        }

        // Validasi
        if (!$this->validate([
            'deskripsi' => 'required',
            'lokasi' => 'required',
            'tanggal_perawatan' => 'required'
        ])) {
            return redirect()->to('/hrga/perawatan')->withInput()->with('errors', $this->validator->getErrors());
        }

        // Update data
        $data = [
            'deskripsi' => $this->request->getPost('deskripsi'),
            'lokasi' => $this->request->getPost('lokasi'),
            'tanggal_perawatan' => $this->request->getPost('tanggal_perawatan'),
            'biaya' => $this->request->getPost('biaya') ?? 0,
            'status' => $this->request->getPost('status') ?? 'planned'
        ];

        $this->perawatanModel->update($id, $data);

        session()->setFlashdata('success', 'Data perawatan berhasil diperbarui');
        return redirect()->to('/hrga/perawatan');
    }

    // TAMBAHKAN FUNGSI HAPUS
    public function hapus($id)
    {
        // Cek apakah data ada
        $perawatan = $this->perawatanModel->find($id);
        
        if (!$perawatan) {
            return redirect()->to('/hrga/perawatan')->with('error', 'Data perawatan tidak ditemukan');
        }

        $this->perawatanModel->delete($id);

        return redirect()->to('/hrga/perawatan')->with('success', 'Data perawatan berhasil dihapus');
    }
}