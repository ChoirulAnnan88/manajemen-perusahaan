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
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Operator - Produksi',
            'module' => 'produksi',
            'operator_list' => $this->operatorModel->getAllOperator()
        ];
        
        return view('produksi/operator/index', $data);
    }

    public function create()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Tambah Operator',
            'module' => 'produksi',
            'validation' => \Config\Services::validation(),
            'alat_list' => $this->alatModel->getAllAlat()
        ];
        
        return view('produksi/operator/create', $data);
    }

    public function save()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'username' => 'required',
            'nama_lengkap' => 'required',
            'email' => 'required|valid_email',
            'nip' => 'required|is_unique[produksi_operator.nip]',
            'status_kerja' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'email' => $this->request->getPost('email'),
            'nip' => $this->request->getPost('nip'),
            'status_kerja' => $this->request->getPost('status_kerja'),
            'alat_id' => $this->request->getPost('alat_id'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->operatorModel->save($data)) {
            return redirect()->to('/produksi/operator')->with('success', 'Operator berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan operator');
        }
    }

    public function edit($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Edit Operator',
            'module' => 'produksi',
            'validation' => \Config\Services::validation(),
            'operator' => $this->operatorModel->find($id),
            'alat_list' => $this->alatModel->getAllAlat()
        ];

        if (!$data['operator']) {
            return redirect()->to('/produksi/operator')->with('error', 'Operator tidak ditemukan');
        }

        return view('produksi/operator/edit', $data);
    }

    public function update($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $operator = $this->operatorModel->find($id);
        if (!$operator) {
            return redirect()->to('/produksi/operator')->with('error', 'Operator tidak ditemukan');
        }

        $rules = [
            'username' => 'required',
            'nama_lengkap' => 'required',
            'email' => 'required|valid_email',
            'nip' => "required|is_unique[produksi_operator.nip,id,$id]",
            'status_kerja' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => $id,
            'username' => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'email' => $this->request->getPost('email'),
            'nip' => $this->request->getPost('nip'),
            'status_kerja' => $this->request->getPost('status_kerja'),
            'alat_id' => $this->request->getPost('alat_id'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->operatorModel->save($data)) {
            return redirect()->to('/produksi/operator')->with('success', 'Operator berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui operator');
        }
    }

    public function delete($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        if ($this->operatorModel->delete($id)) {
            return redirect()->to('/produksi/operator')->with('success', 'Operator berhasil dihapus');
        } else {
            return redirect()->to('/produksi/operator')->with('error', 'Gagal menghapus operator');
        }
    }

    public function view($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Detail Operator',
            'module' => 'produksi',
            'operator' => $this->operatorModel->getOperatorWithAlat($id)
        ];

        if (!$data['operator']) {
            return redirect()->to('/produksi/operator')->with('error', 'Operator tidak ditemukan');
        }

        return view('produksi/operator/view', $data);
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