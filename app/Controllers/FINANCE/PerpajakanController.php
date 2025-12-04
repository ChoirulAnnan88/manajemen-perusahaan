<?php
namespace App\Controllers\FINANCE;

use App\Controllers\BaseController;
use App\Models\FINANCE\PerpajakanModel;

class PerpajakanController extends BaseController
{
    protected $perpajakanModel;

    public function __construct()
    {
        $this->perpajakanModel = new PerpajakanModel();
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Finance');
        }

        // Hitung statistik dari model
        $totalLunas = $this->perpajakanModel->getTotalPajak('lunas');
        $totalBelum = $this->perpajakanModel->getTotalPajak('belum_bayar');
        $totalSemua = $this->perpajakanModel->getTotalPajak();

        $data = [
            'title' => 'Perpajakan',
            'module' => 'finance',
            'pajak' => $this->perpajakanModel->getAllPajak(),
            'status' => ['belum_bayar', 'lunas'],
            'total_lunas' => $totalLunas,
            'total_belum' => $totalBelum,
            'total_semua' => $totalSemua
        ];
        
        return view('finance/perpajakan/dashboard', $data);
    }

    public function create()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Tambah Data Pajak',
            'module' => 'finance',
            'validation' => \Config\Services::validation()
        ];
        
        return view('finance/perpajakan/create', $data);
    }

    public function store()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'jenis_pajak' => 'required',
            'periode' => 'required|valid_date',
            'jumlah_pajak' => 'required|numeric|greater_than[0]',
            'tanggal_jatuh_tempo' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'jenis_pajak' => $this->request->getPost('jenis_pajak'),
            'periode' => $this->request->getPost('periode'),
            'jumlah_pajak' => $this->request->getPost('jumlah_pajak'),
            'tanggal_jatuh_tempo' => $this->request->getPost('tanggal_jatuh_tempo'),
            'status' => 'belum_bayar'
        ];

        if ($this->perpajakanModel->insert($data)) {
            return redirect()->to('/finance/pajak')->with('success', 'Data pajak berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data pajak');
    }

    public function edit($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pajak = $this->perpajakanModel->find($id);
        if (!$pajak) {
            return redirect()->to('/finance/pajak')->with('error', 'Data pajak tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Data Pajak',
            'module' => 'finance',
            'pajak' => $pajak,
            'validation' => \Config\Services::validation()
        ];
        
        return view('finance/perpajakan/edit', $data);
    }

    public function update($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pajak = $this->perpajakanModel->find($id);
        if (!$pajak) {
            return redirect()->to('/finance/pajak')->with('error', 'Data pajak tidak ditemukan');
        }

        $rules = [
            'jenis_pajak' => 'required',
            'periode' => 'required|valid_date',
            'jumlah_pajak' => 'required|numeric|greater_than[0]',
            'tanggal_jatuh_tempo' => 'required|valid_date',
            'status' => 'required|in_list[belum_bayar,lunas]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'jenis_pajak' => $this->request->getPost('jenis_pajak'),
            'periode' => $this->request->getPost('periode'),
            'jumlah_pajak' => $this->request->getPost('jumlah_pajak'),
            'tanggal_jatuh_tempo' => $this->request->getPost('tanggal_jatuh_tempo'),
            'status' => $this->request->getPost('status')
        ];

        if ($this->perpajakanModel->update($id, $data)) {
            return redirect()->to('/finance/pajak')->with('success', 'Data pajak berhasil diperbarui');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data pajak');
    }

    public function delete($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        if ($this->perpajakanModel->delete($id)) {
            return redirect()->to('/finance/pajak')->with('success', 'Data pajak berhasil dihapus');
        }

        return redirect()->to('/finance/pajak')->with('error', 'Gagal menghapus data pajak');
    }

    public function view($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pajak = $this->perpajakanModel->find($id);
        if (!$pajak) {
            return redirect()->to('/finance/pajak')->with('error', 'Data pajak tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Pajak',
            'module' => 'finance',
            'pajak' => $pajak
        ];
        
        return view('finance/perpajakan/view', $data);
    }

    public function markPaid($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = ['status' => 'lunas'];
        
        if ($this->perpajakanModel->update($id, $data)) {
            return redirect()->to('/finance/pajak')->with('success', 'Status pajak berhasil diperbarui menjadi lunas');
        }

        return redirect()->to('/finance/pajak')->with('error', 'Gagal memperbarui status pajak');
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