<?php

namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;

class ProduksiSyncController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Produksi');
        }
    }

    public function index()
    {
        // Get all data from ppic_produksi table
        $ppic_produksi = $this->db->table('ppic_produksi pp')
            ->select('pp.*, 
                     u.nama_lengkap as operator_name,
                     pa.nama_alat as alat_nama')
            ->join('users u', 'pp.operator_id = u.id', 'left')
            ->join('produksi_alat pa', 'pp.alat_id = pa.id', 'left')
            ->orderBy('pp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();
        
        $data = [
            'title' => 'Data PPIC Produksi (Sync)',
            'module' => 'produksi',
            'ppic_produksi' => $ppic_produksi
        ];
        
        return view('produksi/ppic/index', $data);
    }
    
    public function create()
    {
        // Get operators
        $operators = $this->db->table('users')
            ->select('id, nama_lengkap, username')
            ->where('role', 'operator')
            ->orWhere('role', 'staff')
            ->orWhere('divisi_id', 5)
            ->orderBy('nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get alat from produksi_alat
        $alat_list = $this->db->table('produksi_alat')
            ->where('status', 'aktif')
            ->orderBy('nama_alat', 'ASC')
            ->get()
            ->getResultArray();
        
        // Generate plan number
        $last = $this->db->table('ppic_produksi')
            ->select('nomor_plan')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        
        $last_number = $last ? ((int) substr($last['nomor_plan'], -3)) : 0;
        $next_number = 'PLAN-' . date('ymd') . '-' . str_pad($last_number + 1, 3, '0', STR_PAD_LEFT);
        
        $data = [
            'title' => 'Buat Rencana PPIC Produksi',
            'module' => 'produksi',
            'operators' => $operators,
            'alat_list' => $alat_list,
            'next_number' => $next_number,
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/ppic/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'nomor_plan' => 'required|is_unique[ppic_produksi.nomor_plan]',
            'produk' => 'required',
            'jumlah_target' => 'required|numeric|greater_than[0]',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Prepare data for ppic_produksi
        $ppic_data = [
            'nomor_plan' => $this->request->getPost('nomor_plan'),
            'produk' => $this->request->getPost('produk'),
            'produk_id' => $this->request->getPost('produk_id') ?: null,
            'jumlah_target' => $this->request->getPost('jumlah_target'),
            'jumlah_hasil' => 0,
            'kualitas' => $this->request->getPost('kualitas') ?? 'baik',
            'persentase_selesai' => 0,
            'material_terpakai' => $this->request->getPost('material_terpakai'),
            'biaya_produksi' => $this->request->getPost('biaya_produksi') ?? 0,
            'operator_id' => $this->request->getPost('operator_id') ?: null,
            'alat_id' => $this->request->getPost('alat_id') ?: null,
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'tanggal_produksi' => $this->request->getPost('tanggal_produksi') ?? $this->request->getPost('tanggal_mulai'),
            'status' => $this->request->getPost('status') ?? 'planned',
            'status_produksi' => 'menunggu',
            'keterangan' => $this->request->getPost('keterangan')
        ];
        
        // Insert to ppic_produksi
        $this->db->table('ppic_produksi')->insert($ppic_data);
        
        return redirect()->to('/produksi/sync')->with('success', 'Rencana PPIC produksi berhasil disimpan!');
    }
    
    public function view($id)
    {
        $ppic = $this->db->table('ppic_produksi pp')
            ->select('pp.*, 
                     u.nama_lengkap as operator_name,
                     pa.nama_alat as alat_nama')
            ->join('users u', 'pp.operator_id = u.id', 'left')
            ->join('produksi_alat pa', 'pp.alat_id = pa.id', 'left')
            ->where('pp.id', $id)
            ->get()
            ->getRowArray();
        
        if (!$ppic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Get production history from produksi_hasil
        $produksi_history = $this->db->table('produksi_hasil')
            ->where('id_ppic_produksi', $id)
            ->orderBy('tanggal_produksi', 'DESC')
            ->get()
            ->getResultArray();
        
        $data = [
            'title' => 'Detail Rencana PPIC Produksi',
            'module' => 'produksi',
            'ppic' => $ppic,
            'produksi_history' => $produksi_history
        ];
        
        return view('produksi/ppic/view', $data);
    }
    
    public function edit($id)
    {
        $ppic = $this->db->table('ppic_produksi')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$ppic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Get operators
        $operators = $this->db->table('users')
            ->select('id, nama_lengkap, username')
            ->where('role', 'operator')
            ->orWhere('role', 'staff')
            ->orWhere('divisi_id', 5)
            ->orderBy('nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get alat from produksi_alat
        $alat_list = $this->db->table('produksi_alat')
            ->where('status', 'aktif')
            ->orderBy('nama_alat', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get total from produksi_hasil for reference
        $total_produksi_hasil = $this->db->table('produksi_hasil')
            ->selectSum('jumlah_hasil')
            ->where('id_ppic_produksi', $id)
            ->get()
            ->getRowArray();
        
        $data = [
            'title' => 'Edit Rencana PPIC Produksi',
            'module' => 'produksi',
            'ppic' => $ppic,
            'operators' => $operators,
            'alat_list' => $alat_list,
            'total_produksi_hasil' => $total_produksi_hasil['jumlah_hasil'] ?? 0,
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/ppic/edit', $data);
    }

    public function update($id)
    {
        $ppic = $this->db->table('ppic_produksi')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$ppic) {
            return redirect()->to('/produksi/sync')->with('error', 'Data tidak ditemukan');
        }
        
        $rules = [
            'nomor_plan' => "required|is_unique[ppic_produksi.nomor_plan,id,$id]",
            'produk' => 'required',
            'jumlah_hasil' => 'required|numeric|greater_than_equal_to[0]',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Calculate percentage
        $jumlah_hasil = $this->request->getPost('jumlah_hasil');
        $persentase = ($jumlah_hasil / $ppic['jumlah_target']) * 100;
        
        // Update data for ppic_produksi
        $ppic_data = [
            'nomor_plan' => $this->request->getPost('nomor_plan'),
            'produk' => $this->request->getPost('produk'),
            'produk_id' => $this->request->getPost('produk_id') ?: null,
            'jumlah_hasil' => $jumlah_hasil, // UPDATE jumlah_hasil
            'persentase_selesai' => min($persentase, 100), // UPDATE persentase
            'kualitas' => $this->request->getPost('kualitas') ?? 'baik',
            'material_terpakai' => $this->request->getPost('material_terpakai'),
            'biaya_produksi' => $this->request->getPost('biaya_produksi') ?? 0,
            'operator_id' => $this->request->getPost('operator_id') ?: null,
            'alat_id' => $this->request->getPost('alat_id') ?: null,
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'tanggal_produksi' => $this->request->getPost('tanggal_produksi') ?? $this->request->getPost('tanggal_mulai'),
            'status' => $this->request->getPost('status') ?? 'planned',
            'status_produksi' => $this->request->getPost('status_produksi') ?? 'menunggu',
            'keterangan' => $this->request->getPost('keterangan')
            // TIDAK update jumlah_target karena tidak boleh diubah
        ];
        
        // Update ppic_produksi
        $this->db->table('ppic_produksi')
            ->where('id', $id)
            ->update($ppic_data);
        
        // Auto-update status based on percentage
        $this->autoUpdateStatus($id, $persentase);
        
        return redirect()->to('/produksi/sync')->with('success', 'Data PPIC produksi berhasil diperbarui!');
    }

    // TAMBAHKAN METODE INI untuk menangani update status melalui URL
    public function updateStatus($id, $status)
    {
        $ppic = $this->db->table('ppic_produksi')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$ppic) {
            return redirect()->to('/produksi/sync')->with('error', 'Data tidak ditemukan');
        }
        
        // Validasi status yang diizinkan
        $allowed_statuses = ['planned', 'progress', 'completed'];
        if (!in_array($status, $allowed_statuses)) {
            return redirect()->to('/produksi/sync')->with('error', 'Status tidak valid');
        }
        
        // Tentukan status_produksi berdasarkan status
        $status_produksi_map = [
            'planned' => 'menunggu',
            'progress' => 'proses',
            'completed' => 'selesai'
        ];
        
        // Update status
        $update_data = [
            'status' => $status,
            'status_produksi' => $status_produksi_map[$status] ?? 'menunggu'
        ];
        
        $this->db->table('ppic_produksi')
            ->where('id', $id)
            ->update($update_data);
        
        return redirect()->to('/produksi/sync')->with('success', 'Status berhasil diperbarui menjadi ' . $status);
    }

    // TAMBAHKAN METODE INI untuk menangani delete
    public function delete($id)
    {
        $ppic = $this->db->table('ppic_produksi')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$ppic) {
            return redirect()->to('/produksi/sync')->with('error', 'Data tidak ditemukan');
        }
        
        // Cek apakah ada produksi_hasil terkait
        $has_history = $this->db->table('produksi_hasil')
            ->where('id_ppic_produksi', $id)
            ->countAllResults();
        
        if ($has_history > 0) {
            return redirect()->to('/produksi/sync')->with('error', 'Tidak dapat menghapus data karena sudah ada riwayat produksi');
        }
        
        // Hapus data
        $this->db->table('ppic_produksi')
            ->where('id', $id)
            ->delete();
        
        return redirect()->to('/produksi/sync')->with('success', 'Data berhasil dihapus');
    }

    // TAMBAHKAN METODE INI untuk AJAX get ppic detail
    public function ajaxGetPpicDetail($id)
    {
        $ppic = $this->db->table('ppic_produksi pp')
            ->select('pp.*, 
                     u.nama_lengkap as operator_name,
                     pa.nama_alat as alat_nama')
            ->join('users u', 'pp.operator_id = u.id', 'left')
            ->join('produksi_alat pa', 'pp.alat_id = pa.id', 'left')
            ->where('pp.id', $id)
            ->get()
            ->getRowArray();
        
        if (!$ppic) {
            return $this->response->setJSON(['error' => 'Data tidak ditemukan']);
        }
        
        return $this->response->setJSON($ppic);
    }

    // TAMBAHKAN METODE INI untuk AJAX get material stock (jika diperlukan)
    public function ajaxGetMaterialStock($material_id)
    {
        // Implementasi sesuai kebutuhan
        // Contoh sederhana:
        $material = $this->db->table('inventory')
            ->where('id', $material_id)
            ->get()
            ->getRowArray();
        
        if (!$material) {
            return $this->response->setJSON(['error' => 'Material tidak ditemukan']);
        }
        
        return $this->response->setJSON([
            'id' => $material['id'],
            'stok' => $material['stok'] ?? 0,
            'nama' => $material['nama'] ?? ''
        ]);
    }

    private function autoUpdateStatus($id, $persentase)
    {
        $update_data = [];
        
        if ($persentase >= 100) {
            $update_data['status'] = 'completed';
            $update_data['status_produksi'] = 'selesai';
        } elseif ($persentase > 0) {
            $update_data['status'] = 'progress';
            $update_data['status_produksi'] = 'proses';
        } else {
            $update_data['status'] = 'planned';
            $update_data['status_produksi'] = 'menunggu';
        }
        
        if (!empty($update_data)) {
            $this->db->table('ppic_produksi')
                ->where('id', $id)
                ->update($update_data);
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
}