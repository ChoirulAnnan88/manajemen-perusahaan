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

// ========== PERBAIKAN UTAMA: TAMBAHKAN ROUTE STANDALONE UNTUK HRGA ==========
$routes->get('hrga', 'HrgaController::index');
$routes->get('hrga/(:any)', 'HrgaController::index');

// Debug Routes
$routes->get('debug/hrga', 'DebugController::hrga');
$routes->get('debug/routes', 'DebugController::routes');

// HRGA Routes Group - PERBAIKI DENGAN NAMESPACE
$routes->group('hrga', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'HrgaController::index');
    
    // Karyawan Routes
    $routes->get('karyawan', 'HrgaController::karyawan');
    $routes->get('karyawan/tambah', 'HrgaController::tambahKaryawan');
    $routes->post('karyawan/simpan', 'HrgaController::simpanKaryawan');
    $routes->get('karyawan/edit/(:num)', 'HrgaController::editKaryawan/$1');
    $routes->post('karyawan/update/(:num)', 'HrgaController::updateKaryawan/$1');
    $routes->get('karyawan/detail/(:num)', 'HrgaController::detailKaryawan/$1');
    $routes->get('karyawan/hapus/(:num)', 'HrgaController::hapusKaryawan/$1');
    
    // Absensi Routes
    $routes->get('absensi', 'HrgaController::absensi');
    $routes->post('absensi/simpan', 'HrgaController::simpanAbsensi');
    $routes->get('absensi/riwayat', 'HrgaController::riwayatAbsensi');
    
    // Penggajian Routes
    $routes->get('penggajian', 'HrgaController::penggajian');
    $routes->get('penggajian/generate', 'HrgaController::generatePenggajian');
    $routes->post('penggajian/proses', 'HrgaController::prosesPenggajian');
    $routes->get('penggajian/slip/(:num)', 'HrgaController::slipGaji/$1');
    
    // Penilaian Routes
    $routes->get('penilaian', 'HrgaController::penilaian');
    $routes->post('penilaian/simpan', 'HrgaController::simpanPenilaian');
    
    // Inventaris Routes
    $routes->get('inventaris', 'HrgaController::inventaris');
    $routes->post('inventaris/simpan', 'HrgaController::simpanInventaris');
    
    // Perawatan Routes
    $routes->get('perawatan', 'HrgaController::perawatan');
    $routes->post('perawatan/simpan', 'HrgaController::simpanPerawatan');
    
    // Perizinan Routes
    $routes->get('perizinan', 'HrgaController::perizinan');
    $routes->post('perizinan/ajukan', 'HrgaController::ajukanPerizinan');
    $routes->get('perizinan/approve/(:num)', 'HrgaController::approvePerizinan/$1');
    $routes->get('perizinan/reject/(:num)', 'HrgaController::rejectPerizinan/$1');
});

// HSE Routes
$routes->group('hse', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'HseController::index');
    $routes->get('insiden', 'HseController::insiden');
    $routes->get('risiko', 'HseController::risiko');
    $routes->get('pelatihan', 'HseController::pelatihan');
    $routes->get('lingkungan', 'HseController::lingkungan');
});

// Finance Routes
$routes->group('finance', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'FinanceController::index');
    $routes->get('transaksi', 'FinanceController::transaksi');
    $routes->get('anggaran', 'FinanceController::anggaran');
    $routes->get('pajak', 'FinanceController::pajak');
    $routes->get('aset', 'FinanceController::aset');
});

// PPIC Routes
$routes->group('ppic', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'PpicController::index');
    $routes->get('inventori', 'PpicController::inventori');
    $routes->get('produksi', 'PpicController::produksi');
    $routes->get('material', 'PpicController::material');
    $routes->get('pemasok', 'PpicController::pemasok');
    $routes->get('pembeli', 'PpicController::pembeli');
});

// Produksi Routes
$routes->group('produksi', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'ProduksiController::index');
    $routes->get('hasil', 'ProduksiController::hasil');
    $routes->get('alat', 'ProduksiController::alat');
    $routes->get('operator', 'ProduksiController::operator');
});

// Marketing Routes
$routes->group('marketing', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'MarketingController::index');
    $routes->get('pelanggan', 'MarketingController::pelanggan');
    $routes->get('penjualan', 'MarketingController::penjualan');
    $routes->get('kampanye', 'MarketingController::kampanye');
    $routes->get('riset', 'MarketingController::riset');
});

// ========== ROUTE DEBUG ==========
$routes->get('debug-routes', function() {
    echo "<h1>Debug Routes</h1>";
    echo "Current URL: " . current_url() . "<br>";
    echo "Base URL: " . base_url() . "<br>";
    
    // Test HRGA Controller
    if (class_exists('App\Controllers\HrgaController')) {
        echo "✓ HrgaController exists<br>";
    } else {
        echo "✗ HrgaController NOT FOUND<br>";
    }
    
    // Test method
    if (method_exists('App\Controllers\HrgaController', 'index')) {
        echo "✓ HrgaController::index exists<br>";
    } else {
        echo "✗ HrgaController::index NOT FOUND<br>";
    }
    
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
        <a href="' . base_url() . '">Kembali ke Home</a> | 
        <a href="' . base_url('auth/login') . '">Pergi ke Login</a> | 
        <a href="' . base_url('dashboard') . '">Dashboard</a>
    </body>
    </html>';
    
    return $html;
});