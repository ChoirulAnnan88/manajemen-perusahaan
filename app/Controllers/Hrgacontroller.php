<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HrgaKaryawanModel;
use App\Models\HrgaAbsensiModel;
use App\Models\HrgaPenggajianModel;
use App\Models\HrgaPenilaianModel;
use App\Models\HrgaInventarisModel;
use App\Models\HrgaPerawatanModel;
use App\Models\HrgaPerizinanModel;
use App\Models\DivisiModel;

class HrgaController extends BaseController
{
    protected $karyawanModel;
    protected $absensiModel;
    protected $penggajianModel;
    protected $penilaianModel;
    protected $inventarisModel;
    protected $perawatanModel;
    protected $perizinanModel;
    protected $divisiModel;

    public function __construct()
    {
        $this->karyawanModel = new HrgaKaryawanModel();
        $this->absensiModel = new HrgaAbsensiModel();
        $this->penggajianModel = new HrgaPenggajianModel();
        $this->penilaianModel = new HrgaPenilaianModel();
        $this->inventarisModel = new HrgaInventarisModel();
        $this->perawatanModel = new HrgaPerawatanModel();
        $this->perizinanModel = new HrgaPerizinanModel();
        $this->divisiModel = new DivisiModel();
    }

    // ==================== ROLE-BASED ACCESS CONTROL ====================
    private function checkUserRoleAccess($action)
    {
        $userRole = session()->get('role');
        
        // Manager bisa akses semua
        if ($userRole === 'manager') {
            return true;
        }
        
        // Staff/Operator hanya bisa akses data sendiri untuk fitur tertentu
        if (in_array($userRole, ['staff', 'operator'])) {
            $restrictedActions = [
                'tambah_karyawan', 'tambah_absensi', 'generate_penggajian', 
                'tambah_penilaian', 'tambah_inventaris', 'tambah_perawatan',
                'edit_karyawan', 'hapus_karyawan'
            ];
            
            // Staff/Operator tidak bisa akses action yang restricted
            if (in_array($action, $restrictedActions)) {
                return false;
            }
        }
        
        return true;
    }

    private function isManager()
    {
        return session()->get('role') === 'manager';
    }

    private function isStaffOrOperator()
    {
        $role = session()->get('role');
        return in_array($role, ['staff', 'operator']);
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

        // Manager bisa akses semua
        if ($userRole === 'manager') {
            return true;
        }

        // Staff dan Operator hanya divisi sendiri
        return isset($divisionMap[$division]) && $userDivisi == $divisionMap[$division];
    }

