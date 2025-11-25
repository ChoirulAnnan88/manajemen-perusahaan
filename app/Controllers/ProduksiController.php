<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class ProduksiController extends BaseController
{
    public function index()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Produksi');
        }

        $data = [
            'title' => 'Dashboard Produksi',
            'module' => 'produksi'
        ];
        
        return view('produksi/dashboard', $data);
    }

    public function hasil()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Hasil Produksi - Produksi',
            'module' => 'produksi'
        ];
        
        return view('produksi/hasil', $data);
    }

    public function alat()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Alat dan Bahan - Produksi',
            'module' => 'produksi'
        ];
        
        return view('produksi/alat', $data);
    }

    public function operator()
    {
        if (!$this->checkDivisionAccess('produksi')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Operator - Produksi',
            'module' => 'produksi'
        ];
        
        return view('produksi/operator', $data);
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