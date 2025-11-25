<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class HrgaController extends BaseController
{
    public function index()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi HRGA');
        }

        $data = [
            'title' => 'Dashboard HRGA',
            'module' => 'hrga'
        ];
        
        return view('hrga/dashboard', $data);
    }

    public function karyawan()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Data Karyawan - HRGA',
            'module' => 'hrga'
        ];
        
        return view('hrga/karyawan', $data);
    }

    public function absensi()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Absensi & Waktu Kerja - HRGA',
            'module' => 'hrga'
        ];
        
        return view('hrga/absensi', $data);
    }

    public function penggajian()
    {
        if (!$this->checkDivisionAccess('hrga')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Penggajian - HRGA',
            'module' => 'hrga'
        ];
        
        return view('hrga/penggajian', $data);
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
}