    public function index()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi HRGA');
        }

        // Dashboard statistics
        $totalKaryawan = $this->karyawanModel->countAll();
        $absensiHariIni = $this->absensiModel->where('tanggal', date('Y-m-d'))->countAllResults();
        
        $bulanIni = date('Y-m');
        $penggajianBulanIni = $this->penggajianModel->like('bulan_tahun', $bulanIni)->countAllResults();
        
        $perizinanPending = $this->perizinanModel->where('status', 'pending')->countAllResults();

        $data = [
            'title' => 'Dashboard HRGA',
            'module' => 'hrga',
            'stats' => [
                'total_karyawan' => $totalKaryawan,
                'absensi_hari_ini' => $absensiHariIni,
                'penggajian_bulan_ini' => $penggajianBulanIni,
                'perizinan_pending' => $perizinanPending
            ],
            'isManager' => $this->isManager(),
            'isStaffOrOperator' => $this->isStaffOrOperator()
        ];
        
        return view('hrga/dashboard', $data);
    }

    // ==================== KARYAWAN MANAGEMENT ====================
    public function karyawan()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Data Karyawan - HRGA',
            'module' => 'hrga',
            'karyawan' => $this->karyawanModel->getKaryawanWithDivisi(),
            'divisi' => $this->divisiModel->findAll(),
            'isManager' => $this->isManager(),
            'isStaffOrOperator' => $this->isStaffOrOperator()
        ];
        
        return view('hrga/karyawan', $data);
    }

    public function tambahKaryawan()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role access
        if (!$this->checkUserRoleAccess('tambah_karyawan')) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Anda tidak memiliki akses untuk menambah karyawan');
        }

        $data = [
            'title' => 'Tambah Karyawan - HRGA',
            'module' => 'hrga',
            'divisi' => $this->divisiModel->findAll(),
            'validation' => \Config\Services::validation(),
            'isManager' => $this->isManager()
        ];
        
        return view('hrga/karyawan_tambah', $data);
    }

    public function simpanKaryawan()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role access
        if (!$this->checkUserRoleAccess('tambah_karyawan')) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Anda tidak memiliki akses untuk menambah karyawan');
        }

        $rules = [
            'nip' => 'required|is_unique[hrga_karyawan.nip]',
            'nama_lengkap' => 'required',
            'divisi_id' => 'required',
            'jabatan' => 'required',
            'tanggal_masuk' => 'required',
            'status_karyawan' => 'required',
            'gaji_pokok' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'nip' => $this->request->getPost('nip'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'divisi_id' => $this->request->getPost('divisi_id'),
                'jabatan' => $this->request->getPost('jabatan'),
                'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
                'status_karyawan' => $this->request->getPost('status_karyawan'),
                'gaji_pokok' => $this->request->getPost('gaji_pokok')
            ];

            if ($this->karyawanModel->save($data)) {
                return redirect()->to('/hrga/karyawan')->with('success', 'Data karyawan berhasil disimpan');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data karyawan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function editKaryawan($id)
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role access
        if (!$this->checkUserRoleAccess('edit_karyawan')) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Anda tidak memiliki akses untuk mengedit karyawan');
        }

        $data = [
            'title' => 'Edit Karyawan - HRGA',
            'module' => 'hrga',
            'karyawan' => $this->karyawanModel->find($id),
            'divisi' => $this->divisiModel->findAll(),
            'validation' => \Config\Services::validation(),
            'isManager' => $this->isManager()
        ];
        
        return view('hrga/karyawan_edit', $data);
    }

    public function updateKaryawan($id)
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role access
        if (!$this->checkUserRoleAccess('edit_karyawan')) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Anda tidak memiliki akses untuk mengedit karyawan');
        }

        $rules = [
            'nama_lengkap' => 'required',
            'divisi_id' => 'required',
            'jabatan' => 'required'
        ];

        $karyawan = $this->karyawanModel->find($id);
        if ($karyawan['nip'] != $this->request->getPost('nip')) {
            $rules['nip'] = 'required|is_unique[hrga_karyawan.nip]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $data = [
                'nip' => $this->request->getPost('nip'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'divisi_id' => $this->request->getPost('divisi_id'),
                'jabatan' => $this->request->getPost('jabatan'),
                'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
                'status_karyawan' => $this->request->getPost('status_karyawan'),
                'gaji_pokok' => $this->request->getPost('gaji_pokok')
            ];

            if ($this->karyawanModel->update($id, $data)) {
                return redirect()->to('/hrga/karyawan')->with('success', 'Data karyawan berhasil diupdate');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal mengupdate data karyawan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function detailKaryawan($id)
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Detail Karyawan - HRGA',
            'module' => 'hrga',
            'karyawan' => $this->karyawanModel->getKaryawanWithDivisiById($id),
            'isManager' => $this->isManager()
        ];
        
        return view('hrga/karyawan_detail', $data);
    }

    public function hapusKaryawan($id)
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role access
        if (!$this->checkUserRoleAccess('hapus_karyawan')) {
            return redirect()->to('/hrga/karyawan')->with('error', 'Anda tidak memiliki akses untuk menghapus karyawan');
        }

        try {
            if ($this->karyawanModel->delete($id)) {
                return redirect()->to('/hrga/karyawan')->with('success', 'Karyawan berhasil dihapus');
            } else {
                return redirect()->back()->with('error', 'Gagal menghapus karyawan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==================== ABSENSI MANAGEMENT ====================
    public function absensi()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Absensi & Waktu Kerja - HRGA',
            'module' => 'hrga',
            'absensi' => $this->absensiModel->getAbsensiHariIniWithKaryawan(),
            'karyawan' => $this->karyawanModel->findAll(),
            'isManager' => $this->isManager(),
            'isStaffOrOperator' => $this->isStaffOrOperator()
        ];
        
        return view('hrga/absensi', $data);
    }

    public function simpanAbsensi()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role untuk staff/operator
        if (!$this->checkUserRoleAccess('tambah_absensi')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah absensi');
        }

        try {
            $data = [
                'karyawan_id' => $this->request->getPost('karyawan_id'),
                'tanggal' => $this->request->getPost('tanggal'),
                'jam_masuk' => $this->request->getPost('jam_masuk'),
                'jam_pulang' => $this->request->getPost('jam_pulang'),
                'status' => $this->request->getPost('status'),
                'keterangan' => $this->request->getPost('keterangan'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Gunakan insert() langsung untuk menghindari error updated_at
            if ($this->absensiModel->insert($data)) {
                return redirect()->to('/hrga/absensi')->with('success', 'Data absensi berhasil disimpan');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data absensi');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function riwayatAbsensi()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title' => 'Riwayat Absensi - HRGA',
            'module' => 'hrga',
            'absensi' => $this->absensiModel->getRekapAbsensi($bulan, $tahun),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'isManager' => $this->isManager()
        ];
        
        return view('hrga/absensi_riwayat', $data);
    }

    // ==================== PENGAJIAN MANAGEMENT ====================
    public function penggajian()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title' => 'Penggajian - HRGA',
            'module' => 'hrga',
            'penggajian' => $this->penggajianModel->getPenggajianWithKaryawan($bulan, $tahun),
            'karyawan' => $this->karyawanModel->getKaryawanWithDivisi(),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'isManager' => $this->isManager(),
            'isStaffOrOperator' => $this->isStaffOrOperator()
        ];
        
        return view('hrga/penggajian', $data);
    }

    public function generatePenggajian()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role untuk staff/operator
        if (!$this->checkUserRoleAccess('generate_penggajian')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk generate penggajian');
        }

        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title' => 'Generate Penggajian - HRGA',
            'module' => 'hrga',
            'karyawan' => $this->karyawanModel->getKaryawanWithDivisi(),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'isManager' => $this->isManager()
        ];
        
        return view('hrga/penggajian_generate', $data);
    }

    public function prosesPenggajian()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role untuk staff/operator
        if (!$this->checkUserRoleAccess('generate_penggajian')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk generate penggajian');
        }

        try {
            $karyawanList = $this->request->getPost('karyawan');
            $bulan = $this->request->getPost('bulan');
            $tahun = $this->request->getPost('tahun');
            $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01';

            foreach ($karyawanList as $karyawanId) {
                $karyawan = $this->karyawanModel->find($karyawanId);
                
                $tunjangan = $this->request->getPost('tunjangan')[$karyawanId] ?? 0;
                $potongan = $this->request->getPost('potongan')[$karyawanId] ?? 0;
                $totalGaji = $karyawan['gaji_pokok'] + $tunjangan - $potongan;

                $data = [
                    'karyawan_id' => $karyawanId,
                    'bulan_tahun' => $periode,
                    'gaji_pokok' => $karyawan['gaji_pokok'],
                    'tunjangan' => $tunjangan,
                    'potongan' => $potongan,
                    'total_gaji' => $totalGaji,
                    'status' => 'draft',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Gunakan insert() langsung untuk menghindari error updated_at
                $this->penggajianModel->insert($data);
            }

            return redirect()->to('/hrga/penggajian')->with('success', 'Penggajian berhasil digenerate');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function slipGaji($id)
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Slip Gaji - HRGA',
            'module' => 'hrga',
            'penggajian' => $this->penggajianModel->find($id),
            'isManager' => $this->isManager()
        ];
        
        return view('hrga/penggajian_slip', $data);
    }

    // ==================== PENILAIAN MANAGEMENT ====================
    public function penilaian()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Penilaian Kinerja - HRGA',
            'module' => 'hrga',
            'penilaian' => $this->penilaianModel->getPenilaianWithKaryawan(),
            'karyawan' => $this->karyawanModel->findAll(),
            'isManager' => $this->isManager(),
            'isStaffOrOperator' => $this->isStaffOrOperator()
        ];
        
        return view('hrga/penilaian', $data);
    }

    public function simpanPenilaian()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role untuk staff/operator
        if (!$this->checkUserRoleAccess('tambah_penilaian')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah penilaian');
        }

        try {
            $produktivitas = $this->request->getPost('nilai_produktivitas');
            $kedisiplinan = $this->request->getPost('nilai_kedisiplinan');
            $kerjasama = $this->request->getPost('nilai_kerjasama');
            
            $nilaiTotal = $this->penilaianModel->calculateNilaiTotal($produktivitas, $kedisiplinan, $kerjasama);

            $data = [
                'karyawan_id' => $this->request->getPost('karyawan_id'),
                'periode' => $this->request->getPost('periode'),
                'nilai_produktivitas' => $produktivitas,
                'nilai_kedisiplinan' => $kedisiplinan,
                'nilai_kerjasama' => $kerjasama,
                'nilai_total' => $nilaiTotal,
                'catatan' => $this->request->getPost('catatan'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Gunakan insert() langsung untuk menghindari error updated_at
            if ($this->penilaianModel->insert($data)) {
                return redirect()->to('/hrga/penilaian')->with('success', 'Penilaian berhasil disimpan');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan penilaian');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==================== INVENTARIS MANAGEMENT ====================
    public function inventaris()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Inventaris General - HRGA',
            'module' => 'hrga',
            'inventaris' => $this->inventarisModel->findAll(),
            'isManager' => $this->isManager(),
            'isStaffOrOperator' => $this->isStaffOrOperator()
        ];
        
        return view('hrga/inventaris', $data);
    }

    public function simpanInventaris()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role untuk staff/operator
        if (!$this->checkUserRoleAccess('tambah_inventaris')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah inventaris');
        }

        try {
            $kodeInventaris = $this->inventarisModel->generateKodeInventaris();

            $data = [
                'kode_inventaris' => $kodeInventaris,
                'nama_barang' => $this->request->getPost('nama_barang'),
                'kategori' => $this->request->getPost('kategori'),
                'jumlah' => $this->request->getPost('jumlah'),
                'kondisi' => $this->request->getPost('kondisi'),
                'lokasi' => $this->request->getPost('lokasi'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Gunakan insert() langsung untuk menghindari error updated_at
            if ($this->inventarisModel->insert($data)) {
                return redirect()->to('/hrga/inventaris')->with('success', 'Inventaris berhasil disimpan');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan inventaris');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==================== PERAWATAN MANAGEMENT ====================
    public function perawatan()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Perawatan Gedung - HRGA',
            'module' => 'hrga',
            'perawatan' => $this->perawatanModel->findAll(),
            'isManager' => $this->isManager(),
            'isStaffOrOperator' => $this->isStaffOrOperator()
        ];
        
        return view('hrga/perawatan', $data);
    }

    public function simpanPerawatan()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role untuk staff/operator
        if (!$this->checkUserRoleAccess('tambah_perawatan')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah perawatan');
        }

        try {
            $kodePerawatan = $this->perawatanModel->generateKodePerawatan();

            $data = [
                'kode_perawatan' => $kodePerawatan,
                'deskripsi' => $this->request->getPost('deskripsi'),
                'lokasi' => $this->request->getPost('lokasi'),
                'tanggal_perawatan' => $this->request->getPost('tanggal_perawatan'),
                'biaya' => $this->request->getPost('biaya'),
                'status' => $this->request->getPost('status'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Gunakan insert() langsung untuk menghindari error updated_at
            if ($this->perawatanModel->insert($data)) {
                return redirect()->to('/hrga/perawatan')->with('success', 'Data perawatan berhasil disimpan');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data perawatan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==================== PERIZINAN MANAGEMENT ====================
    public function perizinan()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Perizinan - HRGA',
            'module' => 'hrga',
            'perizinan' => $this->perizinanModel->getPerizinanWithKaryawan(),
            'karyawan' => $this->karyawanModel->findAll(),
            'isManager' => $this->isManager(),
            'isStaffOrOperator' => $this->isStaffOrOperator()
        ];
        
        return view('hrga/perizinan', $data);
    }

    public function ajukanPerizinan()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        try {
            $data = [
                'karyawan_id' => $this->request->getPost('karyawan_id'),
                'jenis_izin' => $this->request->getPost('jenis_izin'),
                'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
                'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
                'alasan' => $this->request->getPost('alasan'),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Gunakan insert() langsung untuk menghindari error updated_at
            if ($this->perizinanModel->insert($data)) {
                return redirect()->to('/hrga/perizinan')->with('success', 'Perizinan berhasil diajukan');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal mengajukan perizinan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approvePerizinan($id)
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role untuk staff/operator
        if ($this->isStaffOrOperator()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menyetujui perizinan');
        }

        try {
            if ($this->perizinanModel->approvePerizinan($id)) {
                return redirect()->to('/hrga/perizinan')->with('success', 'Perizinan berhasil disetujui');
            } else {
                return redirect()->back()->with('error', 'Gagal menyetujui perizinan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function rejectPerizinan($id)
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        // Cek role untuk staff/operator
        if ($this->isStaffOrOperator()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menolak perizinan');
        }

        try {
            if ($this->perizinanModel->rejectPerizinan($id)) {
                return redirect()->to('/hrga/perizinan')->with('success', 'Perizinan berhasil ditolak');
            } else {
                return redirect()->back()->with('error', 'Gagal menolak perizinan');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}