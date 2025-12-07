<?php
namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;
use App\Models\PRODUKSI\AlatdanBahanModel;

class AlatdanBahanController extends BaseController
{
    protected $alatModel;

    public function __construct()
    {
        $this->alatModel = new AlatdanBahanModel();
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Alat dan Bahan - Produksi',
            'module' => 'produksi',
            'alat_list' => $this->alatModel->getAllAlat(),
            'material_list' => $this->alatModel->getAllMaterial()
        ];
        
        return view('produksi/alat_dan_bahan/index', $data);
    }

    // ALAT - CRUD
    public function createAlat()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Tambah Alat/Mesin',
            'module' => 'produksi',
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/alat_dan_bahan/create_alat', $data);
    }

    public function saveAlat()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'kode_alat' => 'required|is_unique[produksi_alat.kode_alat]',
            'nama_alat' => 'required',
            'tipe' => 'required',
            'status' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_alat' => $this->request->getPost('kode_alat'),
            'nama_alat' => $this->request->getPost('nama_alat'),
            'tipe' => $this->request->getPost('tipe'),
            'kategori' => $this->request->getPost('kategori'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'status' => $this->request->getPost('status'),
            'kondisi' => $this->request->getPost('kondisi'),
            'tanggal_maintenance' => $this->request->getPost('tanggal_maintenance'),
            'lokasi' => $this->request->getPost('lokasi'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->alatModel->saveAlat($data)) {
            return redirect()->to('/produksi/alat')->with('success', 'Alat berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan alat');
        }
    }

    public function editAlat($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Edit Alat/Mesin',
            'module' => 'produksi',
            'validation' => \Config\Services::validation(),
            'alat' => $this->alatModel->getAlatById($id)
        ];

        if (!$data['alat']) {
            return redirect()->to('/produksi/alat')->with('error', 'Alat tidak ditemukan');
        }

        return view('produksi/alat_dan_bahan/edit_alat', $data);
    }

    public function updateAlat($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $alat = $this->alatModel->getAlatById($id);
        if (!$alat) {
            return redirect()->to('/produksi/alat')->with('error', 'Alat tidak ditemukan');
        }

        $rules = [
            'kode_alat' => "required|is_unique[produksi_alat.kode_alat,id,$id]",
            'nama_alat' => 'required',
            'tipe' => 'required',
            'status' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => $id,
            'kode_alat' => $this->request->getPost('kode_alat'),
            'nama_alat' => $this->request->getPost('nama_alat'),
            'tipe' => $this->request->getPost('tipe'),
            'kategori' => $this->request->getPost('kategori'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'status' => $this->request->getPost('status'),
            'kondisi' => $this->request->getPost('kondisi'),
            'tanggal_maintenance' => $this->request->getPost('tanggal_maintenance'),
            'lokasi' => $this->request->getPost('lokasi'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->alatModel->updateAlat($id, $data)) {
            return redirect()->to('/produksi/alat')->with('success', 'Alat berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui alat');
        }
    }

    public function deleteAlat($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        if ($this->alatModel->deleteAlat($id)) {
            return redirect()->to('/produksi/alat')->with('success', 'Alat berhasil dihapus');
        } else {
            return redirect()->to('/produksi/alat')->with('error', 'Gagal menghapus alat');
        }
    }

    public function viewAlat($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Detail Alat/Mesin',
            'module' => 'produksi',
            'alat' => $this->alatModel->getAlatById($id)
        ];

        if (!$data['alat']) {
            return redirect()->to('/produksi/alat')->with('error', 'Alat tidak ditemukan');
        }

        return view('produksi/alat_dan_bahan/view_alat', $data);
    }

    // MATERIAL - CRUD
    public function createMaterial()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Tambah Material',
            'module' => 'produksi',
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/alat_dan_bahan/create_material', $data);
    }

    public function saveMaterial()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'kode_material' => 'required|is_unique[produksi_material.kode_material]',
            'nama_material' => 'required',
            'stok_aktual' => 'required|numeric',
            'stok_minimal' => 'required|numeric',
            'satuan' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_material' => $this->request->getPost('kode_material'),
            'nama_material' => $this->request->getPost('nama_material'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'stok_aktual' => $this->request->getPost('stok_aktual'),
            'stok_minimal' => $this->request->getPost('stok_minimal'),
            'satuan' => $this->request->getPost('satuan'),
            'harga_satuan' => $this->request->getPost('harga_satuan'),
            'status_stok' => $this->request->getPost('status_stok'),
            'lokasi' => $this->request->getPost('lokasi'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->alatModel->saveMaterial($data)) {
            return redirect()->to('/produksi/alat')->with('success', 'Material berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan material');
        }
    }

    public function editMaterial($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Edit Material',
            'module' => 'produksi',
            'validation' => \Config\Services::validation(),
            'material' => $this->alatModel->getMaterialById($id)
        ];

        if (!$data['material']) {
            return redirect()->to('/produksi/alat')->with('error', 'Material tidak ditemukan');
        }

        return view('produksi/alat_dan_bahan/edit_material', $data);
    }

    public function updateMaterial($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $material = $this->alatModel->getMaterialById($id);
        if (!$material) {
            return redirect()->to('/produksi/alat')->with('error', 'Material tidak ditemukan');
        }

        $rules = [
            'kode_material' => "required|is_unique[produksi_material.kode_material,id,$id]",
            'nama_material' => 'required',
            'stok_aktual' => 'required|numeric',
            'stok_minimal' => 'required|numeric',
            'satuan' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_material' => $this->request->getPost('kode_material'),
            'nama_material' => $this->request->getPost('nama_material'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'stok_aktual' => $this->request->getPost('stok_aktual'),
            'stok_minimal' => $this->request->getPost('stok_minimal'),
            'satuan' => $this->request->getPost('satuan'),
            'harga_satuan' => $this->request->getPost('harga_satuan'),
            'status_stok' => $this->request->getPost('status_stok'),
            'lokasi' => $this->request->getPost('lokasi'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->alatModel->updateMaterial($id, $data)) {
            return redirect()->to('/produksi/alat')->with('success', 'Material berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui material');
        }
    }

    public function deleteMaterial($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        if ($this->alatModel->deleteMaterial($id)) {
            return redirect()->to('/produksi/alat')->with('success', 'Material berhasil dihapus');
        } else {
            return redirect()->to('/produksi/alat')->with('error', 'Gagal menghapus material');
        }
    }

    public function viewMaterial($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Detail Material',
            'module' => 'produksi',
            'material' => $this->alatModel->getMaterialById($id)
        ];

        if (!$data['material']) {
            return redirect()->to('/produksi/alat')->with('error', 'Material tidak ditemukan');
        }

        return view('produksi/alat_dan_bahan/view_material', $data);
    }

    private function checkDivisionAccess($division)
    {
        $session = session();
        $userRole = $session->get('role');
        $userDivisi = $session->get('divisi_id');
        
        $divisionMap = [
            'hrga' => 1,
            'hse' => 2,
            'finance' => 3,
            'ppic' => 4, 
            'produksi' => 5,
            'marketing' => 6
        ];

        if ($userRole === 'manager') {
            return true;
        }

        return isset($divisionMap[$division]) && $userDivisi == $divisionMap[$division];
    }
}