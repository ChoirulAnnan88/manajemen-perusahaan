<?php
namespace App\Controllers\HSE;

use App\Controllers\BaseController;
use App\Models\HSE\InsidenModel;
use App\Models\HSE\RisikoModel;
use App\Models\HSE\PelatihanModel;
use App\Models\HSE\LingkunganModel;
use App\Models\DivisiModel;

class HseController extends BaseController
{
    protected $insidenModel;
    protected $risikoModel;
    protected $pelatihanModel;
    protected $lingkunganModel;
    protected $divisiModel;
    
    public function __construct()
    {
        $this->insidenModel = new InsidenModel();
        $this->risikoModel = new RisikoModel();
        $this->pelatihanModel = new PelatihanModel();
        $this->lingkunganModel = new LingkunganModel();
        $this->divisiModel = new \App\Models\DivisiModel();
    }
    
    public function index()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi HSE');
        }

        $userId = session()->get('userId');
        $divisiId = session()->get('divisi_id');
        
        // Stats untuk dashboard
        $insidenStats = $this->insidenModel->getInsidenStats($userId, $divisiId);
        $risikoStats = $this->risikoModel->getRisikoStats($divisiId);
        $pelatihanStats = $this->pelatihanModel->getPelatihanStats(date('Y'));
        $lingkunganStats = $this->lingkunganModel->getLingkunganStats();
        
        // Latest data
        $latestInsiden = $this->insidenModel->getInsidenWithRelations();
        $latestInsiden = array_slice($latestInsiden, 0, 5);
        
        $latestRisiko = $this->risikoModel->getRisikoWithRelations();
        $latestRisiko = array_slice($latestRisiko, 0, 5);
        
        $latestLingkungan = $this->lingkunganModel->getLatestMeasurements(5);

        $data = [
            'title' => 'Dashboard HSE - Health, Safety & Environment',
            'module' => 'hse',
            'insidenStats' => $insidenStats,
            'risikoStats' => $risikoStats,
            'pelatihanStats' => $pelatihanStats,
            'lingkunganStats' => $lingkunganStats,
            'latestInsiden' => $latestInsiden,
            'latestRisiko' => $latestRisiko,
            'latestLingkungan' => $latestLingkungan,
        ];
        
        return view('hse/dashboard', $data);
    }
    
    // INSIDEN METHODS
    public function insiden()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $userId = session()->get('userId');
        $userRole = session()->get('role');
        $divisiId = session()->get('divisi_id');
        
        // Filter berdasarkan role
        if ($userRole === 'manager') {
            $insiden = $this->insidenModel->getInsidenWithRelations();
        } elseif ($userRole === 'staff') {
            // Staff HSE bisa lihat semua atau berdasarkan divisi
            $insiden = $this->insidenModel->getInsidenByDivisi($divisiId);
        } else {
            // Operator hanya melihat yang mereka laporkan
            $insiden = $this->insidenModel->where('pelapor_id', $userId)->findAll();
        }
        
        $pelaporOptions = $this->insidenModel->getPelaporOptions();
        $karyawanOptions = $this->insidenModel->getKaryawanOptions();
        
        $data = [
            'title' => 'Insiden & Kecelakaan Kerja - HSE',
            'module' => 'hse',
            'insiden' => $insiden,
            'pelaporOptions' => $pelaporOptions,
            'karyawanOptions' => $karyawanOptions,
            'userRole' => $userRole,
            'userId' => $userId,
        ];
        
        return view('hse/insiden/index', $data);
    }
    
    public function tambahInsiden()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $nomorLaporan = $this->insidenModel->generateNomorLaporan();
        $pelaporOptions = $this->insidenModel->getPelaporOptions();
        $karyawanOptions = $this->insidenModel->getKaryawanOptions();
        
        $data = [
            'title' => 'Tambah Laporan Insiden - HSE',
            'module' => 'hse',
            'nomorLaporan' => $nomorLaporan,
            'pelaporOptions' => $pelaporOptions,
            'karyawanOptions' => $karyawanOptions,
            'userId' => session()->get('userId'),
        ];
        
        return view('hse/insiden/tambah', $data);
    }
    
    public function simpanInsiden()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $validationRules = [
            'nomor_laporan' => 'required',
            'tanggal_kejadian' => 'required',
            'lokasi' => 'required',
            'jenis_insiden' => 'required',
            'deskripsi' => 'required',
        ];
        
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'nomor_laporan' => $this->request->getPost('nomor_laporan'),
            'tanggal_kejadian' => $this->request->getPost('tanggal_kejadian'),
            'lokasi' => $this->request->getPost('lokasi'),
            'jenis_insiden' => $this->request->getPost('jenis_insiden'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'tindakan' => $this->request->getPost('tindakan'),
            'status' => 'dilaporkan',
            'karyawan_id' => $this->request->getPost('karyawan_id'),
            'pelapor_id' => $this->request->getPost('pelapor_id') ?? session()->get('userId'),
        ];
        
        try {
            $this->insidenModel->insert($data);
            return redirect()->to('/hse/insiden')->with('success', 'Laporan insiden berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }
    
    public function detailInsiden($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $insiden = $this->insidenModel->getInsidenWithRelations($id);
        
        if (!$insiden) {
            return redirect()->to('/hse/insiden')->with('error', 'Data insiden tidak ditemukan');
        }
        
        $data = [
            'title' => 'Detail Insiden - HSE',
            'module' => 'hse',
            'insiden' => $insiden,
        ];
        
        return view('hse/insiden/detail', $data);
    }
    
    public function editInsiden($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $insiden = $this->insidenModel->getInsidenWithRelations($id);
        
        if (!$insiden) {
            return redirect()->to('/hse/insiden')->with('error', 'Data insiden tidak ditemukan');
        }
        
        // Cek akses edit
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff' && $insiden['pelapor_id'] != $userId) {
            return redirect()->to('/hse/insiden')->with('error', 'Anda tidak memiliki izin untuk mengedit data ini');
        }
        
        // Cek jika status sudah selesai
        if ($insiden['status'] == 'selesai' && $userRole !== 'manager') {
            return redirect()->to('/hse/insiden/detail/' . $id)->with('error', 'Data yang sudah selesai tidak dapat diubah');
        }
        
        $pelaporOptions = $this->insidenModel->getPelaporOptions();
        $karyawanOptions = $this->insidenModel->getKaryawanOptions();
        
        $data = [
            'title' => 'Edit Laporan Insiden - HSE',
            'module' => 'hse',
            'insiden' => $insiden,
            'pelaporOptions' => $pelaporOptions,
            'karyawanOptions' => $karyawanOptions,
            'userId' => $userId,
        ];
        
        return view('hse/insiden/edit', $data);
    }
    
    /**
     * Update insiden
     */
    public function updateInsiden($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $insiden = $this->insidenModel->find($id);
        
        if (!$insiden) {
            return redirect()->to('/hse/insiden')->with('error', 'Data insiden tidak ditemukan');
        }
        
        // Cek akses edit
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff' && $insiden['pelapor_id'] != $userId) {
            return redirect()->to('/hse/insiden')->with('error', 'Anda tidak memiliki izin untuk mengedit data ini');
        }
        
        // Validasi yang benar - HAPUS is_unique untuk update
        $validationRules = [
            'nomor_laporan' => 'required', // HAPUS: is_unique[hse_insiden.nomor_laporan,id,' . $id . ']
            'tanggal_kejadian' => 'required',
            'lokasi' => 'required|min_length[3]|max_length[100]',
            'jenis_insiden' => 'required|in_list[kecelakaan,hampir_celaka,kerusakan,kebakaran]',
            'deskripsi' => 'required|min_length[10]',
            'pelapor_id' => 'required|numeric'
        ];
        
        // Validation messages kustom
        $validationMessages = [
            'nomor_laporan' => [
                'required' => 'Nomor laporan wajib diisi'
            ],
            'tanggal_kejadian' => [
                'required' => 'Tanggal kejadian wajib diisi'
            ],
            'lokasi' => [
                'required' => 'Lokasi wajib diisi',
                'min_length' => 'Lokasi minimal 3 karakter',
                'max_length' => 'Lokasi maksimal 100 karakter'
            ],
            'deskripsi' => [
                'required' => 'Deskripsi wajib diisi',
                'min_length' => 'Deskripsi minimal 10 karakter'
            ]
        ];
        
        // Set validation rules dan messages
        $this->validate($validationRules, $validationMessages);
        
        if (!$this->validator->run($this->request->getPost())) {
            $errors = $this->validator->getErrors();
            return redirect()->back()->withInput()->with('errors', $errors);
        }
        
        // Handle karyawan_id - set null jika kosong atau 0
        $karyawanId = $this->request->getPost('karyawan_id');
        if (empty($karyawanId) || $karyawanId == '' || $karyawanId == '0') {
            $karyawanId = null;
        }
        
        // Handle pelapor_id
        $pelaporId = $this->request->getPost('pelapor_id');
        if (empty($pelaporId) || !is_numeric($pelaporId)) {
            $pelaporId = $userId; // Default ke user yang login
        }
        
        // Cek jika nomor laporan diubah ke nilai yang sudah ada di record lain
        $newNomorLaporan = $this->request->getPost('nomor_laporan');
        if ($newNomorLaporan != $insiden['nomor_laporan']) {
            // Jika nomor laporan diubah, cek uniqueness
            $existing = $this->insidenModel->where('nomor_laporan', $newNomorLaporan)
                                        ->where('id !=', $id)
                                        ->first();
            if ($existing) {
                return redirect()->back()->withInput()->with('error', 'Nomor laporan "' . $newNomorLaporan . '" sudah digunakan oleh insiden lain');
            }
        }
        
        // Data untuk diupdate
        $data = [
            'nomor_laporan' => $newNomorLaporan,
            'tanggal_kejadian' => $this->request->getPost('tanggal_kejadian'),
            'lokasi' => $this->request->getPost('lokasi'),
            'jenis_insiden' => $this->request->getPost('jenis_insiden'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'tindakan' => $this->request->getPost('tindakan') ?? $insiden['tindakan'],
            'karyawan_id' => $karyawanId,
            'pelapor_id' => $pelaporId,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Gunakan model update
            $result = $this->insidenModel->update($id, $data);
            
            if ($result) {
                return redirect()->to('/hse/insiden/detail/' . $id)->with('success', 'Laporan insiden berhasil diperbarui');
            } else {
                // Cek error database
                $dbError = $this->insidenModel->errors();
                if (!empty($dbError)) {
                    return redirect()->back()->withInput()->with('error', 'Database error: ' . implode(', ', $dbError));
                }
                
                return redirect()->back()->withInput()->with('error', 'Gagal memperbarui laporan: Tidak ada perubahan data');
            }
        } catch (\Exception $e) {
            // Log error detail
            log_message('error', 'Gagal update insiden ID ' . $id . ': ' . $e->getMessage());
            
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus insiden
     */
    public function deleteInsiden($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $insiden = $this->insidenModel->find($id);
        
        if (!$insiden) {
            return redirect()->to('/hse/insiden')->with('error', 'Data insiden tidak ditemukan');
        }
        
        // Cek akses hapus - hanya manager yang boleh hapus
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager') {
            return redirect()->to('/hse/insiden')->with('error', 'Hanya manager yang dapat menghapus data insiden');
        }
        
        try {
            $this->insidenModel->delete($id);
            return redirect()->to('/hse/insiden')->with('success', 'Laporan insiden berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus laporan: ' . $e->getMessage());
        }
    }
    
    /**
     * Konfirmasi hapus insiden
     */
    public function confirmDeleteInsiden($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $insiden = $this->insidenModel->getInsidenWithRelations($id);
        
        if (!$insiden) {
            return redirect()->to('/hse/insiden')->with('error', 'Data insiden tidak ditemukan');
        }
        
        // Cek akses hapus
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager') {
            return redirect()->to('/hse/insiden')->with('error', 'Hanya manager yang dapat menghapus data insiden');
        }
        
        $data = [
            'title' => 'Konfirmasi Hapus Insiden - HSE',
            'module' => 'hse',
            'insiden' => $insiden,
        ];
        
        return view('hse/insiden/confirm_delete', $data);
    }

    /**
     * Update status insiden
     */
    public function updateStatusInsiden($id, $status)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $allowedStatus = ['dilaporkan', 'investigasi', 'selesai'];
        
        if (!in_array($status, $allowedStatus)) {
            return redirect()->back()->with('error', 'Status tidak valid');
        }
        
        // Debug: Tampilkan data yang diterima
        // echo "<pre>";
        // echo "ID: $id\n";
        // echo "Status Parameter: $status\n";
        // echo "Status from Form: " . $this->request->getPost('status') . "\n";
        // echo "Tindakan: " . $this->request->getPost('tindakan') . "\n";
        // echo "</pre>";
        // die();
        
        // Gunakan status dari form, bukan dari URL parameter
        $newStatus = $this->request->getPost('status') ?? $status;
        
        $data = [
            'status' => $newStatus,
            'tindakan' => $this->request->getPost('tindakan') ?? null,
        ];
        
        try {
            $result = $this->insidenModel->update($id, $data);
            
            if ($result) {
                return redirect()->back()->with('success', 'Status insiden berhasil diperbarui');
            } else {
                return redirect()->back()->with('error', 'Gagal memperbarui status');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
    
    // RISIKO METHODS
    public function risiko()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole === 'manager') {
            $risiko = $this->risikoModel->getRisikoWithRelations();
        } elseif ($userRole === 'staff') {
            $risiko = $this->risikoModel->where('penanggung_jawab_id', $userId)->findAll();
        } else {
            $risiko = [];
        }
        
        $divisiOptions = $this->risikoModel->getDivisiOptions();
        $penanggungJawabOptions = $this->risikoModel->getPenanggungJawabOptions();
        
        $data = [
            'title' => 'Risiko & Hazard - HSE',
            'module' => 'hse',
            'risiko' => $risiko,
            'divisiOptions' => $divisiOptions,
            'penanggungJawabOptions' => $penanggungJawabOptions,
            'userRole' => $userRole,
        ];
        
        return view('hse/risiko/index', $data);
    }
    
    public function tambahRisiko()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $kodeRisiko = $this->risikoModel->generateKodeRisiko();
        $divisiOptions = $this->risikoModel->getDivisiOptions();
        $penanggungJawabOptions = $this->risikoModel->getPenanggungJawabOptions();
        
        $data = [
            'title' => 'Tambah Identifikasi Risiko - HSE',
            'module' => 'hse',
            'kodeRisiko' => $kodeRisiko,
            'divisiOptions' => $divisiOptions,
            'penanggungJawabOptions' => $penanggungJawabOptions,
            'userId' => session()->get('userId'),
        ];
        
        return view('hse/risiko/tambah', $data);
    }
    
    public function simpanRisiko()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $validationRules = [
            'kode_risiko' => 'required',
            'deskripsi' => 'required',
            'lokasi' => 'required',
            'tingkat_risiko' => 'required',
        ];
        
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'kode_risiko' => $this->request->getPost('kode_risiko'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'lokasi' => $this->request->getPost('lokasi'),
            'tingkat_risiko' => $this->request->getPost('tingkat_risiko'),
            'tindakan_pengendalian' => $this->request->getPost('tindakan_pengendalian'),
            'status' => 'open',
            'penanggung_jawab_id' => $this->request->getPost('penanggung_jawab_id'),
            'divisi_id' => $this->request->getPost('divisi_id'),
        ];
        
        try {
            $this->risikoModel->insert($data);
            return redirect()->to('/hse/risiko')->with('success', 'Identifikasi risiko berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan risiko: ' . $e->getMessage());
        }
    }
    
    public function updateStatusRisiko($id, $status = null)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $risiko = $this->risikoModel->find($id);
        
        if (!$risiko) {
            return redirect()->to('/hse/risiko')->with('error', 'Data risiko tidak ditemukan');
        }
        
        // Cek akses
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff' && $risiko['penanggung_jawab_id'] != $userId) {
            return redirect()->to('/hse/risiko')->with('error', 'Anda tidak memiliki izin untuk mengupdate data ini');
        }
        
        // Gunakan status dari form jika ada, jika tidak gunakan dari URL
        $newStatus = $this->request->getPost('status') ?? $status;
        
        // Validasi status
        $allowedStatus = ['open', 'closed'];
        if (!in_array($newStatus, $allowedStatus)) {
            return redirect()->back()->with('error', 'Status tidak valid');
        }
        
        $data = [
            'status' => $newStatus,
            'tindakan_pengendalian' => $this->request->getPost('tindakan_pengendalian'),
        ];
        
        try {
            $this->risikoModel->update($id, $data);
            return redirect()->back()->with('success', 'Status risiko berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    public function hapusRisiko($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $risiko = $this->risikoModel->find($id);
        
        if (!$risiko) {
            return redirect()->to('/hse/risiko')->with('error', 'Data risiko tidak ditemukan');
        }
        
        // Cek akses hapus - hanya manager yang boleh hapus
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager') {
            return redirect()->to('/hse/risiko')->with('error', 'Hanya manager yang dapat menghapus data risiko');
        }
        
        try {
            $this->risikoModel->delete($id);
            return redirect()->to('/hse/risiko')->with('success', 'Risiko berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus risiko: ' . $e->getMessage());
        }
    }
    
    // PELATIHAN METHODS
    public function pelatihan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pelatihan = $this->pelatihanModel->getPelatihanWithRelations();
        $instrukturOptions = $this->pelatihanModel->getInstrukturOptions();
        $karyawanOptions = $this->pelatihanModel->getKaryawanForPelatihan();
        $divisiOptions = $this->divisiModel->findAll();
        
        // Parse peserta untuk setiap pelatihan
        foreach ($pelatihan as &$item) {
            $pesertaIds = $this->pelatihanModel->parsePesertaIds($item['peserta']);
            $item['peserta_details'] = $this->pelatihanModel->getPesertaDetails($pesertaIds);
        }
        
        $data = [
            'title' => 'Pelatihan HSE - HSE',
            'module' => 'hse',
            'pelatihan' => $pelatihan,
            'instrukturOptions' => $instrukturOptions,
            'karyawanOptions' => $karyawanOptions,
            'divisiOptions' => $divisiOptions,
        ];
        
        return view('hse/pelatihan/index', $data);
    }
    
    public function tambahPelatihan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $instrukturOptions = $this->pelatihanModel->getInstrukturOptions();
        $karyawanOptions = $this->pelatihanModel->getKaryawanForPelatihan();
        $divisiOptions = $this->divisiModel->findAll();
        
        $data = [
            'title' => 'Tambah Pelatihan HSE - HSE',
            'module' => 'hse',
            'instrukturOptions' => $instrukturOptions,
            'karyawanOptions' => $karyawanOptions,
            'divisiOptions' => $divisiOptions,
        ];
        
        return view('hse/pelatihan/tambah', $data);
    }

    /**
     * Edit pelatihan
     */
    public function editPelatihan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pelatihan = $this->pelatihanModel->getPelatihanWithRelations($id);
        
        if (!$pelatihan) {
            return redirect()->to('/hse/pelatihan')->with('error', 'Data pelatihan tidak ditemukan');
        }
        
        // Cek akses edit
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff') {
            return redirect()->to('/hse/pelatihan')->with('error', 'Anda tidak memiliki izin untuk mengedit data ini');
        }
        
        $instrukturOptions = $this->pelatihanModel->getInstrukturOptions();
        $karyawanOptions = $this->pelatihanModel->getKaryawanForPelatihan();
        $divisiOptions = $this->divisiModel->findAll();
        
        // Parse peserta IDs untuk checkbox
        $pesertaIds = $this->pelatihanModel->parsePesertaIds($pelatihan['peserta']);
        
        $data = [
            'title' => 'Edit Pelatihan HSE - HSE',
            'module' => 'hse',
            'pelatihan' => $pelatihan,
            'instrukturOptions' => $instrukturOptions,
            'karyawanOptions' => $karyawanOptions,
            'divisiOptions' => $divisiOptions,
            'selectedPesertaIds' => $pesertaIds,
        ];
        
        return view('hse/pelatihan/edit', $data);
    }

    /**
     * Update pelatihan
     */
    public function updatePelatihan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pelatihan = $this->pelatihanModel->find($id);
        
        if (!$pelatihan) {
            return redirect()->to('/hse/pelatihan')->with('error', 'Data pelatihan tidak ditemukan');
        }
        
        // Cek akses edit
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff') {
            return redirect()->to('/hse/pelatihan')->with('error', 'Anda tidak memiliki izin untuk mengedit data ini');
        }
        
        $validationRules = [
            'judul_pelatihan' => 'required',
            'tanggal_pelatihan' => 'required',
            'peserta' => 'required',
            'materi' => 'required',
        ];
        
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $peserta = is_array($this->request->getPost('peserta')) 
            ? implode(',', $this->request->getPost('peserta'))
            : $this->request->getPost('peserta');
        
        $data = [
            'judul_pelatihan' => $this->request->getPost('judul_pelatihan'),
            'tanggal_pelatihan' => $this->request->getPost('tanggal_pelatihan'),
            'peserta' => $peserta,
            'materi' => $this->request->getPost('materi'),
            'hasil' => $this->request->getPost('hasil'),
            'instruktur_id' => $this->request->getPost('instruktur_id'),
            'divisi_target' => $this->request->getPost('divisi_target'),
        ];
        
        try {
            $this->pelatihanModel->update($id, $data);
            return redirect()->to('/hse/pelatihan')->with('success', 'Pelatihan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pelatihan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus pelatihan
     */
    public function hapusPelatihan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pelatihan = $this->pelatihanModel->find($id);
        
        if (!$pelatihan) {
            return redirect()->to('/hse/pelatihan')->with('error', 'Data pelatihan tidak ditemukan');
        }
        
        // Cek akses hapus - hanya manager dan staff yang boleh hapus
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff') {
            return redirect()->to('/hse/pelatihan')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
        }
        
        try {
            $this->pelatihanModel->delete($id);
            return redirect()->to('/hse/pelatihan')->with('success', 'Pelatihan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pelatihan: ' . $e->getMessage());
        }
    }

    /**
     * Konfirmasi hapus pelatihan
     */
    public function konfirmasiHapusPelatihan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pelatihan = $this->pelatihanModel->getPelatihanWithRelations($id);
        
        if (!$pelatihan) {
            return redirect()->to('/hse/pelatihan')->with('error', 'Data pelatihan tidak ditemukan');
        }
        
        // Cek akses hapus
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff') {
            return redirect()->to('/hse/pelatihan')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
        }
        
        // Parse peserta untuk detail
        $pesertaIds = $this->pelatihanModel->parsePesertaIds($pelatihan['peserta']);
        $pelatihan['peserta_details'] = $this->pelatihanModel->getPesertaDetails($pesertaIds);
        
        $data = [
            'title' => 'Konfirmasi Hapus Pelatihan - HSE',
            'module' => 'hse',
            'pelatihan' => $pelatihan,
        ];
        
        return view('hse/pelatihan/konfirmasi_hapus', $data);
    }
    
    public function simpanPelatihan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $validationRules = [
            'judul_pelatihan' => 'required',
            'tanggal_pelatihan' => 'required',
            'peserta' => 'required',
            'materi' => 'required',
        ];
        
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $peserta = is_array($this->request->getPost('peserta')) 
            ? implode(',', $this->request->getPost('peserta'))
            : $this->request->getPost('peserta');
        
        $data = [
            'judul_pelatihan' => $this->request->getPost('judul_pelatihan'),
            'tanggal_pelatihan' => $this->request->getPost('tanggal_pelatihan'),
            'peserta' => $peserta,
            'materi' => $this->request->getPost('materi'),
            'hasil' => $this->request->getPost('hasil'),
            'instruktur_id' => $this->request->getPost('instruktur_id'),
            'divisi_target' => $this->request->getPost('divisi_target'),
        ];
        
        try {
            $this->pelatihanModel->insert($data);
            return redirect()->to('/hse/pelatihan')->with('success', 'Pelatihan berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pelatihan: ' . $e->getMessage());
        }
    }
    
    /**
     * Update method lingkungan() untuk mendukung filter
     */
    public function lingkungan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Get filter parameters
        $filters = [
            'parameter' => $this->request->getGet('parameter'),
            'status' => $this->request->getGet('status'),
            'lokasi_id' => $this->request->getGet('lokasi_id'),
            'pengukur_id' => $this->request->getGet('pengukur_id'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
        ];
        
        $lingkungan = $this->lingkunganModel->getLingkunganWithFilter($filters);
        $pengukurOptions = $this->lingkunganModel->getPengukurOptions();
        $lokasiOptions = $this->lingkunganModel->getLokasiOptions();
        $parameterOptions = $this->lingkunganModel->getParameterOptionsWithDefault();
        
        // Hitung stats dengan filter
        $lingkunganStats = $this->lingkunganModel->getLingkunganStats(
            $filters['start_date'], 
            $filters['end_date']
        );
        
        $data = [
            'title' => 'Pemantauan Lingkungan - HSE',
            'module' => 'hse',
            'lingkungan' => $lingkungan,
            'pengukurOptions' => $pengukurOptions,
            'lokasiOptions' => $lokasiOptions,
            'parameterOptions' => $parameterOptions,
            'lingkunganStats' => $lingkunganStats,
            'filters' => $filters,
        ];
        
        return view('hse/lingkungan/index', $data);
    }
    
    public function tambahLingkungan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $pengukurOptions = $this->lingkunganModel->getPengukurOptions();
        $lokasiOptions = $this->lingkunganModel->getLokasiOptions();
        $parameterOptions = $this->lingkunganModel->getParameterOptionsWithDefault();
        
        $data = [
            'title' => 'Tambah Data Pemantauan Lingkungan - HSE',
            'module' => 'hse',
            'pengukurOptions' => $pengukurOptions,
            'lokasiOptions' => $lokasiOptions,
            'parameterOptions' => $parameterOptions,
            'userId' => session()->get('userId'),
        ];
        
        return view('hse/lingkungan/tambah', $data);
    }
    
    public function simpanLingkungan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $validationRules = [
            'tanggal_pantau' => 'required',
            'parameter' => 'required',
            'nilai_ukur' => 'required|numeric',
            'satuan' => 'required',
            'batas_normal' => 'required|numeric',
        ];
        
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $nilaiUkur = $this->request->getPost('nilai_ukur');
        $batasNormal = $this->request->getPost('batas_normal');
        $status = ($nilaiUkur <= $batasNormal) ? 'normal' : 'melebihi_batas';
        $currentDateTime = date('Y-m-d H:i:s');

        $data = [
            'tanggal_pantau' => $this->request->getPost('tanggal_pantau'),
            'parameter' => $this->request->getPost('parameter'),
            'nilai_ukur' => $nilaiUkur,
            'satuan' => $this->request->getPost('satuan'),
            'batas_normal' => $batasNormal,
            'status' => $status,
            'pengukur_id' => $this->request->getPost('pengukur_id') ?? session()->get('userId'),
            'lokasi_id' => $this->request->getPost('lokasi_id'),
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime,
        ];
        
        try {
            $this->lingkunganModel->insert($data);
            return redirect()->to('/hse/lingkungan')->with('success', 'Data pemantauan berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
    
    public function grafikLingkungan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $parameter = $this->request->getGet('parameter');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        $builder = $this->lingkunganModel;
        
        if ($parameter) {
            $builder->where('parameter', $parameter);
        }
        
        if ($startDate && $endDate) {
            $builder->where('tanggal_pantau >=', $startDate)
                   ->where('tanggal_pantau <=', $endDate);
        } else {
            // Default 30 hari terakhir
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d');
            $builder->where('tanggal_pantau >=', $startDate)
                   ->where('tanggal_pantau <=', $endDate);
        }
        
        $data = $builder->orderBy('tanggal_pantau', 'ASC')->findAll();
        
        $parameterOptions = $this->lingkunganModel->getParameterOptionsWithDefault();
        
        $result = [
            'labels' => [],
            'values' => [],
            'batas' => [],
            'status' => [],
        ];
        
        foreach ($data as $row) {
            $result['labels'][] = date('d M', strtotime($row['tanggal_pantau']));
            $result['values'][] = (float) $row['nilai_ukur'];
            $result['batas'][] = (float) $row['batas_normal'];
            $result['status'][] = $row['status'];
        }
        
        $data = [
            'title' => 'Grafik Pemantauan Lingkungan - HSE',
            'module' => 'hse',
            'chartData' => $result,
            'parameterOptions' => $parameterOptions,
            'selectedParameter' => $parameter,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        
        return view('hse/lingkungan/grafik', $data);
    }

    /**
     * Detail data pemantauan lingkungan
     */
    public function detailLingkungan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $lingkungan = $this->lingkunganModel->getLingkunganWithRelations($id);
        
        if (!$lingkungan) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Data pemantauan tidak ditemukan');
        }
        
        // Get latest 5 measurements for same parameter
        $latestMeasurements = $this->lingkunganModel->getLatestMeasurements(5, $lingkungan['parameter']);
        
        $data = [
            'title' => 'Detail Pemantauan Lingkungan - HSE',
            'module' => 'hse',
            'lingkungan' => $lingkungan,
            'latestMeasurements' => $latestMeasurements,
        ];
        
        return view('hse/lingkungan/detail', $data);
    }

    /**
     * Edit data pemantauan lingkungan
     */
    public function editLingkungan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $lingkungan = $this->lingkunganModel->getLingkunganWithRelations($id);
        
        if (!$lingkungan) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Data pemantauan tidak ditemukan');
        }
        
        // Cek akses edit
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff' && $lingkungan['pengukur_id'] != $userId) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Anda tidak memiliki izin untuk mengedit data ini');
        }
        
        $pengukurOptions = $this->lingkunganModel->getPengukurOptions();
        $lokasiOptions = $this->lingkunganModel->getLokasiOptions();
        $parameterOptions = $this->lingkunganModel->getParameterOptionsWithDefault();
        
        $data = [
            'title' => 'Edit Data Pemantauan Lingkungan - HSE',
            'module' => 'hse',
            'lingkungan' => $lingkungan,
            'pengukurOptions' => $pengukurOptions,
            'lokasiOptions' => $lokasiOptions,
            'parameterOptions' => $parameterOptions,
            'userId' => $userId,
            'userRole' => $userRole,
        ];
        
        return view('hse/lingkungan/edit', $data);
    }

    /**
     * Update data pemantauan lingkungan
     */
    public function updateLingkungan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $lingkungan = $this->lingkunganModel->find($id);
        
        if (!$lingkungan) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Data pemantauan tidak ditemukan');
        }
        
        // Cek akses edit
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff' && $lingkungan['pengukur_id'] != $userId) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Anda tidak memiliki izin untuk mengedit data ini');
        }
        
        $validationRules = [
            'tanggal_pantau' => 'required',
            'parameter' => 'required',
            'nilai_ukur' => 'required|numeric',
            'satuan' => 'required',
            'batas_normal' => 'required|numeric',
            'lokasi_id' => 'required|numeric',
        ];
        
        // Cek duplikasi data
        $tanggal = $this->request->getPost('tanggal_pantau');
        $parameter = $this->request->getPost('parameter');
        $lokasiId = $this->request->getPost('lokasi_id');
        
        if ($this->lingkunganModel->checkDuplicate($tanggal, $parameter, $lokasiId, $id)) {
            return redirect()->back()->withInput()->with('error', 'Data dengan parameter, lokasi, dan tanggal yang sama sudah ada');
        }
        
        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $nilaiUkur = $this->request->getPost('nilai_ukur');
        $batasNormal = $this->request->getPost('batas_normal');
        $status = ($nilaiUkur <= $batasNormal) ? 'normal' : 'melebihi_batas';
        
        $data = [
            'tanggal_pantau' => $tanggal,
            'parameter' => $parameter,
            'nilai_ukur' => $nilaiUkur,
            'satuan' => $this->request->getPost('satuan'),
            'batas_normal' => $batasNormal,
            'status' => $status,
            'pengukur_id' => $this->request->getPost('pengukur_id') ?? $lingkungan['pengukur_id'],
            'lokasi_id' => $lokasiId,
            'catatan_perubahan' => $this->request->getPost('catatan_perubahan'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        try {
            $this->lingkunganModel->update($id, $data);
            return redirect()->to('/hse/lingkungan/detail/' . $id)->with('success', 'Data pemantauan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Konfirmasi hapus data pemantauan lingkungan
     */
    public function confirmHapusLingkungan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $lingkungan = $this->lingkunganModel->getLingkunganWithRelations($id);
        
        if (!$lingkungan) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Data pemantauan tidak ditemukan');
        }
        
        // Cek akses hapus
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff' && $lingkungan['pengukur_id'] != $userId) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
        }
        
        $data = [
            'title' => 'Konfirmasi Hapus Data Pemantauan - HSE',
            'module' => 'hse',
            'lingkungan' => $lingkungan,
        ];
        
        return view('hse/lingkungan/confirm_hapus', $data);
    }

    /**
     * Hapus data pemantauan lingkungan
     */
    public function hapusLingkungan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $lingkungan = $this->lingkunganModel->find($id);
        
        if (!$lingkungan) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Data pemantauan tidak ditemukan');
        }
        
        // Cek akses hapus
        $userId = session()->get('userId');
        $userRole = session()->get('role');
        
        if ($userRole !== 'manager' && $userRole !== 'staff' && $lingkungan['pengukur_id'] != $userId) {
            return redirect()->to('/hse/lingkungan')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
        }
        
        // Get alasan hapus dari form
        $alasanHapus = $this->request->getPost('alasan_hapus');
        $userId = session()->get('userId');
        
        // OPTION 1: Soft delete (tandai sebagai deleted, tidak hapus fisik)
        $data = [
            'alasan_hapus' => $alasanHapus,
            'dihapus_oleh' => $userId,
            'dihapus_pada' => date('Y-m-d H:i:s'),
            'is_deleted' => 1 // tambah field is_deleted TINYINT(1) DEFAULT 0
        ];
        
        try {
            // Soft delete
            $this->lingkunganModel->update($id, $data);
            
            // OPTION 2: Hard delete (hapus fisik dari database)
            // $this->lingkunganModel->delete($id);
            
            // Log aktivitas
            log_message('info', "Data lingkungan ID {$id} dihapus oleh user {$userId}. Alasan: {$alasanHapus}");
            
            return redirect()->to('/hse/lingkungan')->with('success', 'Data pemantauan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Data lingkungan yang sudah dihapus (recovery)
     */
    public function dataTerhapus()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Hanya manager yang bisa melihat data terhapus
        $userRole = session()->get('role');
        if ($userRole !== 'manager') {
            return redirect()->to('/hse/lingkungan')->with('error', 'Hanya manager yang dapat melihat data terhapus');
        }
        
        // Ambil data yang sudah dihapus
        $lingkunganTerhapus = $this->lingkunganModel->getDeletedData();
        
        $data = [
            'title' => 'Data Lingkungan yang Dihapus - HSE',
            'module' => 'hse',
            'lingkungan' => $lingkunganTerhapus,
            'userRole' => $userRole,
        ];
        
        return view('hse/lingkungan/terhapus', $data);
    }

    /**
     * Restore data yang dihapus
     */
    public function restoreLingkungan($id)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Hanya manager yang bisa restore
        $userRole = session()->get('role');
        if ($userRole !== 'manager') {
            return redirect()->to('/hse/lingkungan')->with('error', 'Hanya manager yang dapat merestore data');
        }
        
        $data = [
            'alasan_hapus' => null,
            'dihapus_oleh' => null,
            'dihapus_pada' => null
        ];
        
        try {
            $this->lingkunganModel->update($id, $data);
            return redirect()->to('/hse/lingkungan/terhapus')->with('success', 'Data berhasil direstore');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal merestore data: ' . $e->getMessage());
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