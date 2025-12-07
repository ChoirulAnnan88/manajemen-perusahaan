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

// ========== HRGA ROUTES (TIDAK DIUBAH) ==========
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

// ========== HSE ROUTES (DENGAN NAMESPACE BARU) ==========
$routes->group('hse', ['namespace' => 'App\Controllers\HSE'], function($routes) {
    // Dashboard
    $routes->get('/', 'HseController::index');
    $routes->get('dashboard', 'HseController::index');
    
    // Insiden Routes
    $routes->group('insiden', function($routes) {
        $routes->get('/', 'HseController::insiden');
        $routes->get('tambah', 'HseController::tambahInsiden');
        $routes->post('simpan', 'HseController::simpanInsiden');
        $routes->get('detail/(:num)', 'HseController::detailInsiden/$1');
        $routes->get('edit/(:num)', 'HseController::editInsiden/$1');
        $routes->post('update/(:num)', 'HseController::updateInsiden/$1');
        $routes->get('confirm-delete/(:num)', 'HseController::confirmDeleteInsiden/$1');
        $routes->post('delete/(:num)', 'HseController::deleteInsiden/$1');
        $routes->post('update-status/(:num)/(:segment)', 'HseController::updateStatusInsiden/$1/$2');
    });
    
    // Risiko Routes
    $routes->group('risiko', function($routes) {
        $routes->get('/', 'HseController::risiko');
        $routes->get('tambah', 'HseController::tambahRisiko');
        $routes->post('simpan', 'HseController::simpanRisiko');
        $routes->post('update-status/(:num)/(:segment)', 'HseController::updateStatusRisiko/$1/$2');
        $routes->post('hapus/(:num)', 'HseController::hapusRisiko/$1');
    });
    
    // Pelatihan Routes
    $routes->group('pelatihan', function($routes) {
        $routes->get('/', 'HseController::pelatihan');
        $routes->get('tambah', 'HseController::tambahPelatihan');
        $routes->post('simpan', 'HseController::simpanPelatihan');
        $routes->get('edit/(:num)', 'HseController::editPelatihan/$1');
        $routes->post('update/(:num)', 'HseController::updatePelatihan/$1');
        $routes->get('konfirmasi-hapus/(:num)', 'HseController::konfirmasiHapusPelatihan/$1');
        $routes->post('hapus/(:num)', 'HseController::hapusPelatihan/$1');
    });
    
    // Lingkungan Routes
    $routes->group('lingkungan', function($routes) {
        $routes->get('/', 'HseController::lingkungan');
        $routes->get('tambah', 'HseController::tambahLingkungan');
        $routes->post('simpan', 'HseController::simpanLingkungan');
        $routes->get('grafik', 'HseController::grafikLingkungan');
        $routes->get('detail/(:num)', 'HseController::detailLingkungan/$1');
        $routes->get('edit/(:num)', 'HseController::editLingkungan/$1');
        $routes->post('update/(:num)', 'HseController::updateLingkungan/$1');
        $routes->get('confirm-hapus/(:num)', 'HseController::confirmHapusLingkungan/$1');
        $routes->post('hapus/(:num)', 'HseController::hapusLingkungan/$1');
        $routes->get('terhapus', 'HseController::dataTerhapus');
        $routes->get('restore/(:num)', 'HseController::restoreLingkungan/$1');
    });
});

// ========== FINANCE ROUTES (DENGAN NAMESPACE BARU) ==========
$routes->group('finance', ['namespace' => 'App\Controllers\FINANCE'], function($routes) {
    $routes->get('/', 'FinanceController::index');
    $routes->get('dashboard', 'FinanceController::index');
    
    // Transaksi Routes
    $routes->group('transaksi', function($routes) {
        $routes->get('/', 'TransaksiController::index');
        $routes->get('create', 'TransaksiController::create');
        $routes->post('store', 'TransaksiController::store');
        $routes->get('view/(:num)', 'TransaksiController::view/$1');
        $routes->get('edit/(:num)', 'TransaksiController::edit/$1');
        $routes->post('update/(:num)', 'TransaksiController::update/$1');
        $routes->get('delete/(:num)', 'TransaksiController::delete/$1');
    });
    
    // Anggaran Routes
    $routes->group('anggaran', function($routes) {
        $routes->get('/', 'AnggaranController::index');
        $routes->get('create', 'AnggaranController::create');
        $routes->post('store', 'AnggaranController::store');
        $routes->get('view/(:num)', 'AnggaranController::view/$1');
        $routes->get('edit/(:num)', 'AnggaranController::edit/$1');
        $routes->post('update/(:num)', 'AnggaranController::update/$1');
        $routes->get('delete/(:num)', 'AnggaranController::delete/$1');
    });
    
    // Pajak Routes
    $routes->group('pajak', function($routes) {
        $routes->get('/', 'PerpajakanController::index');
        $routes->get('create', 'PerpajakanController::create');
        $routes->post('store', 'PerpajakanController::store');
        $routes->get('view/(:num)', 'PerpajakanController::view/$1');
        $routes->get('edit/(:num)', 'PerpajakanController::edit/$1');
        $routes->post('update/(:num)', 'PerpajakanController::update/$1');
        $routes->get('delete/(:num)', 'PerpajakanController::delete/$1');
        $routes->get('mark-paid/(:num)', 'PerpajakanController::markPaid/$1');
    });
    
    // Aset Routes
    $routes->group('aset', function($routes) {
        $routes->get('/', 'AsetController::index');
        $routes->get('create', 'AsetController::create');
        $routes->post('store', 'AsetController::store');
        $routes->get('view/(:num)', 'AsetController::view/$1');
        $routes->get('edit/(:num)', 'AsetController::edit/$1');
        $routes->post('update/(:num)', 'AsetController::update/$1');
        $routes->get('delete/(:num)', 'AsetController::delete/$1');
    });
});

