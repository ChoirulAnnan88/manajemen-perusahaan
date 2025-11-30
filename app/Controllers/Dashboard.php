<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $userRole = session()->get('role');
        $userDivisi = session()->get('nama_divisi');
        $userDivisiId = session()->get('divisi_id');

        // Tentukan menu berdasarkan role dan divisi
        $menu = $this->getUserMenu($userRole, $userDivisiId);

        $data = [
            'title' => 'Dashboard - Manajemen Perusahaan',
            'user' => [
                'nama_lengkap' => session()->get('nama_lengkap'),
                'role' => $userRole,
                'divisi' => $userDivisi,
                'username' => session()->get('username')
            ],
            'menu' => $menu
        ];

        // FIX: Gunakan view dashboard yang standalone, bukan extend layout
        return view('dashboard', $data);
    }

    private function getUserMenu($role, $divisiId)
    {
        $baseUrl = base_url();
        
        // Manager bisa akses semua divisi
        if ($role === 'manager') {
            return [
                ['name' => 'HRGA', 'url' => $baseUrl . '/hrga', 'icon' => 'fas fa-users'],
                ['name' => 'HSE', 'url' => $baseUrl . '/hse', 'icon' => 'fas fa-shield-alt'],
                ['name' => 'Finance', 'url' => $baseUrl . '/finance', 'icon' => 'fas fa-chart-line'],
                ['name' => 'PPIC', 'url' => $baseUrl . '/ppic', 'icon' => 'fas fa-boxes'],
                ['name' => 'Produksi', 'url' => $baseUrl . '/produksi', 'icon' => 'fas fa-industry'],
                ['name' => 'Marketing', 'url' => $baseUrl . '/marketing', 'icon' => 'fas fa-bullhorn']
            ];
        }

        // Staff dan Operator hanya divisi sendiri
        $divisionMenu = [
            1 => ['name' => 'HRGA', 'url' => $baseUrl . '/hrga', 'icon' => 'fas fa-users'],
            2 => ['name' => 'HSE', 'url' => $baseUrl . '/hse', 'icon' => 'fas fa-shield-alt'],
            3 => ['name' => 'Finance', 'url' => $baseUrl . '/finance', 'icon' => 'fas fa-chart-line'],
            4 => ['name' => 'PPIC', 'url' => $baseUrl . '/ppic', 'icon' => 'fas fa-boxes'],
            5 => ['name' => 'Produksi', 'url' => $baseUrl . '/produksi', 'icon' => 'fas fa-industry'],
            6 => ['name' => 'Marketing', 'url' => $baseUrl . '/marketing', 'icon' => 'fas fa-bullhorn']
        ];

        return isset($divisionMenu[$divisiId]) ? [$divisionMenu[$divisiId]] : [];
    }
}