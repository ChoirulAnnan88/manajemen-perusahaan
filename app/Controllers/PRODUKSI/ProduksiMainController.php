<?php
namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;
use App\Models\PRODUKSI\ProduksiModel;
use App\Models\PRODUKSI\AlatdanBahanModel;
use App\Models\PRODUKSI\OperatorModel;

class ProduksiMainController extends BaseController
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

        // Get data for dashboard
        $today = date('Y-m-d');
        
        // Jika model belum ada, gunakan database query langsung
        $db = \Config\Database::connect();
        
        // Total Produksi
        $total_produksi = $db->table('produksi_hasil')->countAllResults();
        
        // Total Alat
        $total_alat = $db->table('produksi_alat')->countAllResults();
        
        // Total Operator
        $total_operator = $db->table('produksi_operator')->countAllResults();
        
        // Produksi Hari Ini
        $produksi_hari_ini = $db->table('produksi_hasil')
            ->where('tanggal_produksi', $today)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Dashboard Produksi',
            'module' => 'produksi',
            'total_produksi' => $total_produksi,
            'total_alat' => $total_alat,
            'total_operator' => $total_operator,
            'produksi_hari_ini' => $produksi_hari_ini
        ];
        
        return view('produksi/dashboard', $data);
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