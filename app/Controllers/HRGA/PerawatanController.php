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

        $this->perawatanModel->save([
            'kode_perawatan' => $kode_perawatan,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'lokasi' => $this->request->getPost('lokasi'),
            'tanggal_perawatan' => $this->request->getPost('tanggal_perawatan'),
            'biaya' => $this->request->getPost('biaya') ?? 0,
            'status' => $this->request->getPost('status') ?? 'planned',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/perawatan')->with('success', 'Data perawatan berhasil disimpan');
    }
}