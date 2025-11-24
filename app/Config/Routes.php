<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public Routes
$routes->get('/', 'Home::index');
$routes->get('home/division/(:segment)', 'Home::division/$1');

// Auth Routes
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/attemptLogin', 'Auth::attemptLogin');
$routes->get('auth/buat-akun', 'Auth::buatAkun');
$routes->post('auth/proses-buat-akun', 'Auth::prosesBuatAkun');
$routes->get('auth/logout', 'Auth::logout');
$routes->get('auth/profile', 'Auth::profile');

// Dashboard Route (protected)
$routes->get('dashboard', 'Auth::profile');

// Catch all - 404 (FIXED VERSION)
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
        <a href="/auth/buat-akun">Buat Akun Baru</a>
    </body>
    </html>';
    
    return $html;
});

// Error handler untuk menampilkan error sebenarnya
if (!function_exists('show_error_details')) {
    function show_error_details($error) {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Error - Manajemen Perusahaan</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .error-box { 
                    background: #f8f9fa; 
                    border: 1px solid #dee2e6; 
                    border-radius: 5px; 
                    padding: 20px; 
                    max-width: 800px; 
                    margin: 0 auto; 
                }
                .error-header { 
                    background: #d9534f; 
                    color: white; 
                    padding: 15px; 
                    border-radius: 5px 5px 0 0; 
                    margin: -20px -20px 20px -20px;
                }
                a { 
                    display: inline-block; 
                    padding: 10px 20px; 
                    background: #007bff; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 10px 5px; 
                }
                .error-details {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 15px 0;
                    font-family: monospace;
                    white-space: pre-wrap;
                }
            </style>
        </head>
        <body>
            <div class="error-box">
                <div class="error-header">
                    <h1>⚠️ Terjadi Kesalahan Sistem</h1>
                </div>
                <p><strong>Pesan Error:</strong></p>
                <div class="error-details">' . htmlspecialchars($error) . '</div>
                <p>Silakan coba lagi atau hubungi administrator.</p>
                <div>
                    <a href="/">Kembali ke Home</a>
                    <a href="/auth/login">Pergi ke Login</a>
                    <a href="/auth/buat-akun">Buat Akun Baru</a>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}