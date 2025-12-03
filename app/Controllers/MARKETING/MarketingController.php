<?php
namespace App\Controllers\MARKETING;

use App\Controllers\BaseController;

class MarketingController extends BaseController
{
    public function index()
    {
        if (!$this->checkDivisionAccess('marketing')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak ke divisi Marketing');
        }

        $data = [
            'title' => 'Dashboard Marketing',
            'module' => 'marketing'
        ];
        
        return view('marketing/dashboard', $data);
    }

    public function pelanggan()
    {
        if (!$this->checkDivisionAccess('marketing')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Pelanggan - Marketing',
            'module' => 'marketing'
        ];
        
        return view('marketing/pelanggan', $data);
    }

    public function penjualan()
    {
        if (!$this->checkDivisionAccess('marketing')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Penjualan - Marketing',
            'module' => 'marketing'
        ];
        
        return view('marketing/penjualan', $data);
    }

    public function kampanye()
    {
        if (!$this->checkDivisionAccess('marketing')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Kampanye & Promosi - Marketing',
            'module' => 'marketing'
        ];
        
        return view('marketing/kampanye', $data);
    }

    public function riset()
    {
        if (!$this->checkDivisionAccess('marketing')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Riset Pasar - Marketing',
            'module' => 'marketing'
        ];
        
        return view('marketing/riset', $data);
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