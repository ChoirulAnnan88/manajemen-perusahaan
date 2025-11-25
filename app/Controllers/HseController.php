<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class HseController extends BaseController
{
    public function index()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi HSE');
        }

        $data = [
            'title' => 'Dashboard HSE',
            'module' => 'hse'
        ];
        
        return view('hse/dashboard', $data);
    }

    public function insiden()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Insiden & Kecelakaan Kerja - HSE',
            'module' => 'hse'
        ];
        
        return view('hse/insiden', $data);
    }

    public function risiko()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Risiko & Hazard - HSE',
            'module' => 'hse'
        ];
        
        return view('hse/risiko', $data);
    }

    public function pelatihan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Pelatihan HSE - HSE',
            'module' => 'hse'
        ];
        
        return view('hse/pelatihan', $data);
    }

    public function lingkungan()
    {
        if (!$this->checkDivisionAccess('hse')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Pemantauan Lingkungan - HSE',
            'module' => 'hse'
        ];
        
        return view('hse/lingkungan', $data);
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