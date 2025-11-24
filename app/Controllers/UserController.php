<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        
        // Cek login
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Silakan login terlebih dahulu');
        }
    }

    /**
     * User profile page
     */
    public function profile()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/auth/logout')->with('error', 'User tidak ditemukan');
        }

        $data = [
            'title' => 'Profile Saya',
            'user' => $user // PASTIKAN VARIABLE USER ADA
        ];

        return view('users/profile', $data);
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        if (!$this->request->is('post')) {
            return redirect()->back()->with('error', 'Method tidak diizinkan');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/auth/logout')->with('error', 'User tidak ditemukan');
        }

        // Data dari form
        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'alamat'   => $this->request->getPost('alamat'),
            'telepon'  => $this->request->getPost('telepon')
        ];

        // Jika ingin update username/email, perlu validasi unique
        if ($this->request->getPost('username') !== $user['username']) {
            $data['username'] = $this->request->getPost('username');
        }

        if ($this->request->getPost('email') !== $user['email']) {
            $data['email'] = $this->request->getPost('email');
        }

        // Custom validation rules
        $rules = [
            'username' => "required|min_length[3]|max_length[100]|is_unique[users.username,id,{$userId}]",
            'email'    => "required|valid_email|max_length[255]|is_unique[users.email,id,{$userId}]"
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            if ($this->userModel->update($userId, $data)) {
                // Update session jika username/email berubah
                if (isset($data['username'])) {
                    session()->set('username', $data['username']);
                }
                if (isset($data['email'])) {
                    session()->set('email', $data['email']);
                }
                if (isset($data['nama_lengkap'])) {
                    session()->set('nama_lengkap', $data['nama_lengkap']);
                }
                
                return redirect()->to('/profile')->with('success', 'Profile berhasil diupdate!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal mengupdate profile.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}