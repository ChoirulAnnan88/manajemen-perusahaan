<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Hapus data existing
        $this->db->table('users')->emptyTable();

        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@tokoonline.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Administrator Toko',
                'alamat' => 'Jl. Administrasi No. 1, Jakarta',
                'telepon' => '081234567890',
                'role' => 'admin',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'username' => 'customer1',
                'email' => 'customer1@example.com',
                'password' => password_hash('customer123', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Budi Santoso',
                'alamat' => 'Jl. Customer No. 123, Surabaya',
                'telepon' => '081234567891',
                'role' => 'customer',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]
        ];

        $this->db->table('users')->insertBatch($data);
        
        echo "UserSeeder berhasil dijalankan! Password: admin123 / customer123\n";
    }
}