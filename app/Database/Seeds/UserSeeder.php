<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Password: password123 (hashed)
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@perusahaan.com',
                'password' => $password,
                'nama_lengkap' => 'Administrator System',
                'divisi_id' => 1, // HRGA
                'role' => 'admin',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'manager_hrga',
                'email' => 'hrga@perusahaan.com',
                'password' => $password,
                'nama_lengkap' => 'Manager HRGA',
                'divisi_id' => 1, // HRGA
                'role' => 'manager',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'staff_hse',
                'email' => 'hse@perusahaan.com',
                'password' => $password,
                'nama_lengkap' => 'Staff HSE',
                'divisi_id' => 2, // HSE
                'role' => 'staff',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('users')->insertBatch($data);
    }
}