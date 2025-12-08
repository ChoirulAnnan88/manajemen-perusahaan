<?php
namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;
use App\Models\PRODUKSI\OperatorModel;
use App\Models\PRODUKSI\AlatdanBahanModel;

class OperatorController extends BaseController
{
    protected $operatorModel;
    protected $alatModel;

    public function __construct()
    {
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
            'title' => 'Data Operator',
            'operators' => $this->operatorModel->getOperators(),
            'module' => 'produksi'
        ];
        return view('produksi/operator/index', $data);
    }

    public function view($id)
    {
        $operator = $this->operatorModel->getOperator($id);
        if (!$operator) {
            return redirect()->to('/produksi/operator')->with('error', 'Data operator tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Operator',
            'operator' => $operator,
            'module' => 'produksi'
        ];
        return view('produksi/operator/view', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Operator',
            'alats' => $this->alatModel->getAlat(),
            'module' => 'produksi'
        ];
        return view('produksi/operator/create', $data);
    }

    public function store()
    {
        $rules = [
            'user_id' => 'required|is_unique[produksi_operator.user_id]',
            'username' => 'required|min_length[3]',
            'nama_lengkap' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'nip' => 'required|is_unique[produksi_operator.nip]',
            'status_kerja' => 'required|in_list[aktif,cuti,sakit,libur]',
            'alat_id' => 'permit_empty|integer',
            'keterangan' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'user_id' => $this->request->getPost('user_id'),
            'username' => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'email' => $this->request->getPost('email'),
            'nip' => $this->request->getPost('nip'),
            'status_kerja' => $this->request->getPost('status_kerja'),
            'alat_id' => $this->request->getPost('alat_id') ?: null,
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->operatorModel->insert($data)) {
            return redirect()->to('/produksi/operator')->with('success', 'Operator berhasil ditambahkan');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan operator');
    }

    public function edit($id)
    {
        $operator = $this->operatorModel->find($id);
        if (!$operator) {
            return redirect()->to('/produksi/operator')->with('error', 'Data operator tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Operator',
            'operator' => $operator,
            'alats' => $this->alatModel->getAlat(),
            'module' => 'produksi'
        ];
        return view('produksi/operator/edit', $data);
    }

    public function update($id)
    {
        $operator = $this->operatorModel->find($id);
        if (!$operator) {
            return redirect()->to('/produksi/operator')->with('error', 'Data operator tidak ditemukan');
        }

        $rules = [
            'user_id' => "required|is_unique[produksi_operator.user_id,id,{$id}]",
            'username' => 'required|min_length[3]',
            'nama_lengkap' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'nip' => "required|is_unique[produksi_operator.nip,id,{$id}]",
            'status_kerja' => 'required|in_list[aktif,cuti,sakit,libur]',
            'alat_id' => 'permit_empty|integer',
            'keterangan' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'user_id' => $this->request->getPost('user_id'),
            'username' => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'email' => $this->request->getPost('email'),
            'nip' => $this->request->getPost('nip'),
            'status_kerja' => $this->request->getPost('status_kerja'),
            'alat_id' => $this->request->getPost('alat_id') ?: null,
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->operatorModel->update($id, $data)) {
            return redirect()->to('/produksi/operator')->with('success', 'Operator berhasil diupdate');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal mengupdate operator');
    }

    public function delete($id)
    {
        $operator = $this->operatorModel->find($id);
        if (!$operator) {
            return redirect()->to('/produksi/operator')->with('error', 'Data operator tidak ditemukan');
        }

        if ($this->operatorModel->delete($id)) {
            return redirect()->to('/produksi/operator')->with('success', 'Operator berhasil dihapus');
        }

        return redirect()->to('/produksi/operator')->with('error', 'Gagal menghapus operator');
    }

    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard Operator',
            'total_operator' => $this->operatorModel->countAll(),
            'operator_aktif' => $this->operatorModel->where('status_kerja', 'aktif')->countAllResults(),
            'operator_cuti' => $this->operatorModel->where('status_kerja', 'cuti')->countAllResults(),
            'operator_sakit' => $this->operatorModel->where('status_kerja', 'sakit')->countAllResults(),
            'operator_list' => $this->operatorModel->getOperators(10),
            'module' => 'produksi'
        ];
        return view('produksi/operator/dashboard', $data);
    }
}