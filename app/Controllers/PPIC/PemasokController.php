<?php

namespace App\Controllers\PPIC;

use App\Controllers\BaseController;
use App\Models\PPIC\PemasokModel;

class PemasokController extends BaseController
{
    protected $pemasokModel;

    public function __construct()
    {
        $this->pemasokModel = new PemasokModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Pemasok',
            'active_menu' => 'pemasok',
            'pemasok' => $this->pemasokModel->findAll(),
        ];
        return view('ppic/pemasok/dashboard', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Pemasok',
            'active_menu' => 'pemasok',
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/pemasok/create', $data);
    }

    public function store()
    {
        // Validasi input
        $rules = [
            'kode_pemasok' => 'required|max_length[50]|is_unique[ppic_pemasok.kode_pemasok]',
            'nama_perusahaan' => 'required|max_length[100]',
            'email' => 'valid_email|max_length[100]',
            'telepon' => 'max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/pemasok/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_pemasok' => $this->request->getPost('kode_pemasok'),
            'nama_perusahaan' => $this->request->getPost('nama_perusahaan'),
            'contact_person' => $this->request->getPost('contact_person'),
            'telepon' => $this->request->getPost('telepon'),
            'email' => $this->request->getPost('email'),
            'alamat' => $this->request->getPost('alamat')
        ];

        $this->pemasokModel->insert($data);
        return redirect()->to('/ppic/pemasok')->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function view($id)
    {
        $pemasok = $this->pemasokModel->find($id);
        if (!$pemasok) {
            return redirect()->to('/ppic/pemasok')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Pemasok',
            'active_menu' => 'pemasok',
            'pemasok' => $pemasok
        ];
        return view('ppic/pemasok/view', $data);
    }

    public function edit($id)
    {
        $pemasok = $this->pemasokModel->find($id);
        if (!$pemasok) {
            return redirect()->to('/ppic/pemasok')->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Pemasok',
            'active_menu' => 'pemasok',
            'pemasok' => $pemasok,
            'validation' => \Config\Services::validation()
        ];
        return view('ppic/pemasok/edit', $data);
    }

    public function update($id)
    {
        // Validasi input
        $rules = [
            'kode_pemasok' => 'required|max_length[50]|is_unique[ppic_pemasok.kode_pemasok,id,' . $id . ']',
            'nama_perusahaan' => 'required|max_length[100]',
            'email' => 'valid_email|max_length[100]',
            'telepon' => 'max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/pemasok/edit/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_pemasok' => $this->request->getPost('kode_pemasok'),
            'nama_perusahaan' => $this->request->getPost('nama_perusahaan'),
            'contact_person' => $this->request->getPost('contact_person'),
            'telepon' => $this->request->getPost('telepon'),
            'email' => $this->request->getPost('email'),
            'alamat' => $this->request->getPost('alamat')
        ];

        $this->pemasokModel->update($id, $data);
        return redirect()->to('/ppic/pemasok')->with('success', 'Pemasok berhasil diperbarui.');
    }

    public function delete($id)
    {
        $pemasok = $this->pemasokModel->find($id);
        if (!$pemasok) {
            return redirect()->to('/ppic/pemasok')->with('error', 'Data tidak ditemukan.');
        }

        $this->pemasokModel->delete($id);
        return redirect()->to('/ppic/pemasok')->with('success', 'Pemasok berhasil dihapus.');
    }
}