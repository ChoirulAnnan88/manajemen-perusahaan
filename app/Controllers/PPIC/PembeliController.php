<?php

namespace App\Controllers\PPIC;

use App\Controllers\BaseController;
use App\Models\PPIC\PembeliModel;

class PembeliController extends BaseController
{
    protected $pembeliModel;

    public function __construct()
    {
        $this->pembeliModel = new PembeliModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Pembeli',
            'active_menu' => 'pembeli',
            'pembeli' => $this->pembeliModel->findAll(),
        ];
        return view('ppic/pembeli/dashboard', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Pembeli',
            'active_menu' => 'pembeli',
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/pembeli/create', $data);
    }

    public function store()
    {
        // Validasi input
        $rules = [
            'kode_pembeli' => 'required|max_length[50]|is_unique[ppic_pembeli.kode_pembeli]',
            'nama_perusahaan' => 'required|max_length[100]',
            'email' => 'valid_email|max_length[100]',
            'telepon' => 'max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/pembeli/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_pembeli' => $this->request->getPost('kode_pembeli'),
            'nama_perusahaan' => $this->request->getPost('nama_perusahaan'),
            'contact_person' => $this->request->getPost('contact_person'),
            'telepon' => $this->request->getPost('telepon'),
            'email' => $this->request->getPost('email'),
            'alamat' => $this->request->getPost('alamat')
        ];

        $this->pembeliModel->insert($data);
        return redirect()->to('/ppic/pembeli')->with('success', 'Pembeli berhasil ditambahkan.');
    }

    public function view($id)
    {
        $pembeli = $this->pembeliModel->find($id);
        if (!$pembeli) {
            return redirect()->to('/ppic/pembeli')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Pembeli',
            'active_menu' => 'pembeli',
            'pembeli' => $pembeli
        ];
        return view('ppic/pembeli/view', $data);
    }

    public function edit($id)
    {
        $pembeli = $this->pembeliModel->find($id);
        if (!$pembeli) {
            return redirect()->to('/ppic/pembeli')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Pembeli',
            'active_menu' => 'pembeli',
            'pembeli' => $pembeli,
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/pembeli/edit', $data);
    }

    public function update($id)
    {
        // Validasi input
        $rules = [
            'kode_pembeli' => 'required|max_length[50]|is_unique[ppic_pembeli.kode_pembeli,id,' . $id . ']',
            'nama_perusahaan' => 'required|max_length[100]',
            'email' => 'valid_email|max_length[100]',
            'telepon' => 'max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/pembeli/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_pembeli' => $this->request->getPost('kode_pembeli'),
            'nama_perusahaan' => $this->request->getPost('nama_perusahaan'),
            'contact_person' => $this->request->getPost('contact_person'),
            'telepon' => $this->request->getPost('telepon'),
            'email' => $this->request->getPost('email'),
            'alamat' => $this->request->getPost('alamat')
        ];

        $this->pembeliModel->update($id, $data);
        return redirect()->to('/ppic/pembeli')->with('success', 'Pembeli berhasil diperbarui.');
    }

    public function delete($id)
    {
        $pembeli = $this->pembeliModel->find($id);
        if (!$pembeli) {
            return redirect()->to('/ppic/pembeli')->with('error', 'Data tidak ditemukan.');
        }

        $this->pembeliModel->delete($id);
        return redirect()->to('/ppic/pembeli')->with('success', 'Pembeli berhasil dihapus.');
    }
}