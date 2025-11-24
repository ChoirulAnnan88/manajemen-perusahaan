<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
    }

    public function login()
    {
    // Jika sudah login, redirect
    if (session()->get('logged_in')) {
        return redirect()->to(session()->get('role') === 'admin' ? '/admin/dashboard' : '/profile');
    }

    // Handle POST request
    if ($this->request->getMethod() === 'post') {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Cari user by email
        $user = $this->userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $sessionData = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'nama_lengkap' => $user['nama_lengkap'],
                'role' => $user['role'],
                'avatar' => $user['avatar'] ?? 'default.png',
                'logged_in' => true
            ];
            session()->set($sessionData);

            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                return redirect()->to('/admin/dashboard')->with('success', 'Login berhasil!');
            } else {
                return redirect()->to('/profile')->with('success', 'Login berhasil!');
            }
        } else {
            // DEBUG: Tampilkan error langsung
            session()->setFlashdata('error', 'Email atau password salah!');
            return redirect()->to('/auth/login');
        }
    }

    // GET request - show login form
    $data = [
        'title' => 'Login - Toko Online'
    ];
    return view('auth/login', $data);
    

        // GET request - show login form
        $data = [
            'title' => 'Login - Toko Online'
        ];
        return view('auth/login', $data);
    }

    public function register()
    {
        // Jika sudah login, redirect
        if (session()->get('logged_in')) {
            return redirect()->to('/profile');
        }

        // Handle POST request
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
                'email' => 'required|valid_email|max_length[255]|is_unique[users.email]',
                'password' => 'required|min_length[6]|max_length[255]',
                'confirm_password' => 'required|matches[password]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            try {
                $userData = [
                    'username' => $this->request->getPost('username'),
                    'email' => $this->request->getPost('email'),
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'nama_lengkap' => $this->request->getPost('nama_lengkap') ?? $this->request->getPost('username'),
                    'role' => 'customer',
                    'is_active' => 1
                ];

                $this->userModel->save($userData);

                return redirect()->to('/auth/login')->with('success', 'Registrasi berhasil! Silakan login.');

            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        }

        // GET request - show register form
        $data = [
            'title' => 'Register - Toko Online',
            'validation' => \Config\Services::validation()
        ];
        return view('auth/register', $data);
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/')->with('success', 'Logout berhasil!');
    }
}