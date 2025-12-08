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
        
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Produksi');
        }
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

    public function index()
    {
        $data = [
            'title' => 'Alat dan Bahan',
            'alats' => $this->alatModel->getAlat(),
            'materials' => $this->alatModel->getMaterials(),
            'module' => 'produksi'
        ];
        return view('produksi/alat_dan_bahan/index', $data);
    }

    public function viewAlat($id)
    {
        $alat = $this->alatModel->getAlat($id);
        if (!$alat) {
            return redirect()->to('/produksi/alat')->with('error', 'Data alat tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Alat',
            'alat' => $alat,
            'module' => 'produksi'
        ];
        return view('produksi/alat_dan_bahan/view_alat', $data);
    }

    public function createAlat()
    {
        $data = [
            'title' => 'Tambah Alat',
            'module' => 'produksi'
        ];
        return view('produksi/alat_dan_bahan/create_alat', $data);
    }

    public function storeAlat()
    {
        $rules = [
            'kode_alat' => 'required|is_unique[produksi_alat.kode_alat]',
            'nama_alat' => 'required|min_length[3]',
            'tipe' => 'required|in_list[alat,mesin,perkakas]',
            'kategori' => 'permit_empty|string',
            'spesifikasi' => 'permit_empty|string',
            'status' => 'required|in_list[aktif,maintenance,rusak]',
            'kondisi' => 'required|in_list[baik,perlu_perawatan,rusak_ringan,rusak_berat]',
            'tanggal_maintenance' => 'permit_empty|valid_date',
            'lokasi' => 'permit_empty|string',
            'keterangan' => 'permit_empty|string'
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
            'lokasi' => $this->request->getPost('lokasi') ?: 'Gudang Produksi',
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->alatModel->insertAlat($data)) {
            return redirect()->to('/produksi/alat')->with('success', 'Alat berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan alat');
    }

    public function editAlat($id)
    {
        $alat = $this->alatModel->getAlat($id);
        if (!$alat) {
            return redirect()->to('/produksi/alat')->with('error', 'Data alat tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Alat',
            'alat' => $alat,
            'module' => 'produksi'
        ];
        return view('produksi/alat_dan_bahan/edit_alat', $data);
    }

    public function updateAlat($id)
    {
        $alat = $this->alatModel->getAlat($id);
        if (!$alat) {
            return redirect()->to('/produksi/alat')->with('error', 'Data alat tidak ditemukan');
        }

        $rules = [
            'kode_alat' => "required|is_unique[produksi_alat.kode_alat,id,{$id}]",
            'nama_alat' => 'required|min_length[3]',
            'tipe' => 'required|in_list[alat,mesin,perkakas]',
            'kategori' => 'permit_empty|string',
            'spesifikasi' => 'permit_empty|string',
            'status' => 'required|in_list[aktif,maintenance,rusak]',
            'kondisi' => 'required|in_list[baik,perlu_perawatan,rusak_ringan,rusak_berat]',
            'tanggal_maintenance' => 'permit_empty|valid_date',
            'lokasi' => 'permit_empty|string',
            'keterangan' => 'permit_empty|string'
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

        if ($this->alatModel->updateAlat($id, $data)) {
            return redirect()->to('/produksi/alat')->with('success', 'Alat berhasil diupdate');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal mengupdate alat');
    }

    public function deleteAlat($id)
    {
        $alat = $this->alatModel->getAlat($id);
        if (!$alat) {
            return redirect()->to('/produksi/alat')->with('error', 'Data alat tidak ditemukan');
        }

        if ($this->alatModel->deleteAlat($id)) {
            return redirect()->to('/produksi/alat')->with('success', 'Alat berhasil dihapus');
        }

        return redirect()->to('/produksi/alat')->with('error', 'Gagal menghapus alat');
    }

    public function createMaterial()
    {
        $data = [
            'title' => 'Tambah Material',
            'module' => 'produksi'
        ];
        return view('produksi/alat_dan_bahan/create_material', $data);
    }

    public function storeMaterial()
    {
        $rules = [
            'kode_material' => 'required|is_unique[produksi_material.kode_material]',
            'nama_material' => 'required|min_length[3]',
            'spesifikasi' => 'permit_empty|string',
            'stok_aktual' => 'required|integer|greater_than_equal_to[0]',
            'stok_minimal' => 'required|integer|greater_than_equal_to[0]',
            'satuan' => 'required|string',
            'harga_satuan' => 'permit_empty|decimal',
            'status_stok' => 'required|in_list[tersedia,habis,terbatas,dipesan]',
            'lokasi' => 'permit_empty|string',
            'keterangan' => 'permit_empty|string'
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
            'harga_satuan' => $this->request->getPost('harga_satuan') ?: 0,
            'status_stok' => $this->request->getPost('status_stok'),
            'lokasi' => $this->request->getPost('lokasi') ?: 'Gudang Material Produksi',
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->alatModel->insertMaterial($data)) {
            return redirect()->to('/produksi/alat')->with('success', 'Material berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan material');
    }

    public function viewMaterial($id)
    {
        $material = $this->alatModel->getMaterial($id);
        if (!$material) {
            return redirect()->to('/produksi/alat')->with('error', 'Data material tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Material',
            'material' => $material,
            'module' => 'produksi'
        ];
        return view('produksi/alat_dan_bahan/view_material', $data);
    }

    public function editMaterial($id)
    {
        $material = $this->alatModel->getMaterial($id);
        if (!$material) {
            return redirect()->to('/produksi/alat')->with('error', 'Data material tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Material',
            'material' => $material,
            'module' => 'produksi'
        ];
        return view('produksi/alat_dan_bahan/edit_material', $data);
    }

    public function updateMaterial($id)
    {
        $material = $this->alatModel->getMaterial($id);
        if (!$material) {
            return redirect()->to('/produksi/alat')->with('error', 'Data material tidak ditemukan');
        }

        $rules = [
            'kode_material' => "required|is_unique[produksi_material.kode_material,id,{$id}]",
            'nama_material' => 'required|min_length[3]',
            'spesifikasi' => 'permit_empty|string',
            'stok_aktual' => 'required|integer|greater_than_equal_to[0]',
            'stok_minimal' => 'required|integer|greater_than_equal_to[0]',
            'satuan' => 'required|string',
            'harga_satuan' => 'permit_empty|decimal',
            'status_stok' => 'required|in_list[tersedia,habis,terbatas,dipesan]',
            'lokasi' => 'permit_empty|string',
            'keterangan' => 'permit_empty|string'
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
            return redirect()->to('/produksi/alat')->with('success', 'Material berhasil diupdate');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal mengupdate material');
    }

    public function deleteMaterial($id)
    {
        $material = $this->alatModel->getMaterial($id);
        if (!$material) {
            return redirect()->to('/produksi/alat')->with('error', 'Data material tidak ditemukan');
        }

        if ($this->alatModel->deleteMaterial($id)) {
            return redirect()->to('/produksi/alat')->with('success', 'Material berhasil dihapus');
        }

        return redirect()->to('/produksi/alat')->with('error', 'Gagal menghapus material');
    }

    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard Alat dan Bahan',
            'total_alat' => $this->alatModel->countAlat(),
            'total_material' => $this->alatModel->countMaterial(),
            'alat_maintenance' => $this->alatModel->where('status', 'maintenance')->countAllResults(),
            'material_terbatas' => $this->alatModel->where('status_stok', 'terbatas')->countAllResults(),
            'alat_list' => $this->alatModel->getAlat(10),
            'material_list' => $this->alatModel->getMaterials(10),
            'module' => 'produksi'
        ];
        return view('produksi/alat_dan_bahan/dashboard', $data);
    }
}