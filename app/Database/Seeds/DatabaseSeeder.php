<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('UserSeeder');
        $this->call('CategorySeeder');
        $this->call('ProductSeeder');
        
        echo "Semua seeder berhasil dijalankan!\n";
        echo "===============================\n";
        echo "Login Admin: admin@tokoonline.com / admin123\n";
        echo "Login Customer: customer1@example.com / customer123\n";
        echo "===============================\n";
    }
}