// ========== PPIC ROUTES ==========
$routes->group('ppic', ['namespace' => 'App\Controllers\PPIC'], function($routes) {
    // Dashboard dan modul utama
    $routes->get('/', 'PpicController::index');
    $routes->get('dashboard', 'PpicController::index');
    $routes->get('inventori', 'PpicController::inventori');
    $routes->get('produksi', 'PpicController::produksi');
    $routes->get('material', 'PpicController::material');
    $routes->get('pemasok', 'PpicController::pemasok');
    $routes->get('pembeli', 'PpicController::pembeli');
    
    // CRUD Inventori
    $routes->get('inventori', 'InventoriController::index');
    $routes->get('inventori/create', 'InventoriController::create');
    $routes->post('inventori/store', 'InventoriController::store');
    $routes->get('inventori/view/(:num)', 'InventoriController::view/$1');
    $routes->get('inventori/edit/(:num)', 'InventoriController::edit/$1');
    $routes->post('inventori/update/(:num)', 'InventoriController::update/$1');
    $routes->get('inventori/delete/(:num)', 'InventoriController::delete/$1');
    
    // CRUD Produksi
    $routes->get('produksi', 'ProduksiController::index');
    $routes->get('produksi/create', 'ProduksiController::create');
    $routes->post('produksi/store', 'ProduksiController::store');
    $routes->get('produksi/view/(:num)', 'ProduksiController::view/$1');
    $routes->get('produksi/edit/(:num)', 'ProduksiController::edit/$1');
    $routes->post('produksi/update/(:num)', 'ProduksiController::update/$1');
    $routes->get('produksi/delete/(:num)', 'ProduksiController::delete/$1');
    
    // CRUD Material
    $routes->get('material', 'MaterialController::index');
    $routes->get('material/create', 'MaterialController::create');
    $routes->post('material/store', 'MaterialController::store');
    $routes->get('material/view/(:num)', 'MaterialController::view/$1');
    $routes->get('material/edit/(:num)', 'MaterialController::edit/$1');
    $routes->post('material/update/(:num)', 'MaterialController::update/$1');
    $routes->get('material/delete/(:num)', 'MaterialController::delete/$1');
    
    // CRUD Pemasok
    $routes->get('pemasok', 'PemasokController::index');
    $routes->get('pemasok/create', 'PemasokController::create');
    $routes->post('pemasok/store', 'PemasokController::store');
    $routes->get('pemasok/view/(:num)', 'PemasokController::view/$1');
    $routes->get('pemasok/edit/(:num)', 'PemasokController::edit/$1');
    $routes->post('pemasok/update/(:num)', 'PemasokController::update/$1');
    $routes->get('pemasok/delete/(:num)', 'PemasokController::delete/$1');
    
    // CRUD Pembeli
    $routes->get('pembeli', 'PembeliController::index');
    $routes->get('pembeli/create', 'PembeliController::create');
    $routes->post('pembeli/store', 'PembeliController::store');
    $routes->get('pembeli/view/(:num)', 'PembeliController::view/$1');
    $routes->get('pembeli/edit/(:num)', 'PembeliController::edit/$1');
    $routes->post('pembeli/update/(:num)', 'PembeliController::update/$1');
    $routes->get('pembeli/delete/(:num)', 'PembeliController::delete/$1');
});

