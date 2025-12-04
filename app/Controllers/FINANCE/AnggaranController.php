<?php
namespace App\Controllers\FINANCE;

use App\Controllers\BaseController;
use App\Models\FINANCE\AnggaranModel;

class AnggaranController extends BaseController
{
    protected $anggaranModel;

    public function __construct()
    {
        $this->anggaranModel = new AnggaranModel();
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Finance');
        }

        $totalAnggaran = $this->anggaranModel->getTotalAnggaran();
        $totalRealisasi = $this->anggaranModel->getTotalRealisasi();

        $data = [
            'title' => 'Anggaran Perusahaan',
            'module' => 'finance',
            'anggaran' => $this->anggaranModel->getAllAnggaran(),
            'years' => $this->anggaranModel->getDistinctYears(),
            'total_anggaran' => $totalAnggaran,
            'total_realisasi' => $totalRealisasi
        ];
        
        return view('finance/anggaran/dashboard', $data);
    }

    public function create()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Tambah Anggaran Baru',
            'module' => 'finance',
            'validation' => \Config\Services::validation(),
            'years' => range(date('Y') - 1, date('Y') + 1)
        ];
        
        return view('finance/anggaran/create', $data);
    }

    public function store()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'tahun' => 'required|numeric|exact_length[4]',
            'divisi_id' => 'required|numeric',
            'kategori_anggaran' => 'required',
            'jumlah_anggaran' => 'required|numeric|greater_than[0]',
            'realisasi' => 'numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'tahun' => $this->request->getPost('tahun'),
            'divisi_id' => $this->request->getPost('divisi_id'),
            'kategori_anggaran' => $this->request->getPost('kategori_anggaran'),
            'jumlah_anggaran' => $this->request->getPost('jumlah_anggaran'),
            'realisasi' => $this->request->getPost('realisasi') ?? 0.00
        ];

        if ($this->anggaranModel->insert($data)) {
            return redirect()->to('/finance/anggaran')->with('success', 'Anggaran berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan anggaran');
    }

    public function edit($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $anggaran = $this->anggaranModel->find($id);
        if (!$anggaran) {
            return redirect()->to('/finance/anggaran')->with('error', 'Anggaran tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Anggaran',
            'module' => 'finance',
            'anggaran' => $anggaran,
            'validation' => \Config\Services::validation(),
            'years' => range(date('Y') - 1, date('Y') + 1)
        ];
        
        return view('finance/anggaran/edit', $data);
    }

    public function update($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $anggaran = $this->anggaranModel->find($id);
        if (!$anggaran) {
            return redirect()->to('/finance/anggaran')->with('error', 'Anggaran tidak ditemukan');
        }

        $rules = [
            'tahun' => 'required|numeric|exact_length[4]',
            'divisi_id' => 'required|numeric',
            'kategori_anggaran' => 'required',
            'jumlah_anggaran' => 'required|numeric|greater_than[0]',
            'realisasi' => 'numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'tahun' => $this->request->getPost('tahun'),
            'divisi_id' => $this->request->getPost('divisi_id'),
            'kategori_anggaran' => $this->request->getPost('kategori_anggaran'),
            'jumlah_anggaran' => $this->request->getPost('jumlah_anggaran'),
            'realisasi' => $this->request->getPost('realisasi') ?? $anggaran['realisasi']
        ];

        if ($this->anggaranModel->update($id, $data)) {
            return redirect()->to('/finance/anggaran')->with('success', 'Anggaran berhasil diperbarui');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui anggaran');
    }

    public function delete($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        if ($this->anggaranModel->delete($id)) {
            return redirect()->to('/finance/anggaran')->with('success', 'Anggaran berhasil dihapus');
        }

        return redirect()->to('/finance/anggaran')->with('error', 'Gagal menghapus anggaran');
    }

    public function view($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $anggaran = $this->anggaranModel->find($id);
        if (!$anggaran) {
            return redirect()->to('/finance/anggaran')->with('error', 'Anggaran tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Anggaran',
            'module' => 'finance',
            'anggaran' => $anggaran
        ];
        
        return view('finance/anggaran/view', $data);
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