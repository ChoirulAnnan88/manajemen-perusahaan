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
    
    public function updateStatusInsiden($id, $status)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $allowedStatus = ['dilaporkan', 'investigasi', 'selesai'];
        
        if (!in_array($status, $allowedStatus)) {
            return redirect()->back()->with('error', 'Status tidak valid');
        }
        
        $data = [
            'status' => $status,
            'tindakan' => $this->request->getPost('tindakan') ?? null,
        ];
        
        try {
            $this->insidenModel->update($id, $data);
            return redirect()->back()->with('success', 'Status insiden berhasil diperbarui');
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
    
    public function updateStatusRisiko($id, $status)
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $allowedStatus = ['open', 'closed'];
        
        if (!in_array($status, $allowedStatus)) {
            return redirect()->back()->with('error', 'Status tidak valid');
        }
        
        $data = ['status' => $status];
        
        try {
            $this->risikoModel->update($id, $data);
            return redirect()->back()->with('success', 'Status risiko berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
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
    
    // LINGKUNGAN METHODS
    public function lingkungan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $lingkungan = $this->lingkunganModel->getLingkunganWithRelations();
        $pengukurOptions = $this->lingkunganModel->getPengukurOptions();
        $lokasiOptions = $this->lingkunganModel->getLokasiOptions();
        $parameterOptions = $this->lingkunganModel->getParameterOptions();
        
        $data = [
            'title' => 'Pemantauan Lingkungan - HSE',
            'module' => 'hse',
            'lingkungan' => $lingkungan,
            'pengukurOptions' => $pengukurOptions,
            'lokasiOptions' => $lokasiOptions,
            'parameterOptions' => $parameterOptions,
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
        $parameterOptions = $this->lingkunganModel->getParameterOptions();
        
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
        
        $data = [
            'tanggal_pantau' => $this->request->getPost('tanggal_pantau'),
            'parameter' => $this->request->getPost('parameter'),
            'nilai_ukur' => $nilaiUkur,
            'satuan' => $this->request->getPost('satuan'),
            'batas_normal' => $batasNormal,
            'status' => $status,
            'pengukur_id' => $this->request->getPost('pengukur_id') ?? session()->get('userId'),
            'lokasi_id' => $this->request->getPost('lokasi_id'),
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
        
        $parameterOptions = $this->lingkunganModel->getParameterOptions();
        
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