<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\KaryawanModel;

class KaryawanController extends BaseController
{
    protected $karyawanModel;

    public function __construct()
    {
        $this->karyawanModel = new KaryawanModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Data Karyawan',
            'karyawan' => $this->karyawanModel->getAllKaryawan()
        ];
        return view('hrga/karyawan_index', $data);
    }

    public function tambah()
    {
        $data = [
            'title' => 'Tambah Data Karyawan',
            'divisi' => $this->karyawanModel->getDivisi()
        ];
        return view('hrga/karyawan_tambah', $data);
    }

    public function store()
    {
        // Validasi sesuai form views
        if (!$this->validate([
            'nip' => 'required|is_unique[karyawan.nip]',
            'nama_lengkap' => 'required',
            'divisi_id' => 'required',
            'jabatan' => 'required',
            'tanggal_masuk' => 'required',
            'status_karyawan' => 'required',
            'gaji_pokok' => 'required|numeric'
        ])) {
            return redirect()->to('/hrga/karyawan/tambah')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Mapping field sesuai database
        $this->karyawanModel->save([
            'nip' => $this->request->getPost('nip'),
            'nama' => $this->request->getPost('nama_lengkap'),
            'divisi' => $this->getDivisiName($this->request->getPost('divisi_id')),
            'jabatan' => $this->request->getPost('jabatan'),
            'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
            'status' => $this->request->getPost('status_karyawan'),
            'gaji_pokok' => $this->request->getPost('gaji_pokok'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/karyawan')->with('success', 'Data karyawan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        
        if (!$karyawan) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Data tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Data Karyawan',
            'karyawan' => $karyawan,
            'divisis' => $this->karyawanModel->getDivisi()
        ];
        
        return view('hrga/karyawan_edit', $data);
    }

    public function update($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        
        if (!$karyawan) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Data tidak ditemukan');
        }

        if (!$this->validate([
            'nip' => "required|is_unique[karyawan.nip,id,{$id}]",
            'nama_lengkap' => 'required',
            'divisi_id' => 'required',
            'jabatan' => 'required',
            'tanggal_masuk' => 'required',
            'status_karyawan' => 'required',
            'gaji_pokok' => 'required|numeric'
        ])) {
            return redirect()->to('/hrga/karyawan/edit/' . $id)
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->karyawanModel->update($id, [
            'nip' => $this->request->getPost('nip'),
            'nama' => $this->request->getPost('nama_lengkap'),
            'divisi' => $this->getDivisiName($this->request->getPost('divisi_id')),
            'jabatan' => $this->request->getPost('jabatan'),
            'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
            'status' => $this->request->getPost('status_karyawan'),
            'gaji_pokok' => $this->request->getPost('gaji_pokok'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/hrga/karyawan')->with('success', 'Data karyawan berhasil diupdate');
    }

    public function detail($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        
        if (!$karyawan) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Data tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Karyawan',
            'karyawan' => $karyawan
        ];
        
        return view('hrga/karyawan_detail', $data);
    }

    public function hapus($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        
        if (!$karyawan) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Data tidak ditemukan');
        }

        $this->karyawanModel->delete($id);
        return redirect()->to('/hrga/karyawan')->with('success', 'Data karyawan berhasil dihapus');
    }

    private function getDivisiName($divisi_id)
    {
        $divisis = $this->karyawanModel->getDivisi();
        foreach ($divisis as $divisi) {
            if ($divisi['id'] == $divisi_id) {
                return $divisi['nama_divisi'];
            }
        }
        return 'Unknown';
    }
}