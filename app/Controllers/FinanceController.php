<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class FinanceController extends BaseController
{
    public function index()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Finance');
        }

        $data = [
            'title' => 'Dashboard Finance & Accounting',
            'module' => 'finance'
        ];
        
        return view('finance/dashboard', $data);
    }

    public function transaksi()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Transaksi - Finance',
            'module' => 'finance'
        ];
        
        return view('finance/transaksi', $data);
    }

    public function anggaran()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Anggaran - Finance',
            'module' => 'finance'
        ];
        
        return view('finance/anggaran', $data);
    }

    public function pajak()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Perpajakan - Finance',
            'module' => 'finance'
        ];
        
        return view('finance/pajak', $data);
    }

    public function aset()
    {
        if (!$this->checkDivisionAccess('finance')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Aset Perusahaan - Finance',
            'module' => 'finance'
        ];
        
        return view('finance/aset', $data);
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