<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public Routes
$routes->get('/', 'Home::index');
$routes->get('division/(:segment)', 'Home::division/$1');

// Auth Routes
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/attemptLogin', 'Auth::attemptLogin');
$routes->get('auth/buat-akun', 'Auth::buatAkun');
$routes->post('auth/proses-buat-akun', 'Auth::prosesBuatAkun');
$routes->get('auth/logout', 'Auth::logout');
$routes->get('auth/profile', 'Auth::profile');

// Dashboard Route
$routes->get('dashboard', 'Dashboard::index');

// ========== HRGA ROUTES (MULTIPLE CONTROLLERS) ==========
$routes->group('hrga', ['namespace' => 'App\Controllers\HRGA'], function($routes) {
    
    // Dashboard HRGA - HrgaController
    $routes->get('/', 'HrgaController::index');
    $routes->get('dashboard', 'HrgaController::dashboard');
    
    
    // Karyawan Routes - KaryawanController
    $routes->get('karyawan', 'KaryawanController::index');
    $routes->get('karyawan/tambah', 'KaryawanController::tambah');
    $routes->post('karyawan/store', 'KaryawanController::store');
    $routes->get('karyawan/edit/(:num)', 'KaryawanController::edit/$1');
    $routes->post('karyawan/update/(:num)', 'KaryawanController::update/$1');
    $routes->get('karyawan/detail/(:num)', 'KaryawanController::detail/$1');
    $routes->get('karyawan/hapus/(:num)', 'KaryawanController::hapus/$1');
    
    // Absensi Routes - AbsensiController
    $routes->get('absensi', 'AbsensiController::index');
    $routes->post('absensi/simpan', 'AbsensiController::simpan');
    $routes->get('absensi/riwayat', 'AbsensiController::riwayat');
    
    // Penggajian Routes - PenggajianController
    $routes->get('penggajian', 'PenggajianController::index');
    $routes->get('penggajian/generate', 'PenggajianController::generate');
    $routes->post('penggajian/proses', 'PenggajianController::proses');
    $routes->post('penggajian/bayar/(:num)', 'PenggajianController::bayar/$1');
    $routes->get('penggajian/slip/(:num)', 'PenggajianController::slip/$1');
    $routes->get('penggajian/edit/(:num)', 'PenggajianController::edit/$1');
    $routes->post('penggajian/update/(:num)', 'PenggajianController::update/$1');
    $routes->get('penggajian/hapus/(:num)', 'PenggajianController::hapus/$1');
    $routes->get('penggajian/cetak/(:num)/(:num)', 'PenggajianController::cetak_semua/$1/$2');

    // Penilaian Routes - PenilaianController
    $routes->get('penilaian', 'PenilaianController::index');
    $routes->post('penilaian/simpan', 'PenilaianController::simpan');
    
    // Inventaris Routes - InventarisController
    $routes->get('inventaris', 'InventarisController::index');
    $routes->post('inventaris/simpan', 'InventarisController::simpan');
    $routes->get('inventaris/detail/(:num)', 'InventarisController::detail/$1');
    $routes->post('inventaris/update/(:num)', 'InventarisController::update/$1');
    $routes->get('inventaris/hapus/(:num)', 'InventarisController::hapus/$1');
    
    // Perawatan Routes - PerawatanController
    $routes->get('perawatan', 'PerawatanController::index');
    $routes->post('perawatan/simpan', 'PerawatanController::simpan');
    
    // Perizinan Routes - PerizinanController
    $routes->get('perizinan', 'PerizinanController::index');
    $routes->post('perizinan/ajukan', 'PerizinanController::ajukan');
    $routes->get('perizinan/approve/(:num)', 'PerizinanController::approve/$1');
    $routes->get('perizinan/reject/(:num)', 'PerizinanController::reject/$1');
    $routes->get('perizinan/detail/(:num)', 'PerizinanController::detail/$1');
});

// ========== DIVISI LAIN (TETAP SAMA) ==========
// HSE Routes - controller ada di app\Controllers\ (root)
$routes->group('hse', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'HseController::index');
    $routes->get('insiden', 'HseController::insiden');
    $routes->get('risiko', 'HseController::risiko');
    $routes->get('pelatihan', 'HseController::pelatihan');
    $routes->get('lingkungan', 'HseController::lingkungan');
});

