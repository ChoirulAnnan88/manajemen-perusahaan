<?php
namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;
use App\Models\PRODUKSI\ProduksiModel;
use App\Models\PRODUKSI\OperatorModel;
use App\Models\PRODUKSI\AlatdanBahanModel;

class ProduksiController extends BaseController
{
    protected $produksiModel;
    protected $operatorModel;
    protected $alatModel;

    public function __construct()
    {
        $this->produksiModel = new ProduksiModel();
        $this->operatorModel = new OperatorModel();
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
            'title' => 'Data Produksi',
            'produksi' => $this->produksiModel->getProduksi(),
            'module' => 'produksi'
        ];
        return view('produksi/produksi/index', $data);
    }

    public function view($id)
    {
        $produksi = $this->produksiModel->getProduksi($id);
        if (!$produksi) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data produksi tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Produksi',
            'produksi' => $produksi,
            'module' => 'produksi'
        ];
        return view('produksi/produksi/view', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Produksi',
            'operators' => $this->operatorModel->getOperators(),
            'alats' => $this->alatModel->getAlat(),
            'module' => 'produksi'
        ];
        return view('produksi/produksi/create', $data);
    }

    public function store()
    {
        $rules = [
            'nomor_produksi' => 'required|is_unique[produksi_hasil.nomor_produksi]',
            'tanggal_produksi' => 'required|valid_date',
            'jumlah_hasil' => 'required|integer|greater_than[0]',
            'kualitas' => 'required|in_list[baik,cacat_ringan,cacat_berat]',
            'status_produksi' => 'required|in_list[planned,progress,completed,canceled]',
            'operator_id' => 'permit_empty|integer',
            'alat_id' => 'permit_empty|integer',
            'keterangan' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nomor_produksi' => $this->request->getPost('nomor_produksi'),
            'tanggal_produksi' => $this->request->getPost('tanggal_produksi'),
            'jumlah_hasil' => $this->request->getPost('jumlah_hasil'),
            'kualitas' => $this->request->getPost('kualitas'),
            'status_produksi' => $this->request->getPost('status_produksi'),
            'operator_id' => $this->request->getPost('operator_id') ?: null,
            'alat_id' => $this->request->getPost('alat_id') ?: null,
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->produksiModel->insert($data)) {
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data produksi');
    }

    public function edit($id)
    {
        $produksi = $this->produksiModel->find($id);
        if (!$produksi) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data produksi tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Produksi',
            'produksi' => $produksi,
            'operators' => $this->operatorModel->getOperators(),
            'alats' => $this->alatModel->getAlat(),
            'module' => 'produksi'
        ];
        return view('produksi/produksi/edit', $data);
    }

    public function update($id)
    {
        $produksi = $this->produksiModel->find($id);
        if (!$produksi) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data produksi tidak ditemukan');
        }

        $rules = [
            'nomor_produksi' => "required|is_unique[produksi_hasil.nomor_produksi,id,{$id}]",
            'tanggal_produksi' => 'required|valid_date',
            'jumlah_hasil' => 'required|integer|greater_than[0]',
            'kualitas' => 'required|in_list[baik,cacat_ringan,cacat_berat]',
            'status_produksi' => 'required|in_list[planned,progress,completed,canceled]',
            'operator_id' => 'permit_empty|integer',
            'alat_id' => 'permit_empty|integer',
            'keterangan' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nomor_produksi' => $this->request->getPost('nomor_produksi'),
            'tanggal_produksi' => $this->request->getPost('tanggal_produksi'),
            'jumlah_hasil' => $this->request->getPost('jumlah_hasil'),
            'kualitas' => $this->request->getPost('kualitas'),
            'status_produksi' => $this->request->getPost('status_produksi'),
            'operator_id' => $this->request->getPost('operator_id') ?: null,
            'alat_id' => $this->request->getPost('alat_id') ?: null,
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->produksiModel->update($id, $data)) {
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil diupdate');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal mengupdate data produksi');
    }

    public function delete($id)
    {
        $produksi = $this->produksiModel->find($id);
        if (!$produksi) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data produksi tidak ditemukan');
        }

        if ($this->produksiModel->delete($id)) {
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil dihapus');
        }

        return redirect()->to('/produksi/hasil')->with('error', 'Gagal menghapus data produksi');
    }

    public function dashboard()
    {
        $today = date('Y-m-d');
        $data = [
            'title' => 'Dashboard Produksi',
            'total_produksi' => $this->produksiModel->countAll(),
            'total_hari_ini' => $this->produksiModel->where('tanggal_produksi', $today)->countAllResults(),
            'produksi_hari_ini' => $this->produksiModel->where('tanggal_produksi', $today)->orderBy('created_at', 'DESC')->findAll(5),
            'module' => 'produksi'
        ];
        return view('produksi/produksi/dashboard', $data);
    }
}