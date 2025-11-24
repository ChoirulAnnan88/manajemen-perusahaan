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

// Catch all - 404 (SIMPLE VERSION)
$routes->set404Override(function() {
    return "Halaman tidak ditemukan. <a href='/'>Kembali ke Home</a>";
});