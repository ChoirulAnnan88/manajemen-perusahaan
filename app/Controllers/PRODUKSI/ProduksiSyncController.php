<?php

namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;
use App\Models\PRODUKSI\ProduksiSyncModel;

class ProduksiSyncController extends BaseController
{
    protected $db;
    protected $produksiSyncModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->produksiSyncModel = new ProduksiSyncModel();
        
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Produksi');
        }
    }

    public function index()
    {
        // Get produksi with PPIC data (FIXED: pakai produksi_alat bukan alat)
        $produksi_list = $this->db->table('produksi_hasil ph')
            ->select('ph.*, pp.nomor_plan, pp.produk as nama_produk_ppic, 
                     u.name as operator_name, pa.nama_alat') // PAKAI pa.nama_alat
            ->join('ppic_produksi pp', 'ph.id_ppic_produksi = pp.id', 'left')
            ->join('users u', 'ph.operator_id = u.id', 'left')
            ->join('produksi_alat pa', 'ph.alat_id = pa.id', 'left') // FIX: produksi_alat bukan alat
            ->orderBy('ph.tanggal_produksi', 'DESC')
            ->get()
            ->getResultArray();
        
        $data = [
            'title' => 'Data Produksi (Sync dengan PPIC)',
            'module' => 'produksi',
            'produksi_list' => $produksi_list
        ];
        
        return view('produksi/produksi/index', $data);
    }
    
    public function create()
    {
        // Get PPIC plans
        $ppic_plans = $this->db->table('ppic_produksi')
            ->whereIn('status', ['planned', 'progress'])
            ->orderBy('tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();
        
        // Get PPIC materials
        $ppic_materials = $this->db->table('ppic_material')
            ->where('stok_aktual >', 0)
            ->whereIn('status_stok', ['tersedia', 'terbatas'])
            ->orderBy('nama_material', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get operators from users table
        $operators = $this->db->table('users')
            ->where('role', 'operator')
            ->orWhere('role', 'staff')
            ->orWhere('divisi_id', 5) // Produksi division
            ->get()
            ->getResultArray();
        
        // Get alat from produksi_alat table (FIXED)
        $alat_list = $this->db->table('produksi_alat')
            ->where('status', 'aktif')
            ->orderBy('nama_alat', 'ASC')
            ->get()
            ->getResultArray();
        
        // Generate production number
        $last = $this->db->table('produksi_hasil')
            ->select('nomor_produksi')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        
        $last_number = $last ? ((int) substr($last['nomor_produksi'], -3)) : 0;
        $next_number = 'PROD-' . date('ymd') . '-' . str_pad($last_number + 1, 3, '0', STR_PAD_LEFT);
        
        $data = [
            'title' => 'Buat Produksi Baru (Sync PPIC)',
            'module' => 'produksi',
            'ppic_plans' => $ppic_plans,
            'materials' => $ppic_materials,
            'operators' => $operators,
            'alat_list' => $alat_list,
            'next_number' => $next_number,
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/produksi/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'id_ppic_produksi' => 'required',
            'nomor_produksi' => 'required|is_unique[produksi_hasil.nomor_produksi]',
            'tanggal_produksi' => 'required',
            'jumlah_hasil' => 'required|numeric|greater_than[0]',
            'kualitas' => 'required|in_list[baik,cacat_ringan,cacat_berat]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Start transaction
        $this->db->transStart();
        
        try {
            // 1. Get PPIC plan data
            $ppic_plan = $this->db->table('ppic_produksi')
                ->where('id', $this->request->getPost('id_ppic_produksi'))
                ->get()
                ->getRowArray();
            
            if (!$ppic_plan) {
                throw new \Exception('Rencana PPIC tidak ditemukan');
            }
            
            // 2. Prepare production data
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
                'produk_ppic' => $ppic_plan['produk'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 3. Insert to produksi_hasil
            $this->db->table('produksi_hasil')->insert($produksi_data);
            $produksi_id = $this->db->insertID();
            
            // 4. Update PPIC progress
            $new_jumlah_hasil = $ppic_plan['jumlah_hasil'] + $produksi_data['jumlah_hasil'];
            $persentase = ($new_jumlah_hasil / $ppic_plan['jumlah_target']) * 100;
            
            $ppic_update = [
                'jumlah_hasil' => $new_jumlah_hasil,
                'persentase_selesai' => min($persentase, 100),
                'updated_at' => date('Y-m-d H:i:s')
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
            
            // 5. Process materials (if any)
            $materials = $this->request->getPost('materials');
            if ($materials && is_array($materials)) {
                foreach ($materials as $material) {
                    if (empty($material['material_id']) || empty($material['jumlah'])) continue;
                    
                    // Check material stock
                    $material_info = $this->db->table('ppic_material')
                        ->where('id', $material['material_id'])
                        ->get()
                        ->getRowArray();
                    
                    if (!$material_info) continue;
                    
                    // Validate stock
                    if ($material_info['stok_aktual'] < $material['jumlah']) {
                        throw new \Exception("Stok {$material_info['nama_material']} tidak mencukupi. Stok tersedia: {$material_info['stok_aktual']}");
                    }
                    
                    // Insert material usage (jika tabel ada)
                    if ($this->db->tableExists('produksi_material_digunakan')) {
                        $this->db->table('produksi_material_digunakan')->insert([
                            'produksi_hasil_id' => $produksi_id,
                            'ppic_material_id' => $material['material_id'],
                            'kode_material' => $material_info['kode_material'],
                            'nama_material' => $material_info['nama_material'],
                            'jumlah_digunakan' => $material['jumlah'],
                            'satuan' => $material_info['satuan'],
                            'harga_satuan' => $material_info['harga_satuan'] ?? 0,
                            'total_harga' => ($material_info['harga_satuan'] ?? 0) * $material['jumlah'],
                            'tanggal_penggunaan' => $produksi_data['tanggal_produksi'],
                            'keterangan' => $material['keterangan'] ?? null,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    
                    // Reduce PPIC material stock
                    $new_stock = $material_info['stok_aktual'] - $material['jumlah'];
                    $status_stok = $this->calculateStockStatus($new_stock, $material_info['stok_minimal']);
                    
                    $this->db->table('ppic_material')
                        ->where('id', $material['material_id'])
                        ->update([
                            'stok_aktual' => $new_stock,
                            'status_stok' => $status_stok,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                }
            }
            
            $this->db->transComplete();
            
            return redirect()->to('/produksi/sync')->with('success', 'Produksi berhasil disimpan! Data PPIC telah diupdate.');
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
    
    public function view($id)
    {
        $produksi = $this->db->table('produksi_hasil ph')
            ->select('ph.*, pp.nomor_plan, pp.produk, pp.jumlah_target,
                     u.name as operator_name, pa.nama_alat') // FIX: pa.nama_alat
            ->join('ppic_produksi pp', 'ph.id_ppic_produksi = pp.id', 'left')
            ->join('users u', 'ph.operator_id = u.id', 'left')
            ->join('produksi_alat pa', 'ph.alat_id = pa.id', 'left') // FIX: produksi_alat bukan alat
            ->where('ph.id', $id)
            ->get()
            ->getRowArray();
        
        if (!$produksi) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Get materials used (jika tabel ada)
        $materials_used = [];
        if ($this->db->tableExists('produksi_material_digunakan')) {
            $materials_used = $this->db->table('produksi_material_digunakan pmd')
                ->select('pmd.*, pm.kode_material, pm.nama_material, pm.satuan')
                ->join('ppic_material pm', 'pmd.ppic_material_id = pm.id')
                ->where('pmd.produksi_hasil_id', $id)
                ->get()
                ->getResultArray();
        }
        
        $data = [
            'title' => 'Detail Produksi',
            'module' => 'produksi',
            'produksi' => $produksi,
            'materials_used' => $materials_used
        ];
        
        return view('produksi/produksi/view', $data);
    }
    
    public function delete($id)
    {
        $this->db->transStart();
        
        try {
            // Get production data
            $produksi = $this->db->table('produksi_hasil')
                ->where('id', $id)
                ->get()
                ->getRowArray();
            
            if (!$produksi) {
                throw new \Exception('Produksi tidak ditemukan');
            }
            
            // 1. Rollback PPIC progress
            if ($produksi['id_ppic_produksi']) {
                $ppic = $this->db->table('ppic_produksi')
                    ->where('id', $produksi['id_ppic_produksi'])
                    ->get()
                    ->getRowArray();
                
                if ($ppic) {
                    $new_jumlah_hasil = max(0, $ppic['jumlah_hasil'] - $produksi['jumlah_hasil']);
                    $persentase = ($new_jumlah_hasil / $ppic['jumlah_target']) * 100;
                    
                    $ppic_update = [
                        'jumlah_hasil' => $new_jumlah_hasil,
                        'persentase_selesai' => $persentase,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    if ($new_jumlah_hasil <= 0) {
                        $ppic_update['status'] = 'planned';
                        $ppic_update['status_produksi'] = 'menunggu';
                    } elseif ($persentase >= 100) {
                        $ppic_update['status'] = 'completed';
                        $ppic_update['status_produksi'] = 'selesai';
                    } else {
                        $ppic_update['status'] = 'progress';
                        $ppic_update['status_produksi'] = 'proses';
                    }
                    
                    $this->db->table('ppic_produksi')
                        ->where('id', $produksi['id_ppic_produksi'])
                        ->update($ppic_update);
                }
            }
            
            // 2. Rollback material stock (jika tabel ada)
            if ($this->db->tableExists('produksi_material_digunakan')) {
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
                                'status_stok' => $status_stok,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    }
                }
                
                // 3. Delete material usage records
                $this->db->table('produksi_material_digunakan')
                    ->where('produksi_hasil_id', $id)
                    ->delete();
            }
            
            // 4. Delete production
            $this->db->table('produksi_hasil')
                ->where('id', $id)
                ->delete();
            
            $this->db->transComplete();
            
            return redirect()->to('/produksi/sync')->with('success', 'Produksi berhasil dihapus dan data PPIC telah dikembalikan.');
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    public function ajaxGetPpicDetail($id)
    {
        $ppic = $this->db->table('ppic_produksi')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$ppic) {
            return $this->response->setJSON(['error' => 'Data tidak ditemukan'])->setStatusCode(404);
        }
        
        return $this->response->setJSON([
            'produk' => $ppic['produk'],
            'jumlah_target' => $ppic['jumlah_target'],
            'jumlah_hasil' => $ppic['jumlah_hasil'],
            'sisa_target' => max(0, $ppic['jumlah_target'] - $ppic['jumlah_hasil']),
            'status' => $ppic['status']
        ]);
    }
    
    public function ajaxGetMaterialStock($id)
    {
        $material = $this->db->table('ppic_material')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$material) {
            return $this->response->setJSON(['error' => 'Material tidak ditemukan'])->setStatusCode(404);
        }
        
        return $this->response->setJSON([
            'nama_material' => $material['nama_material'],
            'stok_aktual' => $material['stok_aktual'],
            'stok_minimal' => $material['stok_minimal'],
            'satuan' => $material['satuan'],
            'status_stok' => $material['status_stok']
        ]);
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