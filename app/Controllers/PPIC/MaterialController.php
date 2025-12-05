<?php

namespace App\Controllers\PPIC;

use App\Controllers\BaseController;
use App\Models\PPIC\MaterialModel;

class MaterialController extends BaseController
{
    protected $materialModel;

    public function __construct()
    {
        $this->materialModel = new MaterialModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Material',
            'active_menu' => 'material',
            'material' => $this->materialModel->findAll(),
        ];
        return view('ppic/material/dashboard', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Material',
            'active_menu' => 'material',
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/material/create', $data);
    }

    public function store()
    {
        // Validasi input
        $rules = [
            'kode_material' => 'required|max_length[50]|is_unique[ppic_material.kode_material]',
            'nama_material' => 'required|max_length[100]',
            'stok_aktual' => 'required|integer|greater_than_equal_to[0]',
            'satuan' => 'required|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/material/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_material' => $this->request->getPost('kode_material'),
            'nama_material' => $this->request->getPost('nama_material'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'stok_aktual' => $this->request->getPost('stok_aktual'),
            'satuan' => $this->request->getPost('satuan'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        $this->materialModel->insert($data);
        return redirect()->to('/ppic/material')->with('success', 'Material berhasil ditambahkan.');
    }

    public function view($id)
    {
        $material = $this->materialModel->find($id);
        if (!$material) {
            return redirect()->to('/ppic/material')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Material',
            'active_menu' => 'material',
            'material' => $material
        ];
        return view('ppic/material/view', $data);
    }

    public function edit($id)
    {
        $material = $this->materialModel->find($id);
        if (!$material) {
            return redirect()->to('/ppic/material')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Material',
            'active_menu' => 'material',
            'material' => $material,
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/material/edit', $data);
    }

    public function update($id)
    {
        // Validasi input
        $rules = [
            'kode_material' => 'required|max_length[50]|is_unique[ppic_material.kode_material,id,' . $id . ']',
            'nama_material' => 'required|max_length[100]',
            'stok_aktual' => 'required|integer|greater_than_equal_to[0]',
            'satuan' => 'required|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/material/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_material' => $this->request->getPost('kode_material'),
            'nama_material' => $this->request->getPost('nama_material'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'stok_aktual' => $this->request->getPost('stok_aktual'),
            'satuan' => $this->request->getPost('satuan'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        $this->materialModel->update($id, $data);
        return redirect()->to('/ppic/material')->with('success', 'Material berhasil diperbarui.');
    }

    public function delete($id)
    {
        $material = $this->materialModel->find($id);
        if (!$material) {
            return redirect()->to('/ppic/material')->with('error', 'Data tidak ditemukan.');
        }

        $this->materialModel->delete($id);
        return redirect()->to('/ppic/material')->with('success', 'Material berhasil dihapus.');
    }
}