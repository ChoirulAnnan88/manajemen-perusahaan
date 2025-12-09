<?php

namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;

class AlatdanBahanController extends BaseController
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
        // Get alat from produksi_alat
        $alat = $this->db->table('produksi_alat')
            ->orderBy('nama_alat', 'ASC')
            ->get()
            ->getResultArray();
        
        // Get bahan/material from ppic_material
        $bahan = $this->db->table('ppic_material')
            ->orderBy('nama_material', 'ASC')
            ->get()
            ->getResultArray();
        
        $data = [
            'title' => 'Alat dan Bahan Produksi',
            'module' => 'produksi',
            'alat' => $alat ?? [],
            'bahan' => $bahan ?? []
        ];
        
        return view('produksi/alat_dan_bahan/index', $data);
    }
    
    // ================ METHOD BARU UNTUK PENGURANGAN STOK ================
    
    public function updateStokBahan()
    {
        $id = $this->request->getPost('id');
        $jumlah_kurang = $this->request->getPost('jumlah_kurang');
        $keterangan = $this->request->getPost('keterangan');
        
        // Validasi input
        if (empty($id) || empty($jumlah_kurang)) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Data tidak lengkap');
        }
        
        // Cek bahan
        $bahan = $this->db->table('ppic_material')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$bahan) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Bahan tidak ditemukan');
        }
        
        // Validasi jumlah
        if ($jumlah_kurang <= 0) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Jumlah pengurangan harus lebih dari 0');
        }
        
        if ($jumlah_kurang > $bahan['stok_aktual']) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Jumlah pengurangan melebihi stok yang tersedia');
        }
        
        // Hitung stok baru
        $stok_baru = $bahan['stok_aktual'] - $jumlah_kurang;
        
        // Tentukan status stok berdasarkan jumlah baru
        if ($stok_baru <= 0) {
            $status_stok = 'habis';
        } elseif ($stok_baru <= $bahan['stok_minimal']) {
            $status_stok = 'terbatas';
        } else {
            $status_stok = 'tersedia';
        }
        
        // Update stok
        $data = [
            'stok_aktual' => $stok_baru,
            'status_stok' => $status_stok,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->table('ppic_material')
            ->where('id', $id)
            ->update($data);
        
        // Simpan history pengurangan stok (jika ada tabel history_stok)
        try {
            if ($this->db->tableExists('history_stok_material')) {
                $historyData = [
                    'material_id' => $id,
                    'kode_material' => $bahan['kode_material'],
                    'nama_material' => $bahan['nama_material'],
                    'jumlah' => -$jumlah_kurang,
                    'stok_sebelum' => $bahan['stok_aktual'],
                    'stok_sesudah' => $stok_baru,
                    'jenis_transaksi' => 'pengurangan_produksi',
                    'keterangan' => $keterangan ?? 'Pengurangan stok untuk produksi',
                    'tanggal' => date('Y-m-d H:i:s'),
                    'user_id' => session()->get('id') ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->table('history_stok_material')->insert($historyData);
            }
        } catch (\Exception $e) {
            // Biarkan error di history tidak mengganggu proses utama
            log_message('error', 'Gagal menyimpan history stok: ' . $e->getMessage());
        }
        
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Stok berhasil dikurangi ' . $jumlah_kurang . ' ' . $bahan['satuan'] . '. Stok baru: ' . $stok_baru . ' ' . $bahan['satuan']);
    }
    
    // ================ METHOD YANG SUDAH ADA ================
    
    public function viewAlat($id)
    {
        $alat = $this->db->table('produksi_alat')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$alat) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Alat tidak ditemukan');
        }
        
        $data = [
            'title' => 'Detail Alat/Mesin',
            'module' => 'produksi',
            'alat' => $alat
        ];
        
        return view('produksi/alat_dan_bahan/view', $data);
    }
    
    public function createAlat()
    {
        $data = [
            'title' => 'Tambah Alat/Mesin Baru',
            'module' => 'produksi',
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/alat_dan_bahan/create_alat', $data);
    }
    
    public function storeAlat()
    {
        $rules = [
            'kode_alat' => 'required|is_unique[produksi_alat.kode_alat]',
            'nama_alat' => 'required',
            'tipe' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'kode_alat' => $this->request->getPost('kode_alat'),
            'nama_alat' => $this->request->getPost('nama_alat'),
            'tipe' => $this->request->getPost('tipe'),
            'kategori' => $this->request->getPost('kategori'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'status' => $this->request->getPost('status') ?? 'aktif',
            'kondisi' => $this->request->getPost('kondisi') ?? 'baik',
            'tanggal_maintenance' => $this->request->getPost('tanggal_maintenance') ?: null,
            'lokasi' => $this->request->getPost('lokasi') ?? 'Gudang Produksi',
            'keterangan' => $this->request->getPost('keterangan')
        ];
        
        $this->db->table('produksi_alat')->insert($data);
        
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Alat/mesin berhasil ditambahkan!');
    }
    
    public function editAlat($id)
    {
        $alat = $this->db->table('produksi_alat')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$alat) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Alat tidak ditemukan');
        }
        
        $data = [
            'title' => 'Edit Alat/Mesin',
            'module' => 'produksi',
            'alat' => $alat,
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/alat_dan_bahan/edit_alat', $data);
    }
    
    public function updateAlat($id)
    {
        $alat = $this->db->table('produksi_alat')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$alat) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Alat tidak ditemukan');
        }
        
        $rules = [
            'kode_alat' => "required|is_unique[produksi_alat.kode_alat,id,$id]",
            'nama_alat' => 'required',
            'tipe' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'kode_alat' => $this->request->getPost('kode_alat'),
            'nama_alat' => $this->request->getPost('nama_alat'),
            'tipe' => $this->request->getPost('tipe'),
            'kategori' => $this->request->getPost('kategori'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'status' => $this->request->getPost('status') ?? 'aktif',
            'kondisi' => $this->request->getPost('kondisi') ?? 'baik',
            'tanggal_maintenance' => $this->request->getPost('tanggal_maintenance') ?: null,
            'lokasi' => $this->request->getPost('lokasi') ?? 'Gudang Produksi',
            'keterangan' => $this->request->getPost('keterangan'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->table('produksi_alat')
            ->where('id', $id)
            ->update($data);
        
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Alat/mesin berhasil diperbarui!');
    }
    
    public function deleteAlat($id)
    {
        $alat = $this->db->table('produksi_alat')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$alat) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Alat tidak ditemukan');
        }
        
        $this->db->table('produksi_alat')
            ->where('id', $id)
            ->delete();
        
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Alat/mesin berhasil dihapus!');
    }
    
    public function updateStatusAlat($id, $status)
    {
        $allowed_statuses = ['aktif', 'maintenance', 'rusak'];
        
        if (!in_array($status, $allowed_statuses)) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Status tidak valid');
        }
        
        $this->db->table('produksi_alat')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Status alat berhasil diperbarui!');
    }
    
    public function createMaterial()
    {
        $data = [
            'title' => 'Tambah Bahan/Material Baru',
            'module' => 'produksi',
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/alat_dan_bahan/create_material', $data);
    }
    
    public function storeMaterial()
    {
        $rules = [
            'kode_material' => 'required|is_unique[ppic_material.kode_material]',
            'nama_material' => 'required',
            'satuan' => 'required',
            'stok_aktual' => 'required|numeric|greater_than_equal_to[0]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $stok_aktual = $this->request->getPost('stok_aktual');
        
        // Tentukan status stok berdasarkan jumlah
        if ($stok_aktual <= 0) {
            $status_stok = 'habis';
        } elseif ($stok_aktual <= ($this->request->getPost('stok_minimal') ?? 10)) {
            $status_stok = 'terbatas';
        } else {
            $status_stok = 'tersedia';
        }
        
        $data = [
            'kode_material' => $this->request->getPost('kode_material'),
            'nama_material' => $this->request->getPost('nama_material'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'stok_aktual' => $stok_aktual,
            'stok_minimal' => $this->request->getPost('stok_minimal') ?? 10,
            'satuan' => $this->request->getPost('satuan'),
            'status_stok' => $status_stok,
            'keterangan' => $this->request->getPost('keterangan'),
            'lokasi' => $this->request->getPost('lokasi') ?? 'Gudang Material Produksi',
            'harga_satuan' => $this->request->getPost('harga_satuan') ?? 0
        ];
        
        $this->db->table('ppic_material')->insert($data);
        
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Bahan/material berhasil ditambahkan!');
    }
    
    public function viewMaterial($id)
    {
        $material = $this->db->table('ppic_material')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$material) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Material tidak ditemukan');
        }
        
        $data = [
            'title' => 'Detail Material',
            'module' => 'produksi',
            'material' => $material
        ];
        
        return view('produksi/alat_dan_bahan/view_material', $data);
    }
    
    public function editMaterial($id)
    {
        $material = $this->db->table('ppic_material')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$material) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Material tidak ditemukan');
        }
        
        $data = [
            'title' => 'Edit Material',
            'module' => 'produksi',
            'material' => $material,
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/alat_dan_bahan/edit_material', $data);
    }
    
    public function updateMaterial($id)
    {
        $material = $this->db->table('ppic_material')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$material) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Material tidak ditemukan');
        }
        
        $rules = [
            'kode_material' => "required|is_unique[ppic_material.kode_material,id,$id]",
            'nama_material' => 'required',
            'satuan' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $stok_aktual = $this->request->getPost('stok_aktual');
        
        // Tentukan status stok berdasarkan jumlah
        if ($stok_aktual <= 0) {
            $status_stok = 'habis';
        } elseif ($stok_aktual <= $this->request->getPost('stok_minimal')) {
            $status_stok = 'terbatas';
        } else {
            $status_stok = 'tersedia';
        }
        
        $data = [
            'kode_material' => $this->request->getPost('kode_material'),
            'nama_material' => $this->request->getPost('nama_material'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'stok_aktual' => $stok_aktual,
            'stok_minimal' => $this->request->getPost('stok_minimal'),
            'satuan' => $this->request->getPost('satuan'),
            'status_stok' => $status_stok,
            'keterangan' => $this->request->getPost('keterangan'),
            'lokasi' => $this->request->getPost('lokasi'),
            'harga_satuan' => $this->request->getPost('harga_satuan') ?? 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->table('ppic_material')
            ->where('id', $id)
            ->update($data);
        
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Material berhasil diperbarui!');
    }
    
    public function deleteMaterial($id)
    {
        $material = $this->db->table('ppic_material')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        
        if (!$material) {
            return redirect()->to(base_url('produksi/alat'))->with('error', 'Material tidak ditemukan');
        }
        
        $this->db->table('ppic_material')
            ->where('id', $id)
            ->delete();
        
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Material berhasil dihapus!');
    }
    
    public function syncMaterial($id)
    {
        // Method untuk sinkronisasi data material
        return redirect()->to(base_url('produksi/alat'))->with('success', 'Material berhasil disinkronisasi!');
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