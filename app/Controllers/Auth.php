<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function login()
    {
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
            $user = $this->userModel->getUserByUsername($username);

            if (!$user) {
                return redirect()->back()->withInput()->with('error', 'Username tidak ditemukan');
            }

            if ($password !== $user['password']) {
                return redirect()->back()->withInput()->with('error', 'Password salah');
            }

            if (empty($user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Akun tidak valid. Silakan buat akun baru.');
            }

            if (!$user['is_active']) {
                return redirect()->back()->withInput()->with('error', 'Akun tidak aktif');
            }

            $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

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

            if ($userData['role'] === 'manager') {
                return redirect()->to('/dashboard')->with('success', 'Selamat datang Manager!');
            } else {
                return redirect()->to('/dashboard')->with('success', 'Selamat datang di divisi ' . $userData['nama_divisi']);
            }

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Sistem sedang dalam perbaikan. Silakan coba lagi nanti.');
        }
    }

    public function buatAkun()
    {
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
            ],
            'roles' => [
                'manager' => 'Manager',
                'staff' => 'Staff', 
                'operator' => 'Operator'
            ]
        ];

        return view('auth/buat_akun', $data);
    }

    public function prosesBuatAkun()
    {
        $validationRules = [
            'username' => 'required|min_length[3]|max_length[50]',
            'email' => 'required|valid_email',
            'nama_lengkap' => 'required|min_length[2]',
            'divisi_id' => 'required|numeric',
            'role' => 'required|in_list[manager,staff,operator]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($validationRules)) {
            $errors = $this->validator->getErrors();
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'divisi_id' => $this->request->getPost('divisi_id'),
            'role' => $this->request->getPost('role'),
            'is_active' => 1
        ];

        try {
            $this->userModel->skipValidation(true);
            $result = $this->userModel->insert($userData);
            
            if ($result) {
                $userId = $this->userModel->getInsertID();
                
                if ($userId) {
                    $userWithDivision = $this->userModel->getUserWithDivision($userId);

                    if ($userWithDivision) {
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
                    } else {
                        $user = $this->userModel->find($userId);
                        session()->set([
                            'isLoggedIn' => true,
                            'userId' => $user['id'],
                            'username' => $user['username'],
                            'email' => $user['email'],
                            'nama_lengkap' => $user['nama_lengkap'],
                            'divisi_id' => $user['divisi_id'],
                            'role' => $user['role']
                        ]);
                        return redirect()->to('/dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang ' . $userData['nama_lengkap']);
                    }
                } else {
                    throw new \Exception('Gagal mendapatkan ID user setelah insert');
                }
            } else {
                throw new \Exception('Insert user gagal');
            }

        } catch (\Exception $e) {
            $errorMessage = 'Gagal membuat akun: ' . $e->getMessage();
            return redirect()->back()->withInput()->with('error', $errorMessage);
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