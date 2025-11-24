<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserManagement extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        
        // Cek jika bukan admin, redirect ke homepage
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak! Halaman untuk admin saja.');
        }
        
        helper(['form', 'text']);
    }

    /**
     * List semua users (Admin only)
     */
    public function users()
    {
        // 🆕 TAMBAHAN: Search dan filter
        $search = $this->request->getGet('search');
        $role = $this->request->getGet('role');
        $status = $this->request->getGet('status');
        
        $userModel = $this->userModel;
        
        if ($search) {
            $userModel->groupStart()
                ->like('username', $search)
                ->orLike('email', $search)
                ->orLike('nama_lengkap', $search)
                ->orLike('telepon', $search)
                ->groupEnd();
        }
        
        if ($role && $role !== 'all') {
            $userModel->where('role', $role);
        }
        
        if ($status && $status !== 'all') {
            if ($status === 'active') {
                $userModel->where('is_active', 1);
            } elseif ($status === 'inactive') {
                $userModel->where('is_active', 0);
            }
        }

        $data = [
            'title' => 'Management User - Admin',
            'users' => $userModel->orderBy('created_at', 'DESC')->findAll(),
            // 🆕 TAMBAHAN: Untuk filter
            'search' => $search,
            'role_filter' => $role,
            'status_filter' => $status
        ];

        return view('admin/users/list', $data);
    }

    /**
     * Form edit user (Admin only)
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        $data = [
            'title' => 'Edit User - Admin',
            'user' => $user,
            'validation' => \Config\Services::validation() // 🆕 TAMBAHAN: Validation service
        ];

        return view('admin/users/edit', $data);
    }

    /**
     * Process update user (Admin only)
     */
    public function update($id)
    {
        if (!$this->request->is('post')) {
            return redirect()->back()->with('error', 'Method tidak diizinkan');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        // 🆕 TAMBAHAN: CSRF Verification
        if (!csrf_hash_is_valid($this->request->getPost('csrf_token'))) {
            return redirect()->back()->with('error', 'Token CSRF tidak valid!');
        }

        // Data dari form
        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'alamat'   => $this->request->getPost('alamat'),
            'telepon'  => $this->request->getPost('telepon'),
            'role'     => $this->request->getPost('role'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0 // 🆕 TAMBAHAN: Active status
        ];

        // Jika password diisi, update password
        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        // Custom validation rules untuk update (exclude current id)
        $rules = [
            'username' => "required|min_length[3]|max_length[100]|is_unique[users.username,id,{$id}]",
            'email'    => "required|valid_email|max_length[255]|is_unique[users.email,id,{$id}]",
            'password' => 'permit_empty|min_length[6]',
            'telepon'  => 'permit_empty|min_length[10]|max_length[15]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            if ($this->userModel->update($id, $data)) {
                // 🆕 TAMBAHAN: Log activity
                log_message('info', "User {$user['username']} berhasil diupdate oleh admin " . session()->get('username'));
                
                return redirect()->to('/admin/users')->with('success', 'User berhasil diupdate!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal mengupdate user.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating user: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete user (Admin only)
     */
    public function delete($id)
    {
        // Prevent admin from deleting themselves
        if ($id == session()->get('user_id')) {
            return redirect()->to('/admin/users')->with('error', 'Tidak bisa menghapus akun sendiri!');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        // 🆕 TAMBAHAN: Prevent deleting superadmin
        if ($user['role'] === 'superadmin') {
            return redirect()->to('/admin/users')->with('error', 'Tidak bisa menghapus akun superadmin!');
        }

        try {
            if ($this->userModel->delete($id)) {
                // 🆕 TAMBAHAN: Log activity
                log_message('info', "User {$user['username']} berhasil dihapus oleh admin " . session()->get('username'));
                
                return redirect()->to('/admin/users')->with('success', 'User berhasil dihapus!');
            } else {
                return redirect()->to('/admin/users')->with('error', 'Gagal menghapus user');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting user: ' . $e->getMessage());
            return redirect()->to('/admin/users')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    // 🆕 TAMBAHAN METHOD BARU
    
    /**
     * View user details
     */
    public function view($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        $data = [
            'title' => 'Detail User - Admin',
            'user' => $user
        ];

        return view('admin/users/view', $data);
    }
    
    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan']);
        }

        // Prevent deactivating own account
        if ($id == session()->get('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak bisa menonaktifkan akun sendiri!']);
        }

        try {
            $newStatus = $user['is_active'] ? 0 : 1;
            $this->userModel->update($id, ['is_active' => $newStatus]);
            
            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => "User berhasil {$statusText}",
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Bulk actions for users
     */
    public function bulkAction()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $action = $this->request->getJSON()->action;
        $userIds = $this->request->getJSON()->user_ids;

        if (empty($userIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada user yang dipilih']);
        }

        try {
            $successCount = 0;
            
            foreach ($userIds as $userId) {
                // Skip if trying to modify own account
                if ($userId == session()->get('user_id')) continue;
                
                switch ($action) {
                    case 'activate':
                        $this->userModel->update($userId, ['is_active' => 1]);
                        $successCount++;
                        break;
                    case 'deactivate':
                        $this->userModel->update($userId, ['is_active' => 0]);
                        $successCount++;
                        break;
                    case 'delete':
                        $user = $this->userModel->find($userId);
                        if ($user && $user['role'] !== 'superadmin') {
                            $this->userModel->delete($userId);
                            $successCount++;
                        }
                        break;
                }
            }
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => "Berhasil memproses {$successCount} user"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}