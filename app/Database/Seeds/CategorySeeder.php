<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama_kategori' => 'Elektronik',
                'slug' => 'elektronik',
                'deskripsi' => 'Produk elektronik terbaru dan berkualitas',
                'created_at' => Time::now(),
            ],
            [
                'nama_kategori' => 'Fashion',
                'slug' => 'fashion',
                'deskripsi' => 'Pakaian dan aksesoris fashion terkini',
                'created_at' => Time::now(),
            ],
            [
                'nama_kategori' => 'Rumah Tangga',
                'slug' => 'rumah-tangga',
                'deskripsi' => 'Kebutuhan rumah tangga lengkap',
                'created_at' => Time::now(),
            ],
            [
                'nama_kategori' => 'Olahraga',
                'slug' => 'olahraga',
                'deskripsi' => 'Perlengkapan olahraga dan fitness',
                'created_at' => Time::now(),
            ]
        ];

        $this->db->table('categories')->insertBatch($data);
    }
}