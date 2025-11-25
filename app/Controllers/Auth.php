<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Login - Manajemen Perusahaan'
        ];

        return view('auth/login', $data);
    }

    public function attemptLogin()
    {
        // Validasi input
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Username dan password harus diisi');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        try {
            // Cari user by username
            $user = $this->userModel->getUserByUsername($username);

            if (!$user) {
                return redirect()->back()->withInput()->with('error', 'Username tidak ditemukan');
            }

            // Cek password
            if (!password_verify($password, $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Password salah');
            }

            // Cek status aktif
            if (!$user['is_active']) {
                return redirect()->back()->withInput()->with('error', 'Akun tidak aktif');
            }

            // Update last login
            $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

            // Set session
            $userData = $this->userModel->getUserWithDivision($user['id']);
            
            session()->set([
                'isLoggedIn' => true,
                'userId' => $userData['id'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'nama_lengkap' => $userData['nama_lengkap'],
                'divisi_id' => $userData['divisi_id'],
                'nama_divisi' => $userData['nama_divisi'],
                'kode_divisi' => $userData['kode_divisi'],
                'role' => $userData['role']
            ]);

            // Redirect berdasarkan role
            if ($userData['role'] === 'admin') {
                return redirect()->to('/dashboard')->with('success', 'Selamat datang Administrator!');
            } else {
                return redirect()->to('/home/division/' . strtolower($userData['kode_divisi']))->with('success', 'Selamat datang di divisi ' . $userData['nama_divisi']);
            }

        } catch (\Exception $e) {
            // Fallback jika database error
            return redirect()->back()->withInput()->with('error', 'Sistem sedang dalam perbaikan. Silakan coba lagi nanti.');
        }
    }

    public function buatAkun()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Buat Akun Baru - Manajemen Perusahaan',
            'divisions' => [
                1 => 'HRGA - Human Resources & General Affairs',
                2 => 'HSE - Health, Safety & Environment',
                3 => 'FINANCE - Finance & Accounting', 
                4 => 'PPIC - Production Planning & Inventory Control',
                5 => 'PRODUKSI - Production Department',
                6 => 'MARKETING - Marketing & Sales'
            ]
        ];

        return view('auth/buat_akun', $data);
    }

    public function prosesBuatAkun()
    {
        // Validasi input
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]',
            'email' => 'required|valid_email',
            'nama_lengkap' => 'required',
            'divisi_id' => 'required',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            // Simpan data ke database
            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'divisi_id' => $this->request->getPost('divisi_id'),
                'role' => 'staff',
                'is_active' => true
            ];

            // Simpan ke database
            $this->userModel->save($userData);

            // Auto login setelah buat akun
            $user = $this->userModel->getUserByUsername($userData['username']);
            $userWithDivision = $this->userModel->getUserWithDivision($user['id']);

            session()->set([
                'isLoggedIn' => true,
                'userId' => $userWithDivision['id'],
                'username' => $userWithDivision['username'],
                'email' => $userWithDivision['email'],
                'nama_lengkap' => $userWithDivision['nama_lengkap'],
                'divisi_id' => $userWithDivision['divisi_id'],
                'nama_divisi' => $userWithDivision['nama_divisi'],
                'kode_divisi' => $userWithDivision['kode_divisi'],
                'role' => $userWithDivision['role']
            ]);

            return redirect()->to('/dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang ' . $userData['nama_lengkap']);

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat akun. Username atau email mungkin sudah digunakan.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'Anda telah logout');
    }

    public function profile()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Profile - ' . session()->get('nama_lengkap'),
            'user' => [
                'username' => session()->get('username'),
                'email' => session()->get('email'),
                'nama_lengkap' => session()->get('nama_lengkap'),
                'divisi' => session()->get('nama_divisi'),
                'role' => session()->get('role'),
                'last_login' => session()->get('last_login')
            ]
        ];

        return view('auth/profile', $data);
    }
}