// Finance Routes - controller ada di app\Controllers\ (root)
$routes->group('finance', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'FinanceController::index');
    $routes->get('transaksi', 'FinanceController::transaksi');
    $routes->get('anggaran', 'FinanceController::anggaran');
    $routes->get('pajak', 'FinanceController::pajak');
    $routes->get('aset', 'FinanceController::aset');
});

// PPIC Routes - controller ada di app\Controllers\ (root)
$routes->group('ppic', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'PpicController::index');
    $routes->get('inventori', 'PpicController::inventori');
    $routes->get('produksi', 'PpicController::produksi');
    $routes->get('material', 'PpicController::material');
    $routes->get('pemasok', 'PpicController::pemasok');
    $routes->get('pembeli', 'PpicController::pembeli');
});

// Produksi Routes - controller ada di app\Controllers\ (root)
$routes->group('produksi', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'ProduksiController::index');
    $routes->get('hasil', 'ProduksiController::hasil');
    $routes->get('alat', 'ProduksiController::alat');
    $routes->get('operator', 'ProduksiController::operator');
});

// Marketing Routes - controller ada di app\Controllers\ (root)
$routes->group('marketing', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'MarketingController::index');
    $routes->get('pelanggan', 'MarketingController::pelanggan');
    $routes->get('penjualan', 'MarketingController::penjualan');
    $routes->get('kampanye', 'MarketingController::kampanye');
    $routes->get('riset', 'MarketingController::riset');
});

// ========== DEBUG ROUTE UNTUK HRGA ==========
$routes->get('debug-hrga-structure', function() {
    echo "<h1>Debug HRGA Structure</h1>";
    echo "Base URL: " . base_url() . "<br>";
    echo "Current URL: " . current_url() . "<br><br>";
    
    // List semua controller HRGA
    $hrgaDir = APPPATH . 'Controllers/HRGA/';
    if (is_dir($hrgaDir)) {
        echo "<h3>Controller HRGA yang ditemukan:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>File</th><th>Class</th><th>Status</th></tr>";
        
        $files = scandir($hrgaDir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $filePath = $hrgaDir . $file;
                echo "<tr>";
                echo "<td>$file</td>";
                
                // Baca isi file
                $content = file_get_contents($filePath);
                
                // Ekstrak nama class
                preg_match('/class\s+(\w+)/', $content, $matches);
                $className = $matches[1] ?? 'Unknown';
                echo "<td>$className</td>";
                
                // Test class existence
                $fullClassName = 'App\Controllers\HRGA\\' . $className;
                if (class_exists($fullClassName)) {
                    echo "<td style='color: green;'>✓ Dapat di-load</td>";
                } else {
                    echo "<td style='color: red;'>✗ Tidak dapat di-load</td>";
                }
                
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "Folder HRGA tidak ditemukan: $hrgaDir";
    }
    
    // Test beberapa route
    echo "<h3>Test Routes:</h3>";
    echo "<ul>";
    $testRoutes = [
        '/hrga' => 'HrgaController::index',
        '/hrga/karyawan' => 'KaryawanController::index',
        '/hrga/absensi' => 'AbsensiController::index'
    ];
    
    foreach ($testRoutes as $route => $handler) {
        echo "<li><a href='" . base_url($route) . "'>$route</a> → $handler</li>";
    }
    echo "</ul>";
    
    die();
});

// Catch all - 404
$routes->set404Override(function() {
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <title>404 - Halaman Tidak Ditemukan</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            h1 { color: #d9534f; }
            a { color: #007bff; text-decoration: none; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <h1>404 - Halaman Tidak Ditemukan</h1>
        <p>Halaman yang Anda cari tidak ditemukan.</p>
        <p>URL: ' . current_url() . '</p>
        <a href="' . base_url() . '">Kembali ke Home</a> | 
        <a href="' . base_url('auth/login') . '">Login</a> | 
        <a href="' . base_url('dashboard') . '">Dashboard</a> | 
        <a href="' . base_url('debug-hrga-structure') . '">Debug HRGA Structure</a>
    </body>
    </html>';
    
    return $html;
});