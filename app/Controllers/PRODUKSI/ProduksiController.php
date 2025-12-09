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
    protected $db;

    public function __construct()
    {
        $this->produksiModel = new ProduksiModel();
        $this->alatModel = new AlatdanBahanModel();
        $this->operatorModel = new OperatorModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Produksi');
        }

        // Gunakan database query langsung untuk menghindari error
        $today = date('Y-m-d');
        
        // Data dari PPIC (ppic_produksi)
        $ppic_plans = $this->db->table('ppic_produksi')
            ->select('COUNT(*) as total_rencana')
            ->get()->getRowArray();
        
        // Data material dari PPIC (ppic_material)
        $ppic_materials = $this->db->table('ppic_material')
            ->select('COUNT(*) as total_material')
            ->get()->getRowArray();
        
        // Produksi hari ini dari produksi_hasil
        $produksi_hari_ini = $this->db->table('produksi_hasil')
            ->where('DATE(tanggal_produksi)', $today)
            ->get()->getResultArray();

        $data = [
            'title' => 'Dashboard Produksi',
            'module' => 'produksi',
            'total_rencana_ppic' => $ppic_plans['total_rencana'] ?? 0,
            'total_material_ppic' => $ppic_materials['total_material'] ?? 0,
            'total_produksi' => $this->produksiModel->countAll(),
            'total_alat' => $this->alatModel->countAll(),
            'total_operator' => $this->operatorModel->countAll(),
            'produksi_hari_ini' => $produksi_hari_ini
        ];
        
        return view('produksi/dashboard', $data);
    }

    // HASIL PRODUKSI (SYNC DENGAN PPIC)
    public function hasil()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Get produksi with PPIC data
        $produksi_list = $this->db->table('produksi_hasil ph')
            ->select('ph.*, pp.nomor_plan, pp.produk as produk_ppic, 
                     u.name as operator_name, a.nama_alat')
            ->join('ppic_produksi pp', 'ph.id_ppic_produksi = pp.id', 'left')
            ->join('users u', 'ph.operator_id = u.id', 'left')
            ->join('alat a', 'ph.alat_id = a.id', 'left')
            ->orderBy('ph.tanggal_produksi', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Hasil Produksi - Produksi',
            'module' => 'produksi',
            'produksi' => $produksi_list
        ];
        
        return view('produksi/produksi/index', $data);
    }

    public function createHasil()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Get PPIC data
        $ppic_plans = $this->db->table('ppic_produksi')
            ->whereIn('status', ['planned', 'progress'])
            ->get()
            ->getResultArray();
        
        // Get PPIC materials
        $ppic_materials = $this->db->table('ppic_material')
            ->where('stok_aktual >', 0)
            ->whereIn('status_stok', ['tersedia', 'terbatas'])
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Tambah Hasil Produksi (Sync PPIC)',
            'module' => 'produksi',
            'validation' => \Config\Services::validation(),
            'ppic_plans' => $ppic_plans,
            'ppic_materials' => $ppic_materials,
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
            'id_ppic_produksi' => 'required',
            'nomor_produksi' => 'required|is_unique[produksi_hasil.nomor_produksi]',
            'tanggal_produksi' => 'required',
            'jumlah_hasil' => 'required|numeric',
            'kualitas' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Mulai transaksi
        $this->db->transStart();
        
        try {
            // 1. Get PPIC plan data
            $ppic_plan = $this->db->table('ppic_produksi')
                ->where('id', $this->request->getPost('id_ppic_produksi'))
                ->get()
                ->getRowArray();
            
            // 2. Insert to produksi_hasil
            $produksi_data = [
                'id_ppic_produksi' => $this->request->getPost('id_ppic_produksi'),
                'nomor_produksi' => $this->request->getPost('nomor_produksi'),
                'tanggal_produksi' => $this->request->getPost('tanggal_produksi'),
                'jumlah_hasil' => $this->request->getPost('jumlah_hasil'),
                'kualitas' => $this->request->getPost('kualitas'),
                'status_produksi' => 'completed',
                'operator_id' => $this->request->getPost('operator_id'),
                'alat_id' => $this->request->getPost('alat_id'),
                'keterangan' => $this->request->getPost('keterangan'),
                'produk_ppic' => $ppic_plan['produk'] ?? null
            ];
            
            $this->db->table('produksi_hasil')->insert($produksi_data);
            $produksi_id = $this->db->insertID();
            
            // 3. Update PPIC progress
            if ($ppic_plan) {
                $new_jumlah_hasil = $ppic_plan['jumlah_hasil'] + $produksi_data['jumlah_hasil'];
                $persentase = ($new_jumlah_hasil / $ppic_plan['jumlah_target']) * 100;
                
                $ppic_update = [
                    'jumlah_hasil' => $new_jumlah_hasil,
                    'persentase_selesai' => min($persentase, 100)
                ];
                
                if ($persentase >= 100) {
                    $ppic_update['status'] = 'completed';
                    $ppic_update['status_produksi'] = 'selesai';
                } else {
                    $ppic_update['status'] = 'progress';
                    $ppic_update['status_produksi'] = 'proses';
                }
                
                $this->db->table('ppic_produksi')
                    ->where('id', $ppic_plan['id'])
                    ->update($ppic_update);
            }
            
            // 4. Process materials (if any)
            $materials = $this->request->getPost('materials');
            if ($materials && is_array($materials)) {
                foreach ($materials as $material) {
                    if (empty($material['material_id']) || empty($material['jumlah'])) continue;
                    
                    // Check material stock
                    $material_info = $this->db->table('ppic_material')
                        ->where('id', $material['material_id'])
                        ->get()
                        ->getRowArray();
                    
                    if ($material_info && $material_info['stok_aktual'] >= $material['jumlah']) {
                        // Insert material usage
                        $this->db->table('produksi_material_digunakan')->insert([
                            'produksi_hasil_id' => $produksi_id,
                            'ppic_material_id' => $material['material_id'],
                            'jumlah_digunakan' => $material['jumlah']
                        ]);
                        
                        // Reduce PPIC material stock
                        $new_stock = $material_info['stok_aktual'] - $material['jumlah'];
                        $status_stok = $this->calculateStockStatus($new_stock, $material_info['stok_minimal']);
                        
                        $this->db->table('ppic_material')
                            ->where('id', $material['material_id'])
                            ->update([
                                'stok_aktual' => $new_stock,
                                'status_stok' => $status_stok
                            ]);
                    }
                }
            }
            
            $this->db->transComplete();
            
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil disimpan dan data PPIC telah diupdate');
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data produksi: ' . $e->getMessage());
        }
    }

    public function editHasil($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $produksi = $this->db->table('produksi_hasil')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$produksi) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data tidak ditemukan');
        }

        $ppic_plans = $this->db->table('ppic_produksi')
            ->whereIn('status', ['planned', 'progress'])
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Edit Hasil Produksi',
            'module' => 'produksi',
            'validation' => \Config\Services::validation(),
            'produksi' => $produksi,
            'ppic_plans' => $ppic_plans,
            'alat_list' => $this->alatModel->findAll(),
            'operator_list' => $this->operatorModel->findAll()
        ];

        return view('produksi/produksi/edit', $data);
    }

    public function updateHasil($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $produksi = $this->db->table('produksi_hasil')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$produksi) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data tidak ditemukan');
        }

        $rules = [
            'id_ppic_produksi' => 'required',
            'nomor_produksi' => "required|is_unique[produksi_hasil.nomor_produksi,id,$id]",
            'tanggal_produksi' => 'required',
            'jumlah_hasil' => 'required|numeric',
            'kualitas' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get PPIC plan
        $ppic_plan = $this->db->table('ppic_produksi')
            ->where('id', $this->request->getPost('id_ppic_produksi'))
            ->get()
            ->getRowArray();

        $data = [
            'id_ppic_produksi' => $this->request->getPost('id_ppic_produksi'),
            'nomor_produksi' => $this->request->getPost('nomor_produksi'),
            'tanggal_produksi' => $this->request->getPost('tanggal_produksi'),
            'jumlah_hasil' => $this->request->getPost('jumlah_hasil'),
            'kualitas' => $this->request->getPost('kualitas'),
            'operator_id' => $this->request->getPost('operator_id'),
            'alat_id' => $this->request->getPost('alat_id'),
            'keterangan' => $this->request->getPost('keterangan'),
            'produk_ppic' => $ppic_plan['produk'] ?? null
        ];

        $this->db->transStart();
        
        try {
            // Update produksi
            $this->db->table('produksi_hasil')
                ->where('id', $id)
                ->update($data);
            
            // Update PPIC progress (if PPIC plan changed or quantity changed)
            $old_ppic_id = $produksi['id_ppic_produksi'];
            $new_ppic_id = $data['id_ppic_produksi'];
            $old_jumlah = $produksi['jumlah_hasil'];
            $new_jumlah = $data['jumlah_hasil'];
            
            if ($old_ppic_id != $new_ppic_id || $old_jumlah != $new_jumlah) {
                // Rollback old PPIC plan
                if ($old_ppic_id) {
                    $old_ppic = $this->db->table('ppic_produksi')
                        ->where('id', $old_ppic_id)
                        ->get()
                        ->getRowArray();
                    
                    if ($old_ppic) {
                        $new_old_jumlah = max(0, $old_ppic['jumlah_hasil'] - $old_jumlah);
                        $this->updatePpicProgress($old_ppic_id, $new_old_jumlah);
                    }
                }
                
                // Update new PPIC plan
                if ($new_ppic_id) {
                    $this->updatePpicProgress($new_ppic_id, $new_jumlah);
                }
            }
            
            $this->db->transComplete();
            
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil diperbarui');
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data produksi: ' . $e->getMessage());
        }
    }

    public function deleteHasil($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $this->db->transStart();
        
        try {
            // Get produksi data
            $produksi = $this->db->table('produksi_hasil')
                ->where('id', $id)
                ->get()
                ->getRowArray();
            
            if (!$produksi) {
                throw new \Exception('Data produksi tidak ditemukan');
            }
            
            // Rollback PPIC progress
            if ($produksi['id_ppic_produksi']) {
                $ppic = $this->db->table('ppic_produksi')
                    ->where('id', $produksi['id_ppic_produksi'])
                    ->get()
                    ->getRowArray();
                
                if ($ppic) {
                    $new_jumlah_hasil = max(0, $ppic['jumlah_hasil'] - $produksi['jumlah_hasil']);
                    $this->updatePpicProgress($produksi['id_ppic_produksi'], $new_jumlah_hasil);
                }
            }
            
            // Rollback material stock
            $materials_used = $this->db->table('produksi_material_digunakan')
                ->where('produksi_hasil_id', $id)
                ->get()
                ->getResultArray();
            
            foreach ($materials_used as $material) {
                $material_info = $this->db->table('ppic_material')
                    ->where('id', $material['ppic_material_id'])
                    ->get()
                    ->getRowArray();
                
                if ($material_info) {
                    $new_stock = $material_info['stok_aktual'] + $material['jumlah_digunakan'];
                    $status_stok = $this->calculateStockStatus($new_stock, $material_info['stok_minimal']);
                    
                    $this->db->table('ppic_material')
                        ->where('id', $material['ppic_material_id'])
                        ->update([
                            'stok_aktual' => $new_stock,
                            'status_stok' => $status_stok
                        ]);
                }
            }
            
            // Delete material usage records
            $this->db->table('produksi_material_digunakan')
                ->where('produksi_hasil_id', $id)
                ->delete();
            
            // Delete produksi
            $this->db->table('produksi_hasil')
                ->where('id', $id)
                ->delete();
            
            $this->db->transComplete();
            
            return redirect()->to('/produksi/hasil')->with('success', 'Data produksi berhasil dihapus dan data PPIC telah dikembalikan');
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->to('/produksi/hasil')->with('error', 'Gagal menghapus data produksi: ' . $e->getMessage());
        }
    }

    public function viewHasil($id)
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $produksi = $this->db->table('produksi_hasil ph')
            ->select('ph.*, pp.nomor_plan, pp.produk, pp.jumlah_target, 
                     u.name as operator_name, a.nama_alat')
            ->join('ppic_produksi pp', 'ph.id_ppic_produksi = pp.id', 'left')
            ->join('users u', 'ph.operator_id = u.id', 'left')
            ->join('alat a', 'ph.alat_id = a.id', 'left')
            ->where('ph.id', $id)
            ->get()
            ->getRowArray();

        if (!$produksi) {
            return redirect()->to('/produksi/hasil')->with('error', 'Data tidak ditemukan');
        }

        // Get materials used
        $materials_used = $this->db->table('produksi_material_digunakan pmd')
            ->select('pmd.*, pm.kode_material, pm.nama_material, pm.satuan')
            ->join('ppic_material pm', 'pmd.ppic_material_id = pm.id')
            ->where('pmd.produksi_hasil_id', $id)
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Detail Hasil Produksi',
            'module' => 'produksi',
            'produksi' => $produksi,
            'materials_used' => $materials_used
        ];

        return view('produksi/produksi/view', $data);
    }

    // ========== HELPER FUNCTIONS ==========

    private function updatePpicProgress($ppic_id, $jumlah_hasil)
    {
        $ppic = $this->db->table('ppic_produksi')
            ->where('id', $ppic_id)
            ->get()
            ->getRowArray();
        
        if (!$ppic) return false;
        
        $persentase = ($jumlah_hasil / $ppic['jumlah_target']) * 100;
        
        $update_data = [
            'jumlah_hasil' => $jumlah_hasil,
            'persentase_selesai' => $persentase
        ];
        
        if ($jumlah_hasil <= 0) {
            $update_data['status'] = 'planned';
            $update_data['status_produksi'] = 'menunggu';
        } elseif ($persentase >= 100) {
            $update_data['status'] = 'completed';
            $update_data['status_produksi'] = 'selesai';
        } else {
            $update_data['status'] = 'progress';
            $update_data['status_produksi'] = 'proses';
        }
        
        return $this->db->table('ppic_produksi')
            ->where('id', $ppic_id)
            ->update($update_data);
    }

    private function calculateStockStatus($stok, $stok_minimal)
    {
        if ($stok <= 0) return 'habis';
        if ($stok <= $stok_minimal) return 'terbatas';
        return 'tersedia';
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