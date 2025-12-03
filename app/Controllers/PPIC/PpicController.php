<?php
namespace App\Controllers\PPIC;

use App\Controllers\BaseController;

class PpicController extends BaseController
{
    public function index()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi PPIC');
        }

        $data = [
            'title' => 'Dashboard PPIC',
            'module' => 'ppic'
        ];
        
        return view('ppic/dashboard', $data);
    }

    public function inventori()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Inventori Stok - PPIC',
            'module' => 'ppic'
        ];
        
        return view('ppic/inventori', $data);
    }

    public function produksi()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Perencanaan Produksi - PPIC',
            'module' => 'ppic'
        ];
        
        return view('ppic/produksi', $data);
    }

    public function material()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Material - PPIC',
            'module' => 'ppic'
        ];
        
        return view('ppic/material', $data);
    }

    public function pemasok()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Pemasok - PPIC',
            'module' => 'ppic'
        ];
        
        return view('ppic/pemasok', $data);
    }

    public function pembeli()
    {
        if (!$this->checkDivisionAccess('ppic')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Pembeli - PPIC',
            'module' => 'ppic'
        ];
        
        return view('ppic/pembeli', $data);
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