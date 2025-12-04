<?php
namespace App\Controllers\FINANCE;

use App\Controllers\BaseController;
use App\Models\FINANCE\AsetModel;

class AsetController extends BaseController
{
    protected $asetModel;

    public function __construct()
    {
        $this->asetModel = new AsetModel();
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Finance');
        }

        // Hitung statistik dari model
        $totalNilai = $this->asetModel->getTotalNilaiAset();
        $jumlahAset = count($this->asetModel->getAllAset());

        $data = [
            'title' => 'Aset Perusahaan',
            'module' => 'finance',
            'aset' => $this->asetModel->getAllAset(),
            'total_nilai' => $totalNilai,
            'jumlah_aset' => $jumlahAset
        ];
        
        return view('finance/aset/dashboard', $data);
    }

    public function create()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Tambah Aset Baru',
            'module' => 'finance',
            'validation' => \Config\Services::validation(),
            'kategori' => ['kantor', 'produksi', 'kendaraan', 'bangunan', 'tanah'],
            'status' => ['aktif', 'maintenance', 'rusak', 'jual']
        ];
        
        return view('finance/aset/create', $data);
    }

    public function store()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'kode_aset' => 'required|is_unique[finance_aset.kode_aset]',
            'nama_aset' => 'required',
            'kategori' => 'required|in_list[kantor,produksi,kendaraan,bangunan,tanah]',
            'nilai_aset' => 'required|numeric|greater_than[0]',
            'tanggal_perolehan' => 'required|valid_date',
            'status' => 'required|in_list[aktif,maintenance,rusak,jual]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_aset' => $this->request->getPost('kode_aset'),
            'nama_aset' => $this->request->getPost('nama_aset'),
            'kategori' => $this->request->getPost('kategori'),
            'nilai_aset' => $this->request->getPost('nilai_aset'),
            'tanggal_perolehan' => $this->request->getPost('tanggal_perolehan'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->asetModel->insert($data)) {
            return redirect()->to('/finance/aset')->with('success', 'Aset berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan aset');
    }

    public function edit($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $aset = $this->asetModel->find($id);
        if (!$aset) {
            return redirect()->to('/finance/aset')->with('error', 'Aset tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Aset',
            'module' => 'finance',
            'aset' => $aset,
            'validation' => \Config\Services::validation(),
            'kategori' => ['kantor', 'produksi', 'kendaraan', 'bangunan', 'tanah'],
            'status' => ['aktif', 'maintenance', 'rusak', 'jual']
        ];
        
        return view('finance/aset/edit', $data);
    }

    public function update($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $aset = $this->asetModel->find($id);
        if (!$aset) {
            return redirect()->to('/finance/aset')->with('error', 'Aset tidak ditemukan');
        }

        $rules = [
            'nama_aset' => 'required',
            'kategori' => 'required|in_list[kantor,produksi,kendaraan,bangunan,tanah]',
            'nilai_aset' => 'required|numeric|greater_than[0]',
            'tanggal_perolehan' => 'required|valid_date',
            'status' => 'required|in_list[aktif,maintenance,rusak,jual]'
        ];

        // Validasi kode aset hanya jika diubah
        if ($this->request->getPost('kode_aset') !== $aset['kode_aset']) {
            $rules['kode_aset'] = 'required|is_unique[finance_aset.kode_aset]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'kode_aset' => $this->request->getPost('kode_aset'),
            'nama_aset' => $this->request->getPost('nama_aset'),
            'kategori' => $this->request->getPost('kategori'),
            'nilai_aset' => $this->request->getPost('nilai_aset'),
            'tanggal_perolehan' => $this->request->getPost('tanggal_perolehan'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->asetModel->update($id, $data)) {
            return redirect()->to('/finance/aset')->with('success', 'Aset berhasil diperbarui');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui aset');
    }

    public function delete($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        if ($this->asetModel->delete($id)) {
            return redirect()->to('/finance/aset')->with('success', 'Aset berhasil dihapus');
        }

        return redirect()->to('/finance/aset')->with('error', 'Gagal menghapus aset');
    }

    public function view($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $aset = $this->asetModel->find($id);
        if (!$aset) {
            return redirect()->to('/finance/aset')->with('error', 'Aset tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Aset',
            'module' => 'finance',
            'aset' => $aset
        ];
        
        return view('finance/aset/view', $data);
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