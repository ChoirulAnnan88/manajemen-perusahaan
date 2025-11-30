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

        return view('dashboard', $data);
    }

    private function getUserMenu($role, $divisiId)
    {
        // ✅ FIXED: Hanya modifikasi URL HRGA saja, divisi lain tetap
        
        // Manager bisa akses semua divisi
        if ($role === 'manager') {
            return [
                ['name' => 'HRGA', 'url' => '/hrga', 'icon' => 'fas fa-users'], // ✅ HRGA fixed
                ['name' => 'HSE', 'url' => '/hse', 'icon' => 'fas fa-shield-alt'],
                ['name' => 'Finance', 'url' => '/finance', 'icon' => 'fas fa-chart-line'],
                ['name' => 'PPIC', 'url' => '/ppic', 'icon' => 'fas fa-boxes'],
                ['name' => 'Produksi', 'url' => '/produksi', 'icon' => 'fas fa-industry'],
                ['name' => 'Marketing', 'url' => '/marketing', 'icon' => 'fas fa-bullhorn']
            ];
        }

        // Staff dan Operator hanya divisi sendiri
        $divisionMenu = [
            1 => ['name' => 'HRGA', 'url' => '/hrga', 'icon' => 'fas fa-users'], // ✅ HRGA fixed
            2 => ['name' => 'HSE', 'url' => '/hse', 'icon' => 'fas fa-shield-alt'],
            3 => ['name' => 'Finance', 'url' => '/finance', 'icon' => 'fas fa-chart-line'],
            4 => ['name' => 'PPIC', 'url' => '/ppic', 'icon' => 'fas fa-boxes'],
            5 => ['name' => 'Produksi', 'url' => '/produksi', 'icon' => 'fas fa-industry'],
            6 => ['name' => 'Marketing', 'url' => '/marketing', 'icon' => 'fas fa-bullhorn']
        ];

        return isset($divisionMenu[$divisiId]) ? [$divisionMenu[$divisiId]] : [];
    }

    /**
     * ✅ METHOD KHUSUS UNTUK DEBUG HRGA - TIDAK MENGGANGGU DIVISI LAIN
     */
    public function debugHrga()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        echo "<h1>🔧 HRGA Debug Information</h1>";
        
        // Tampilkan session info
        echo "<h3>Session Data:</h3>";
        echo "<pre>";
        print_r([
            'isLoggedIn' => session()->get('isLoggedIn'),
            'role' => session()->get('role'),
            'divisi_id' => session()->get('divisi_id'),
            'nama_divisi' => session()->get('nama_divisi'),
            'nama_lengkap' => session()->get('nama_lengkap')
        ]);
        echo "</pre>";

        // Test URL HRGA
        echo "<h3>Test HRGA URLs:</h3>";
        echo "<ul>";
        echo "<li><a href='/hrga' target='_blank'>/hrga</a></li>";
        echo "<li><a href='/hrga/karyawan' target='_blank'>/hrga/karyawan</a></li>";
        echo "<li><a href='/hrga/absensi' target='_blank'>/hrga/absensi</a></li>";
        echo "</ul>";

        // Test divisi lain (tetap bekerja)
        echo "<h3>Test Other Divisions (should work):</h3>";
        echo "<ul>";
        echo "<li><a href='/hse' target='_blank'>/hse</a></li>";
        echo "<li><a href='/finance' target='_blank'>/finance</a></li>";
        echo "</ul>";

        echo "<br><a href='/dashboard' class='btn btn-primary'>Back to Dashboard</a>";
    }

    /**
     * ✅ METHOD UNTUK FORCE HRGA ACCESS (Testing Only)
     */
    public function forceHrga()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        // Simpan divisi asli untuk nanti direset
        $originalDivisi = session()->get('divisi_id');
        session()->set('hrga_original_divisi', $originalDivisi);

        // Force set sebagai HRGA
        session()->set('divisi_id', 1);
        session()->set('nama_divisi', 'HRGA');

        return redirect()->to('/hrga')->with('success', 'Forced HRGA access for testing');
    }

    /**
     * ✅ METHOD UNTUK RESET KE DIVISI ASLI
     */
    public function resetDivisi()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $originalDivisi = session()->get('hrga_original_divisi');
        
        if ($originalDivisi) {
            session()->set('divisi_id', $originalDivisi);
            // Set nama divisi berdasarkan ID
            $divisionNames = [
                1 => 'HRGA', 2 => 'HSE', 3 => 'Finance', 
                4 => 'PPIC', 5 => 'Produksi', 6 => 'Marketing'
            ];
            session()->set('nama_divisi', $divisionNames[$originalDivisi] ?? 'Unknown');
            session()->remove('hrga_original_divisi');
        }

        return redirect()->to('/dashboard')->with('success', 'Divisi berhasil direset ke aslinya');
    }
}