// PRODUKSI Routes
$routes->group('produksi', function($routes) {
    // Main Dashboard
    $routes->get('/', 'PRODUKSI\ProduksiController::index');
    $routes->get('dashboard', 'PRODUKSI\ProduksiController::index');
    
    // Hasil Produksi - CRUD
    $routes->get('hasil', 'PRODUKSI\ProduksiController::hasil');
    $routes->get('hasil/create', 'PRODUKSI\ProduksiController::createHasil');
    $routes->post('hasil/save', 'PRODUKSI\ProduksiController::saveHasil');
    $routes->get('hasil/edit/(:num)', 'PRODUKSI\ProduksiController::editHasil/$1');
    $routes->post('hasil/update/(:num)', 'PRODUKSI\ProduksiController::updateHasil/$1');
    $routes->get('hasil/delete/(:num)', 'PRODUKSI\ProduksiController::deleteHasil/$1');
    $routes->get('hasil/view/(:num)', 'PRODUKSI\ProduksiController::viewHasil/$1');
    
    // Alat dan Bahan - CRUD
    $routes->get('alat', 'PRODUKSI\AlatdanBahanController::index');
    $routes->get('alat/create', 'PRODUKSI\AlatdanBahanController::createAlat');
    $routes->post('alat/save', 'PRODUKSI\AlatdanBahanController::saveAlat');
    $routes->get('alat/edit/(:num)', 'PRODUKSI\AlatdanBahanController::editAlat/$1');
    $routes->post('alat/update/(:num)', 'PRODUKSI\AlatdanBahanController::updateAlat/$1');
    $routes->get('alat/delete/(:num)', 'PRODUKSI\AlatdanBahanController::deleteAlat/$1');
    $routes->get('alat/view/(:num)', 'PRODUKSI\AlatdanBahanController::viewAlat/$1');
    
    // Material - CRUD
    $routes->get('material/create', 'PRODUKSI\AlatdanBahanController::createMaterial');
    $routes->post('material/save', 'PRODUKSI\AlatdanBahanController::saveMaterial');
    $routes->get('material/edit/(:num)', 'PRODUKSI\AlatdanBahanController::editMaterial/$1');
    $routes->post('material/update/(:num)', 'PRODUKSI\AlatdanBahanController::updateMaterial/$1');
    $routes->get('material/delete/(:num)', 'PRODUKSI\AlatdanBahanController::deleteMaterial/$1');
    $routes->get('material/view/(:num)', 'PRODUKSI\AlatdanBahanController::viewMaterial/$1');
    
    // Operator - CRUD
    $routes->get('operator', 'PRODUKSI\OperatorController::index');
    $routes->get('operator/create', 'PRODUKSI\OperatorController::create');
    $routes->post('operator/save', 'PRODUKSI\OperatorController::save');
    $routes->get('operator/edit/(:num)', 'PRODUKSI\OperatorController::edit/$1');
    $routes->post('operator/update/(:num)', 'PRODUKSI\OperatorController::update/$1');
    $routes->get('operator/delete/(:num)', 'PRODUKSI\OperatorController::delete/$1');
    $routes->get('operator/view/(:num)', 'PRODUKSI\OperatorController::view/$1');
});

// ========== MARKETING ROUTES (DENGAN NAMESPACE BARU) ==========
$routes->group('marketing', ['namespace' => 'App\Controllers\MARKETING'], function($routes) {
    $routes->get('/', 'MarketingController::index');
    $routes->get('dashboard', 'MarketingController::index');
    $routes->get('pelanggan', 'MarketingController::pelanggan');
    $routes->get('penjualan', 'MarketingController::penjualan');
    $routes->get('kampanye', 'MarketingController::kampanye');
    $routes->get('riset', 'MarketingController::riset');
});

// ========== DEBUG ROUTE UNTUK HRGA (TIDAK DIUBAH) ==========
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

// ========== DEBUG ROUTE UNTUK STRUKTUR BARU ==========
$routes->get('debug-new-structure', function() {
    echo "<h1>Debug New Structure - Controller per Divisi</h1>";
    
    $divisions = ['HSE', 'FINANCE', 'PPIC', 'PRODUKSI', 'MARKETING'];
    
    foreach ($divisions as $division) {
        echo "<h2>Divisi: $division</h2>";
        
        // Cek pola HrgaController (huruf pertama kapital, sisanya kecil)
        $controllerName = ucfirst(strtolower($division)) . 'Controller';
        $controllerFile = APPPATH . "Controllers/$division/$controllerName.php";
        $controllerClass = "App\Controllers\\$division\\$controllerName";
        
        echo "<ul>";
        echo "<li>Controller Name: $controllerName</li>";
        echo "<li>File: $controllerFile</li>";
        echo "<li>Class: $controllerClass</li>";
        echo "<li>File exists: " . (file_exists($controllerFile) ? '✓' : '✗') . "</li>";
        echo "<li>Class exists: " . (class_exists($controllerClass) ? '✓' : '✗') . "</li>";
        echo "</ul>";
        
        // Test route
        $route = strtolower($division);
        echo "<p>Test Route: <a href='" . base_url($route) . "' target='_blank'>/$route</a></p><hr>";
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
        <p>URL: ' . current_url() . '</p>
        <a href="' . base_url() . '">Kembali ke Home</a> | 
        <a href="' . base_url('auth/login') . '">Login</a> | 
        <a href="' . base_url('dashboard') . '">Dashboard</a> |
        <a href="' . base_url('debug-hrga-structure') . '">Debug HRGA Structure</a> |
        <a href="' . base_url('debug-new-structure') . '">Debug New Structure</a>
    </body>
    </html>';
    
    return $html;
});