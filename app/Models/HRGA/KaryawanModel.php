<?php

namespace App\Models\HRGA;

use CodeIgniter\Model;

class KaryawanModel extends Model
{
    protected $table = 'hrga_karyawan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'nip', 'nama_lengkap', 'divisi_id', 'jabatan',
        'tanggal_masuk', 'status_karyawan', 'gaji_pokok'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Method untuk mengambil semua karyawan dengan join divisi
    public function getAllKaryawan()
    {
        return $this->findAllWithDivisi();
    }
    
    // Method yang sudah ada
    public function findAllWithDivisi()
    {
        return $this->db->table('hrga_karyawan k')
            ->select('k.*, d.nama_divisi, u.username, u.email')
            ->join('divisi d', 'd.id = k.divisi_id', 'left')
            ->join('users u', 'u.id = k.user_id', 'left') // Join dengan users
            ->orderBy('k.nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    // Method untuk mengambil data divisi
    public function getDivisi()
    {
        return $this->db->table('divisi')
            ->orderBy('nama_divisi', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    // Method baru: untuk sync data dari users ke hrga_karyawan
    public function syncUserToKaryawan($userId)
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Cek apakah karyawan sudah ada berdasarkan user_id
        $existingKaryawan = $this->where('user_id', $userId)->first();
        
        if (!$existingKaryawan) {
            // Generate NIP otomatis jika belum ada
            $nip = 'NIP' . str_pad($userId, 6, '0', STR_PAD_LEFT);
            
            $data = [
                'user_id' => $userId,
                'nip' => $nip,
                'nama_lengkap' => $user['nama_lengkap'],
                'divisi_id' => $user['divisi_id'] ?? 1,
                'jabatan' => $user['role'] ?? 'staff',
                'tanggal_masuk' => date('Y-m-d'),
                'status_karyawan' => 'probation',
                'gaji_pokok' => 0.00
            ];
            
            return $this->insert($data);
        }
        
        return false;
    }
    
    // Method untuk mengambil karyawan berdasarkan user_id
    public function getKaryawanByUserId($userId)
    {
        return $this->where('user_id', $userId)->first();
    }
    
    // Method untuk mengambil semua users yang belum memiliki data karyawan
    public function getUsersWithoutKaryawan()
    {
        return $this->db->table('users u')
            ->select('u.*')
            ->join('hrga_karyawan k', 'k.user_id = u.id', 'left')
            ->where('k.id IS NULL')
            ->get()
            ->getResultArray();
    }
    
    // Method untuk update data karyawan dari user
    public function updateFromUser($userId, $userData)
    {
        $karyawan = $this->where('user_id', $userId)->first();
        
        if ($karyawan) {
            $updateData = [
                'nama_lengkap' => $userData['nama_lengkap'] ?? $karyawan['nama_lengkap'],
                'divisi_id' => $userData['divisi_id'] ?? $karyawan['divisi_id'],
                'jabatan' => $userData['role'] ?? $karyawan['jabatan'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->update($karyawan['id'], $updateData);
        }
        
        return false;
    }
}