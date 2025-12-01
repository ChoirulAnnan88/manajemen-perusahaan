<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false); // Matikan auto route untuk keamanan

// ========== PUBLIC ROUTES ==========
$routes->get('/', 'Home::index');
$routes->get('division/(:any)', 'Home::division/$1');

// ========== AUTH ROUTES ==========
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/attemptLogin', 'Auth::attemptLogin');
$routes->get('auth/buat-akun', 'Auth::buatAkun');
$routes->post('auth/proses-buat-akun', 'Auth::prosesBuatAkun');
$routes->get('auth/logout', 'Auth::logout');
$routes->get('auth/profile', 'Auth::profile');

// ========== DASHBOARD ==========
$routes->get('dashboard', 'Dashboard::index');

// ========== HRGA ROUTES ==========
$routes->group('hrga', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'HrgaController::index');
    
    // Karyawan
    $routes->get('karyawan', 'HrgaController::karyawan');
    $routes->get('karyawan/tambah', 'HrgaController::tambahKaryawan');
    $routes->post('karyawan/simpan', 'HrgaController::simpanKaryawan');
    $routes->get('karyawan/edit/(:num)', 'HrgaController::editKaryawan/$1');
    $routes->post('karyawan/update/(:num)', 'HrgaController::updateKaryawan/$1');
    $routes->get('karyawan/detail/(:num)', 'HrgaController::detailKaryawan/$1');
    $routes->get('karyawan/hapus/(:num)', 'HrgaController::hapusKaryawan/$1');
    
    // Absensi
    $routes->get('absensi', 'HrgaController::absensi');
    $routes->post('absensi/simpan', 'HrgaController::simpanAbsensi');
    $routes->get('absensi/riwayat', 'HrgaController::riwayatAbsensi');
    
    // Penggajian
    $routes->get('penggajian', 'HrgaController::penggajian');
    $routes->get('penggajian/generate', 'HrgaController::generatePenggajian');
    $routes->post('penggajian/proses', 'HrgaController::prosesPenggajian');
    $routes->get('penggajian/slip/(:num)', 'HrgaController::slipGaji/$1');
    
    // Penilaian
    $routes->get('penilaian', 'HrgaController::penilaian');
    $routes->post('penilaian/simpan', 'HrgaController::simpanPenilaian');
    
    // Inventaris
    $routes->get('inventaris', 'HrgaController::inventaris');
    $routes->post('inventaris/simpan', 'HrgaController::simpanInventaris');
    
    // Perawatan
    $routes->get('perawatan', 'HrgaController::perawatan');
    $routes->post('perawatan/simpan', 'HrgaController::simpanPerawatan');
    
    // Perizinan
    $routes->get('perizinan', 'HrgaController::perizinan');
    $routes->post('perizinan/ajukan', 'HrgaController::ajukanPerizinan');
    $routes->get('perizinan/approve/(:num)', 'HrgaController::approvePerizinan/$1');
    $routes->get('perizinan/reject/(:num)', 'HrgaController::rejectPerizinan/$1');
});

// ========== HSE ROUTES ==========
$routes->group('hse', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'HseController::index');
    $routes->get('insiden', 'HseController::insiden');
    $routes->get('risiko', 'HseController::risiko');
    $routes->get('pelatihan', 'HseController::pelatihan');
    $routes->get('lingkungan', 'HseController::lingkungan');
});

// ========== FINANCE ROUTES ==========
$routes->group('finance', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'FinanceController::index');
    $routes->get('transaksi', 'FinanceController::transaksi');
    $routes->get('anggaran', 'FinanceController::anggaran');
    $routes->get('pajak', 'FinanceController::pajak');
    $routes->get('aset', 'FinanceController::aset');
});

// ========== PPIC ROUTES ==========
$routes->group('ppic', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'PpicController::index');
    $routes->get('inventori', 'PpicController::inventori');
    $routes->get('produksi', 'PpicController::produksi');
    $routes->get('material', 'PpicController::material');
    $routes->get('pemasok', 'PpicController::pemasok');
    $routes->get('pembeli', 'PpicController::pembeli');
});

// ========== PRODUKSI ROUTES ==========
$routes->group('produksi', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'ProduksiController::index');
    $routes->get('hasil', 'ProduksiController::hasil');
    $routes->get('alat', 'ProduksiController::alat');
    $routes->get('operator', 'ProduksiController::operator');
});

// ========== MARKETING ROUTES ==========
$routes->group('marketing', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'MarketingController::index');
    $routes->get('pelanggan', 'MarketingController::pelanggan');
    $routes->get('penjualan', 'MarketingController::penjualan');
    $routes->get('kampanye', 'MarketingController::kampanye');
    $routes->get('riset', 'MarketingController::riset');
});

// ========== 404 ERROR PAGE ==========
$routes->set404Override(function() {
    $data = [
        'title' => '404 - Page Not Found',
        'config' => config('App')
    ];
    return view('errors/html/error_404', $data);
});

// ========== CATCH-ALL FOR DEBUG ==========
if (ENVIRONMENT === 'development') {
    $routes->get('debug/routes', function() {
        $collection = \Config\Services::routes();
        echo "<h1>Debug Routes</h1>";
        echo "<pre>";
        print_r($collection->getRoutes());
        echo "</pre>";
    });
    
    $routes->get('debug/session', function() {
        echo "<h1>Debug Session</h1>";
        echo "<pre>";
        print_r(session()->get());
        echo "</pre>";
    });
}