<?php
namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;
use App\Models\PRODUKSI\ProduksiModel;
use App\Models\PRODUKSI\AlatdanBahanModel;
use App\Models\PRODUKSI\OperatorModel;

class ProduksiController extends BaseController
{
    protected $produksiModel;
    protected $alatModel;
    protected $operatorModel;

    public function __construct()
    {
        $this->produksiModel = new ProduksiModel();
        $this->alatModel = new AlatdanBahanModel();
        $this->operatorModel = new OperatorModel();
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Produksi');
        }

        $data = [
            'title' => 'Dashboard Produksi',
            'module' => 'produksi',
            'total_produksi' => $this->produksiModel->countAll(),
            'total_alat' => $this->alatModel->countAll(),
            'total_operator' => $this->operatorModel->countAll(),
            'produksi_hari_ini' => $this->produksiModel->getProduksiHariIni()
        ];
        
        return view('produksi/dashboard', $data);
    }

    // HASIL PRODUKSI
    public function hasil()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Hasil Produksi - Produksi',
            'module' => 'produksi',
            'produksi' => $this->produksiModel->getAllProduksi()
        ];
        
        return view('produksi/produksi/index', $data);
    }

    public function createHasil()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Tambah Hasil Produksi',
            'module' => 'produksi',
            'validation' => \Config\Services::validation(),
            'alat_list' => $this->alatModel->findAll(),
            'operator_list' => $this->operatorModel->findAll()
        ];
        
        return view('produksi/produksi/create', $data);
    }

    public function saveHasil()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'nomor_produksi' => 'required|is_unique[produksi_produksi.nomor_produksi]',
            'tanggal_produksi' => 'required',
            'jumlah_hasil' => 'required|numeric',
            'kualitas' => 'required',
            'status_produksi' => 'required'
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
            'operator_id' => $this->request->getPost('operator_id'),
            'alat_id' => $this->request->getPost('alat_id'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        // GUNAKAN HELPER FUNCTION saveToProduksiTable()
        if ($this->saveToProduksiTable($data)) {
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil disimpan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data produksi');
        }
    }

    public function editHasil($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Edit Hasil Produksi',
            'module' => 'produksi',
            'validation' => \Config\Services::validation(),
            'produksi' => $this->produksiModel->find($id),
            'alat_list' => $this->alatModel->findAll(),
            'operator_list' => $this->operatorModel->findAll()
        ];

        if (!$data['produksi']) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data tidak ditemukan');
        }

        return view('produksi/produksi/edit', $data);
    }

    public function updateHasil($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $rules = [
            'nomor_produksi' => "required|is_unique[produksi_produksi.nomor_produksi,id,$id]",
            'tanggal_produksi' => 'required',
            'jumlah_hasil' => 'required|numeric',
            'kualitas' => 'required',
            'status_produksi' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id' => $id,
            'nomor_produksi' => $this->request->getPost('nomor_produksi'),
            'tanggal_produksi' => $this->request->getPost('tanggal_produksi'),
            'jumlah_hasil' => $this->request->getPost('jumlah_hasil'),
            'kualitas' => $this->request->getPost('kualitas'),
            'status_produksi' => $this->request->getPost('status_produksi'),
            'operator_id' => $this->request->getPost('operator_id'),
            'alat_id' => $this->request->getPost('alat_id'),
            'keterangan' => $this->request->getPost('keterangan')
        ];

        // GUNAKAN HELPER FUNCTION saveToProduksiTable()
        if ($this->saveToProduksiTable($data)) {
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data produksi');
        }
    }

    public function deleteHasil($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // GUNAKAN HELPER FUNCTION deleteFromProduksiTable()
        if ($this->deleteFromProduksiTable($id)) {
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil dihapus');
        } else {
            return redirect()->to('/produksi/hasil')->with('error', 'Gagal menghapus data produksi');
        }
    }

    public function viewHasil($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Detail Hasil Produksi',
            'module' => 'produksi',
            'produksi' => $this->produksiModel->find($id)
        ];

        if (!$data['produksi']) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data tidak ditemukan');
        }

        return view('produksi/produksi/view', $data);
    }

    // ========== HELPER FUNCTIONS ==========

    private function saveToProduksiTable($data)
    {
        $db = db_connect();
        
        // Map data untuk tabel produksi_produksi
        $tableData = [
            'nomor_produksi' => $data['nomor_produksi'],
            'tanggal_produksi' => $data['tanggal_produksi'],
            'jumlah_hasil' => $data['jumlah_hasil'],
            'kualitas' => $data['kualitas'],
            'status' => $data['status_produksi'], // Konversi: status_produksi -> status
            'operator_id' => $data['operator_id'] ?? null,
            'alat_id' => $data['alat_id'] ?? null,
            'keterangan' => $data['keterangan'] ?? null
        ];
        
        // Jika ada ID, berarti UPDATE
        if (isset($data['id'])) {
            return $db->table('produksi_produksi')
                     ->where('id', $data['id'])
                     ->update($tableData);
        } else {
            // INSERT
            return $db->table('produksi_produksi')
                     ->insert($tableData);
        }
    }

    private function deleteFromProduksiTable($id)
    {
        $db = db_connect();
        return $db->table('produksi_produksi')
                 ->where('id', $id)
                 ->delete();
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