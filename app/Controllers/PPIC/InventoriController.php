<?php

namespace App\Controllers\PPIC;

use App\Controllers\BaseController;
use App\Models\PPIC\InventoriModel;

class InventoriController extends BaseController
{
    protected $inventoriModel;

    public function __construct()
    {
        $this->inventoriModel = new InventoriModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Inventori',
            'active_menu' => 'inventori',
            'inventori' => $this->inventoriModel->findAll(),
        ];
        return view('ppic/inventori/dashboard', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Inventori',
            'active_menu' => 'inventori',
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/inventori/create', $data);
    }

    public function store()
    {
        // Validasi input
        $rules = [
            'kode_item' => 'required|max_length[50]|is_unique[ppic_inventori.kode_item]',
            'nama_item' => 'required|max_length[100]',
            'kategori' => 'required|max_length[50]',
            'stok_minimal' => 'required|integer|greater_than[0]',
            'stok_aktual' => 'required|integer|greater_than_equal_to[0]',
            'satuan' => 'required|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/inventori/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_item' => $this->request->getPost('kode_item'),
            'nama_item' => $this->request->getPost('nama_item'),
            'kategori' => $this->request->getPost('kategori'),
            'stok_minimal' => $this->request->getPost('stok_minimal'),
            'stok_aktual' => $this->request->getPost('stok_aktual'),
            'satuan' => $this->request->getPost('satuan'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        $this->inventoriModel->insert($data);
        return redirect()->to('/ppic/inventori')->with('success', 'Item inventori berhasil ditambahkan.');
    }

    public function view($id)
    {
        $item = $this->inventoriModel->find($id);
        if (!$item) {
            return redirect()->to('/ppic/inventori')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Inventori',
            'active_menu' => 'inventori',
            'item' => $item
        ];
        return view('ppic/inventori/view', $data);
    }

    public function edit($id)
    {
        $item = $this->inventoriModel->find($id);
        if (!$item) {
            return redirect()->to('/ppic/inventori')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Inventori',
            'active_menu' => 'inventori',
            'item' => $item,
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/inventori/edit', $data);
    }

    public function update($id)
    {
        // Validasi input
        $rules = [
            'kode_item' => 'required|max_length[50]|is_unique[ppic_inventori.kode_item,id,' . $id . ']',
            'nama_item' => 'required|max_length[100]',
            'kategori' => 'required|max_length[50]',
            'stok_minimal' => 'required|integer|greater_than[0]',
            'stok_aktual' => 'required|integer|greater_than_equal_to[0]',
            'satuan' => 'required|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/inventori/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_item' => $this->request->getPost('kode_item'),
            'nama_item' => $this->request->getPost('nama_item'),
            'kategori' => $this->request->getPost('kategori'),
            'stok_minimal' => $this->request->getPost('stok_minimal'),
            'stok_aktual' => $this->request->getPost('stok_aktual'),
            'satuan' => $this->request->getPost('satuan'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        $this->inventoriModel->update($id, $data);
        return redirect()->to('/ppic/inventori')->with('success', 'Item inventori berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->inventoriModel->find($id);
        if (!$item) {
            return redirect()->to('/ppic/inventori')->with('error', 'Data tidak ditemukan.');
        }

        $this->inventoriModel->delete($id);
        return redirect()->to('/ppic/inventori')->with('success', 'Item inventori berhasil dihapus.');
    }
}