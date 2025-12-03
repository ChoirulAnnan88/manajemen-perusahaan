<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\InventarisModel;

class InventarisController extends BaseController
{
    protected $inventarisModel;

    public function __construct()
    {
        $this->inventarisModel = new InventarisModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $data = [
            'title' => 'Inventaris General',
            'inventaris' => $this->inventarisModel->getAllInventaris()
        ];
        return view('hrga/inventaris', $data);
    }

    public function simpan()
    {
        if (!$this->validate([
            'nama_barang' => 'required',
            'kategori' => 'required',
            'jumlah' => 'required|numeric',
            'kondisi' => 'required'
        ])) {
            return redirect()->to('/hrga/inventaris')->withInput()->with('errors', $this->validator->getErrors());
        }

        $kode_inventaris = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->inventarisModel->save([
            'kode_inventaris' => $kode_inventaris,
            'nama_barang' => $this->request->getPost('nama_barang'),
            'kategori' => $this->request->getPost('kategori'),
            'jumlah' => $this->request->getPost('jumlah'),
            'kondisi' => $this->request->getPost('kondisi'),
            'lokasi' => $this->request->getPost('lokasi'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/inventaris')->with('success', 'Data inventaris berhasil disimpan');
    }

    public function detail($id)
    {
        $inventaris = $this->inventarisModel->find($id);
        
        if (!$inventaris) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data inventaris tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $inventaris
        ]);
    }

    public function update($id)
    {
        // Debug: Log request
        // log_message('debug', 'Update request for ID: ' . $id);
        // log_message('debug', 'POST data: ' . print_r($this->request->getPost(), true));
        
        // Validasi
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nama_barang' => 'required',
            'kategori' => 'required',
            'jumlah' => 'required|numeric',
            'kondisi' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            session()->setFlashdata('errors', $errors);
            return redirect()->to('/hrga/inventaris')->withInput();
        }

        // Update data
        $data = [
            'nama_barang' => $this->request->getPost('nama_barang'),
            'kategori' => $this->request->getPost('kategori'),
            'jumlah' => $this->request->getPost('jumlah'),
            'kondisi' => $this->request->getPost('kondisi'),
            'lokasi' => $this->request->getPost('lokasi')
        ];

        // Cek apakah data ada
        $inventaris = $this->inventarisModel->find($id);
        if (!$inventaris) {
            session()->setFlashdata('error', 'Data tidak ditemukan');
            return redirect()->to('/hrga/inventaris');
        }

        try {
            $this->inventarisModel->update($id, $data);
            session()->setFlashdata('success', 'Data inventaris berhasil diperbarui');
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }

        return redirect()->to('/hrga/inventaris');
    }

    public function hapus($id)
    {
        $inventaris = $this->inventarisModel->find($id);
        
        if (!$inventaris) {
            return redirect()->to('/hrga/inventaris')->with('error', 'Data inventaris tidak ditemukan');
        }

        $this->inventarisModel->delete($id);

        return redirect()->to('/hrga/inventaris')->with('success', 'Data inventaris berhasil dihapus');
    }
}