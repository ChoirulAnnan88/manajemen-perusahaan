<?php

namespace App\Controllers\PPIC;

use App\Controllers\BaseController;
use App\Models\PPIC\ProduksiModel;
use App\Models\PPIC\InventoriModel;

class ProduksiController extends BaseController
{
    protected $produksiModel;
    protected $inventoriModel;

    public function __construct()
    {
        $this->produksiModel = new ProduksiModel();
        $this->inventoriModel = new InventoriModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Produksi',
            'active_menu' => 'produksi',
            'produksi' => $this->produksiModel->findAll(),
        ];
        return view('ppic/produksi/dashboard', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Produksi',
            'active_menu' => 'produksi',
            'produk_list' => $this->inventoriModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/produksi/create', $data);
    }

    public function store()
    {
        // Validasi input
        $rules = [
            'nomor_plan' => 'required|max_length[50]|is_unique[ppic_produksi.nomor_plan]',
            'produk' => 'required|max_length[100]',
            'jumlah_target' => 'required|integer|greater_than[0]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'status' => 'required|in_list[planned,progress,completed,canceled]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/produksi/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nomor_plan' => $this->request->getPost('nomor_plan'),
            'produk_id' => $this->request->getPost('produk_id'),
            'produk' => $this->request->getPost('produk'),
            'jumlah_target' => $this->request->getPost('jumlah_target'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'status' => $this->request->getPost('status'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        $this->produksiModel->insert($data);
        return redirect()->to('/ppic/produksi')->with('success', 'Rencana produksi berhasil ditambahkan.');
    }

    public function view($id)
    {
        $produksi = $this->produksiModel->find($id);
        if (!$produksi) {
            return redirect()->to('/ppic/produksi')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Produksi',
            'active_menu' => 'produksi',
            'produksi' => $produksi
        ];
        return view('ppic/produksi/view', $data);
    }

    public function edit($id)
    {
        $produksi = $this->produksiModel->find($id);
        if (!$produksi) {
            return redirect()->to('/ppic/produksi')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Produksi',
            'active_menu' => 'produksi',
            'produksi' => $produksi,
            'produk_list' => $this->inventoriModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/produksi/edit', $data);
    }

    public function update($id)
    {
        // Validasi input
        $rules = [
            'nomor_plan' => 'required|max_length[50]|is_unique[ppic_produksi.nomor_plan,id,' . $id . ']',
            'produk' => 'required|max_length[100]',
            'jumlah_target' => 'required|integer|greater_than[0]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'status' => 'required|in_list[planned,progress,completed,canceled]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/produksi/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nomor_plan' => $this->request->getPost('nomor_plan'),
            'produk_id' => $this->request->getPost('produk_id'),
            'produk' => $this->request->getPost('produk'),
            'jumlah_target' => $this->request->getPost('jumlah_target'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'status' => $this->request->getPost('status'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        $this->produksiModel->update($id, $data);
        return redirect()->to('/ppic/produksi')->with('success', 'Rencana produksi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $produksi = $this->produksiModel->find($id);
        if (!$produksi) {
            return redirect()->to('/ppic/produksi')->with('error', 'Data tidak ditemukan.');
        }

        $this->produksiModel->delete($id);
        return redirect()->to('/ppic/produksi')->with('success', 'Rencana produksi berhasil dihapus.');
    }
}