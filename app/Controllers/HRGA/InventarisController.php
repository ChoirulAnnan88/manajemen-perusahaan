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

        $kode_barang = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->inventarisModel->save([
            'kode_barang' => $kode_barang,
            'nama_barang' => $this->request->getPost('nama_barang'),
            'kategori' => $this->request->getPost('kategori'),
            'jumlah' => $this->request->getPost('jumlah'),
            'kondisi' => $this->request->getPost('kondisi'),
            'lokasi' => $this->request->getPost('lokasi'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/inventaris')->with('success', 'Data inventaris berhasil disimpan');
    }
}