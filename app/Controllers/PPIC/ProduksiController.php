<?php
namespace App\Controllers\PPIC;

use App\Controllers\BaseController;
use App\Models\PPIC\ProduksiModel;
use App\Models\PPIC\InventoriModel;

class ProduksiController extends BaseController
{
    protected $produksiModel;
    protected $inventoriModel;

    public function __construct()
    {
        $this->produksiModel = new ProduksiModel();
        $this->inventoriModel = new InventoriModel();
        
        helper(['form', 'url']);
    }

    // ==================== INDEX/DASHBOARD ====================
    public function index()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi PPIC');
        }

        try {
            // Gunakan findAll() karena getAllProduksi() tidak ada
            $produksi = $this->produksiModel->findAll();
            
            // Hitung statistik manual
            $total = count($produksi);
            $completed = 0;
            $in_progress = 0;
            $total_target = 0;
            $total_hasil = 0;
            
            foreach ($produksi as $item) {
                if ($item['status_produksi'] == 'selesai') $completed++;
                if ($item['status_produksi'] == 'proses') $in_progress++;
                $total_target += $item['jumlah_target'] ?? 0;
                $total_hasil += $item['jumlah_hasil'] ?? 0;
            }
            
            $stats = [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $in_progress,
                'avg_progress' => $total_target > 0 ? round(($total_hasil / $total_target) * 100, 1) : 0
            ];
            
            $data = [
                'title' => 'Produksi PPIC',
                'produksi' => $produksi,
                'stats' => $stats,
                'module' => 'ppic'
            ];
            
            // Ganti dengan view yang ADA: 'ppic/produksi/index'
            return view('ppic/produksi/index', $data);

        } catch (\Exception $e) {
            // Untuk debugging
            die("ERROR: " . $e->getMessage() . 
                "<br>File: " . $e->getFile() . 
                "<br>Line: " . $e->getLine());
        }
    }

    // ==================== CREATE ====================
    public function create()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi PPIC');
        }

        try {
            // Ambil data produk dari inventori untuk dropdown
            $produkList = $this->inventoriModel->findAll();
            
            $data = [
                'title' => 'Tambah Rencana Produksi',
                'produk_list' => $produkList,
                'validation' => \Config\Services::validation(),
                'module' => 'ppic'
            ];

            return view('ppic/produksi/create', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in PPIC ProduksiController::create: ' . $e->getMessage());
            
            return redirect()->to('/ppic/produksi')
                           ->with('error', 'Gagal memuat form tambah produksi.');
        }
    }

    // ==================== STORE ====================
    public function store()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi PPIC');
        }

        // Validasi input
        $rules = [
            'nomor_plan' => 'required|max_length[50]|is_unique[ppic_produksi.nomor_plan]',
            'produk' => 'required|max_length[100]',
            'produk_id' => 'permit_empty|integer',
            'jumlah_target' => 'required|integer|greater_than[0]',
            'jumlah_hasil' => 'required|integer|greater_than_equal_to[0]',
            'kualitas' => 'required|in_list[baik,sedang,rusak]',
            'biaya_produksi' => 'permit_empty|decimal',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'tanggal_produksi' => 'required|valid_date',
            'status' => 'required|in_list[planned,progress,completed,canceled,hold]',
            'status_produksi' => 'required|in_list[menunggu,proses,selesai,batal]',
            'keterangan' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/produksi/create')
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        try {
            // Prepare data for insert
            $data = [
                'nomor_plan' => $this->request->getPost('nomor_plan'),
                'produk_id' => $this->request->getPost('produk_id'),
                'produk' => $this->request->getPost('produk'),
                'jumlah_target' => $this->request->getPost('jumlah_target'),
                'jumlah_hasil' => $this->request->getPost('jumlah_hasil'),
                'kualitas' => $this->request->getPost('kualitas'),
                'biaya_produksi' => $this->request->getPost('biaya_produksi') ?? 0.00,
                'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
                'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
                'tanggal_produksi' => $this->request->getPost('tanggal_produksi'),
                'status' => $this->request->getPost('status'),
                'status_produksi' => $this->request->getPost('status_produksi'),
                'keterangan' => $this->request->getPost('keterangan')
            ];

            // Format material terpakai jika ada
            $materialNama = $this->request->getPost('material_nama');
            if ($materialNama && is_array($materialNama)) {
                $materials = [];
                $materialJumlah = $this->request->getPost('material_jumlah');
                $materialSatuan = $this->request->getPost('material_satuan');
                
                for ($i = 0; $i < count($materialNama); $i++) {
                    if (!empty($materialNama[$i]) && !empty($materialJumlah[$i])) {
                        $materials[] = [
                            'nama' => $materialNama[$i],
                            'jumlah' => (int)$materialJumlah[$i],
                            'satuan' => $materialSatuan[$i] ?? 'pcs'
                        ];
                    }
                }
                
                if (!empty($materials)) {
                    $data['material_terpakai'] = json_encode($materials);
                }
            }

            // Simpan data - nomor_produksi akan auto-generate oleh Model
            $insertId = $this->produksiModel->insert($data);
            
            return redirect()->to('/ppic/produksi/view/' . $insertId)
                           ->with('success', 'Rencana produksi berhasil ditambahkan.');
            
        } catch (\Exception $e) {
            log_message('error', 'Error in PPIC ProduksiController::store: ' . $e->getMessage());
            
            return redirect()->to('/ppic/produksi/create')
                           ->withInput()
                           ->with('error', 'Gagal menambahkan rencana produksi: ' . $e->getMessage());
        }
    }

    // ==================== VIEW ====================
    public function view($id)
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi PPIC');
        }

        try {
            // Cari data produksi
            $produksi = $this->produksiModel->find($id);
            
            if (!$produksi) {
                return redirect()->to('/ppic/produksi')
                               ->with('error', 'Data produksi tidak ditemukan.');
            }

            // Decode material terpakai jika ada
            if (!empty($produksi['material_terpakai'])) {
                $produksi['material_terpakai'] = json_decode($produksi['material_terpakai'], true);
            } else {
                $produksi['material_terpakai'] = [];
            }

            // Format tanggal untuk display
            $produksi['tanggal_mulai_formatted'] = date('d/m/Y', strtotime($produksi['tanggal_mulai']));
            $produksi['tanggal_selesai_formatted'] = date('d/m/Y', strtotime($produksi['tanggal_selesai']));
            $produksi['tanggal_produksi_formatted'] = $produksi['tanggal_produksi'] 
                ? date('d/m/Y', strtotime($produksi['tanggal_produksi']))
                : '-';

            // Hitung progress
            $produksi['progress_percentage'] = $produksi['jumlah_target'] > 0 
                ? min(100, round(($produksi['jumlah_hasil'] / $produksi['jumlah_target']) * 100))
                : 0;

            $data = [
                'title' => 'Detail Rencana Produksi',
                'produksi' => $produksi,
                'module' => 'ppic'
            ];
            
            return view('ppic/produksi/view', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in PPIC ProduksiController::view: ' . $e->getMessage());
            
            return redirect()->to('/ppic/produksi')
                           ->with('error', 'Gagal memuat detail produksi.');
        }
    }

    // ==================== EDIT ====================
    public function edit($id)
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi PPIC');
        }

        try {
            // Cari data produksi
            $produksi = $this->produksiModel->find($id);
            
            if (!$produksi) {
                return redirect()->to('/ppic/produksi')
                               ->with('error', 'Data produksi tidak ditemukan.');
            }

            // Decode material terpakai jika ada
            if (!empty($produksi['material_terpakai'])) {
                $produksi['material_terpakai'] = json_decode($produksi['material_terpakai'], true);
            } else {
                $produksi['material_terpakai'] = [];
            }

            // Ambil data produk dari inventori untuk dropdown
            $produkList = $this->inventoriModel->findAll();
            
            // Format tanggal untuk form input
            $produksi['tanggal_mulai'] = date('Y-m-d', strtotime($produksi['tanggal_mulai']));
            $produksi['tanggal_selesai'] = date('Y-m-d', strtotime($produksi['tanggal_selesai']));
            $produksi['tanggal_produksi'] = $produksi['tanggal_produksi'] 
                ? date('Y-m-d', strtotime($produksi['tanggal_produksi']))
                : date('Y-m-d');

            $data = [
                'title' => 'Edit Rencana Produksi',
                'produksi' => $produksi,
                'produk_list' => $produkList,
                'validation' => \Config\Services::validation(),
                'module' => 'ppic'
            ];

            return view('ppic/produksi/edit', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error in PPIC ProduksiController::edit: ' . $e->getMessage());
            
            return redirect()->to('/ppic/produksi')
                           ->with('error', 'Gagal memuat form edit.');
        }
    }

    // ==================== UPDATE ====================
    public function update($id)
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi PPIC');
        }

        // Cek apakah data exists
        $existing = $this->produksiModel->find($id);
        if (!$existing) {
            return redirect()->to('/ppic/produksi')
                           ->with('error', 'Data produksi tidak ditemukan.');
        }

        // Validasi input
        $rules = [
            'nomor_plan' => 'required|max_length[50]|is_unique[ppic_produksi.nomor_plan,id,' . $id . ']',
            'produk' => 'required|max_length[100]',
            'jumlah_target' => 'required|integer|greater_than[0]',
            'jumlah_hasil' => 'required|integer|greater_than_equal_to[0]',
            'kualitas' => 'required|in_list[baik,sedang,rusak]',
            'biaya_produksi' => 'permit_empty|decimal',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'tanggal_produksi' => 'required|valid_date',
            'status' => 'required|in_list[planned,progress,completed,canceled,hold]',
            'status_produksi' => 'required|in_list[menunggu,proses,selesai,batal]',
            'keterangan' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/ppic/produksi/edit/' . $id)
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        try {
            // Prepare data for update
            $data = [
                'nomor_plan' => $this->request->getPost('nomor_plan'),
                'produk_id' => $this->request->getPost('produk_id'),
                'produk' => $this->request->getPost('produk'),
                'jumlah_target' => $this->request->getPost('jumlah_target'),
                'jumlah_hasil' => $this->request->getPost('jumlah_hasil'),
                'kualitas' => $this->request->getPost('kualitas'),
                'biaya_produksi' => $this->request->getPost('biaya_produksi') ?? 0.00,
                'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
                'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
                'tanggal_produksi' => $this->request->getPost('tanggal_produksi'),
                'status' => $this->request->getPost('status'),
                'status_produksi' => $this->request->getPost('status_produksi'),
                'keterangan' => $this->request->getPost('keterangan')
            ];

            // Hitung persentase selesai
            if ($data['jumlah_target'] > 0) {
                $data['persentase_selesai'] = min(100, 
                    round(($data['jumlah_hasil'] / $data['jumlah_target']) * 100)
                );
            } else {
                $data['persentase_selesai'] = 0;
            }

            // Format material terpakai jika ada
            $materialNama = $this->request->getPost('material_nama');
            if ($materialNama && is_array($materialNama)) {
                $materials = [];
                $materialJumlah = $this->request->getPost('material_jumlah');
                $materialSatuan = $this->request->getPost('material_satuan');
                
                for ($i = 0; $i < count($materialNama); $i++) {
                    if (!empty($materialNama[$i]) && !empty($materialJumlah[$i])) {
                        $materials[] = [
                            'nama' => $materialNama[$i],
                            'jumlah' => (int)$materialJumlah[$i],
                            'satuan' => $materialSatuan[$i] ?? 'pcs'
                        ];
                    }
                }
                
                if (!empty($materials)) {
                    $data['material_terpakai'] = json_encode($materials);
                } else {
                    $data['material_terpakai'] = null;
                }
            } else {
                $data['material_terpakai'] = null;
            }

            // Update data
            $this->produksiModel->update($id, $data);
            
            return redirect()->to('/ppic/produksi/view/' . $id)
                           ->with('success', 'Rencana produksi berhasil diperbarui.');
            
        } catch (\Exception $e) {
            log_message('error', 'Error in PPIC ProduksiController::update: ' . $e->getMessage());
            
            return redirect()->to('/ppic/produksi/edit/' . $id)
                           ->withInput()
                           ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    // ==================== DELETE ====================
    public function delete($id)
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi PPIC');
        }

        try {
            // Cari data produksi
            $produksi = $this->produksiModel->find($id);
            
            if (!$produksi) {
                return redirect()->to('/ppic/produksi')
                               ->with('error', 'Data produksi tidak ditemukan.');
            }

            // Soft delete data
            $this->produksiModel->delete($id);
            
            return redirect()->to('/ppic/produksi')
                           ->with('success', 'Rencana produksi berhasil dihapus.');
            
        } catch (\Exception $e) {
            log_message('error', 'Error in PPIC ProduksiController::delete: ' . $e->getMessage());
            
            return redirect()->to('/ppic/produksi')
                           ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // ==================== CHECK DIVISION ACCESS ====================
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