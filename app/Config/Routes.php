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

// HRGA Routes
$routes->group('hrga', ['filter' => 'divisionAuth'], function($routes) {
    $routes->get('/', 'HrgaController::index');
    $routes->get('karyawan', 'HrgaController::karyawan');
    $routes->get('absensi', 'HrgaController::absensi');
    $routes->get('penggajian', 'HrgaController::penggajian');
    $routes->get('penilaian', 'HrgaController::penilaian');
    $routes->get('inventaris', 'HrgaController::inventaris');
    $routes->get('perawatan', 'HrgaController::perawatan');
    $routes->get('perizinan', 'HrgaController::perizinan');
});

// HSE Routes  
$routes->group('hse', ['filter' => 'divisionAuth'], function($routes) {
    $routes->get('/', 'HseController::index');
    $routes->get('insiden', 'HseController::insiden');
    $routes->get('risiko', 'HseController::risiko');
    $routes->get('pelatihan', 'HseController::pelatihan');
    $routes->get('lingkungan', 'HseController::lingkungan');
});

// Finance Routes
$routes->group('finance', ['filter' => 'divisionAuth'], function($routes) {
    $routes->get('/', 'FinanceController::index');
    $routes->get('transaksi', 'FinanceController::transaksi');
    $routes->get('anggaran', 'FinanceController::anggaran');
    $routes->get('pajak', 'FinanceController::pajak');
    $routes->get('aset', 'FinanceController::aset');
});

// PPIC Routes
$routes->group('ppic', ['filter' => 'divisionAuth'], function($routes) {
    $routes->get('/', 'PpicController::index');
    $routes->get('inventori', 'PpicController::inventori');
    $routes->get('produksi', 'PpicController::produksi');
    $routes->get('material', 'PpicController::material');
    $routes->get('pemasok', 'PpicController::pemasok');
    $routes->get('pembeli', 'PpicController::pembeli');
});

// Produksi Routes
$routes->group('produksi', ['filter' => 'divisionAuth'], function($routes) {
    $routes->get('/', 'ProduksiController::index');
    $routes->get('hasil', 'ProduksiController::hasil');
    $routes->get('alat', 'ProduksiController::alat');
    $routes->get('operator', 'ProduksiController::operator');
});

// Marketing Routes
$routes->group('marketing', ['filter' => 'divisionAuth'], function($routes) {
    $routes->get('/', 'MarketingController::index');
    $routes->get('pelanggan', 'MarketingController::pelanggan');
    $routes->get('penjualan', 'MarketingController::penjualan');
    $routes->get('kampanye', 'MarketingController::kampanye');
    $routes->get('riset', 'MarketingController::riset');
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
        <a href="/">Kembali ke Home</a> | 
        <a href="/auth/login">Pergi ke Login</a> | 
        <a href="/dashboard">Dashboard</a>
    </body>
    </html>';
    
    return $html;
});