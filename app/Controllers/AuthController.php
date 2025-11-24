<?php
namespace App\Controllers;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function register() 
    { 
        $data = ['title' => 'Register - TokoOnline'];
        return view('auth/register', $data); 
    }
    
    public function processRegister()
    {
        if (!$this->request->is('post')) return redirect()->back()->with('error', 'Method tidak diizinkan');
        
        $userModel = new UserModel();
        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'alamat'   => $this->request->getPost('alamat'),
            'telepon'  => $this->request->getPost('telepon'),
            'role'     => $this->request->getPost('role') // Wajib ada role
        ];

        if (!$userModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        try {
            if ($userModel->insert($data)) {
                $user = $userModel->find($userModel->getInsertID());
                $this->setUserSession($user);
                return redirect()->to('/')->with('success', 'Registrasi berhasil! Selamat datang, ' . $user['username'] . ' (' . $user['role'] . ')');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Username/Email sudah digunakan');
        }
    }

    public function login() 
    { 
        $data = ['title' => 'Login - TokoOnline'];
        return view('auth/login', $data); 
    }
    
    public function processLogin()
    {
        if (!$this->request->is('post')) return redirect()->back()->with('error', 'Method tidak diizinkan');
        
        $userModel = new UserModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $username)->orWhere('email', $username)->first();
        
        // PLAIN PASSWORD CHECK (tanpa password_verify)
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Username/Email tidak ditemukan');
        }
        
        // Check plain text password
        if ($user['password'] !== $password) {
            return redirect()->back()->withInput()->with('error', 'Password salah');
        }

        $this->setUserSession($user);
        return redirect()->to('/')->with('success', 'Login berhasil! Selamat datang, ' . $user['username'] . ' (' . $user['role'] . ')');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('success', 'Logout berhasil!');
    }

    private function setUserSession($user)
    {
        session()->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role' => $user['role'],
            'logged_in' => true
        ]);
    }
}