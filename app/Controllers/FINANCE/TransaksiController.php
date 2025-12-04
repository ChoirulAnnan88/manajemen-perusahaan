<?php
namespace App\Controllers\FINANCE;

use App\Controllers\BaseController;
use App\Models\FINANCE\TransaksiModel;

class TransaksiController extends BaseController
{
    protected $transaksiModel;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Finance');
        }

        $data = [
            'title' => 'Transaksi Keuangan',
            'module' => 'finance',
            'transaksi' => $this->transaksiModel->getAllTransaksi()
        ];
        
        return view('finance/transaksi/dashboard', $data);
    }

    public function create()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Hitung statistik dari model
        $totalPemasukan = $this->transaksiModel->getTotalPemasukan();
        $totalPengeluaran = $this->transaksiModel->getTotalPengeluaran();

        $data = [
            'title' => 'Transaksi Keuangan',
            'module' => 'finance',
            'transaksi' => $this->transaksiModel->getAllTransaksi(),
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran
        ];
        
        return view('finance/transaksi/dashboard', $data);
    }

    public function store()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'nomor_transaksi' => 'required|is_unique[finance_transaksi.nomor_transaksi]',
            'tanggal_transaksi' => 'required|valid_date',
            'jenis' => 'required|in_list[pemasukan,pengeluaran]',
            'kategori' => 'required',
            'jumlah' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nomor_transaksi' => $this->request->getPost('nomor_transaksi'),
            'tanggal_transaksi' => $this->request->getPost('tanggal_transaksi'),
            'jenis' => $this->request->getPost('jenis'),
            'kategori' => $this->request->getPost('kategori'),
            'jumlah' => $this->request->getPost('jumlah'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->transaksiModel->insert($data)) {
            return redirect()->to('/finance/transaksi')->with('success', 'Transaksi berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan transaksi');
    }

    public function edit($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $transaksi = $this->transaksiModel->find($id);
        if (!$transaksi) {
            return redirect()->to('/finance/transaksi')->with('error', 'Transaksi tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Transaksi',
            'module' => 'finance',
            'transaksi' => $transaksi,
            'validation' => \Config\Services::validation()
        ];
        
        return view('finance/transaksi/edit', $data);
    }

    public function update($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $transaksi = $this->transaksiModel->find($id);
        if (!$transaksi) {
            return redirect()->to('/finance/transaksi')->with('error', 'Transaksi tidak ditemukan');
        }

        $rules = [
            'tanggal_transaksi' => 'required|valid_date',
            'jenis' => 'required|in_list[pemasukan,pengeluaran]',
            'kategori' => 'required',
            'jumlah' => 'required|numeric|greater_than[0]'
        ];

        // Validasi nomor transaksi hanya jika diubah
        if ($this->request->getPost('nomor_transaksi') !== $transaksi['nomor_transaksi']) {
            $rules['nomor_transaksi'] = 'required|is_unique[finance_transaksi.nomor_transaksi]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nomor_transaksi' => $this->request->getPost('nomor_transaksi'),
            'tanggal_transaksi' => $this->request->getPost('tanggal_transaksi'),
            'jenis' => $this->request->getPost('jenis'),
            'kategori' => $this->request->getPost('kategori'),
            'jumlah' => $this->request->getPost('jumlah'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->transaksiModel->update($id, $data)) {
            return redirect()->to('/finance/transaksi')->with('success', 'Transaksi berhasil diperbarui');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui transaksi');
    }

    public function delete($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        if ($this->transaksiModel->delete($id)) {
            return redirect()->to('/finance/transaksi')->with('success', 'Transaksi berhasil dihapus');
        }

        return redirect()->to('/finance/transaksi')->with('error', 'Gagal menghapus transaksi');
    }

    public function view($id)
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $transaksi = $this->transaksiModel->find($id);
        if (!$transaksi) {
            return redirect()->to('/finance/transaksi')->with('error', 'Transaksi tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Transaksi',
            'module' => 'finance',
            'transaksi' => $transaksi
        ];
        
        return view('finance/transaksi/view', $data);
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