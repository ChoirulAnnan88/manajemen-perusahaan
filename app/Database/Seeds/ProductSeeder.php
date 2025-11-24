<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'kategori_id' => 1,
                'nama_produk' => 'Smartphone Android',
                'slug' => 'smartphone-android',
                'deskripsi' => 'Smartphone Android dengan spesifikasi tinggi dan kamera berkualitas',
                'harga' => 2500000,
                'stok' => 15,
                'gambar' => 'smartphone.jpg',
                'berat' => 0.3,
                'diskon' => 10,
                'is_active' => true,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'kategori_id' => 1,
                'nama_produk' => 'Laptop Gaming',
                'slug' => 'laptop-gaming',
                'deskripsi' => 'Laptop gaming dengan processor terbaru dan GPU powerful',
                'harga' => 12000000,
                'stok' => 8,
                'gambar' => 'laptop.jpg',
                'berat' => 2.5,
                'diskon' => 0,
                'is_active' => true,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'kategori_id' => 2,
                'nama_produk' => 'Kaos Polos Premium',
                'slug' => 'kaos-polos-premium',
                'deskripsi' => 'Kaos polos berbahan cotton combed 30s yang nyaman dipakai',
                'harga' => 75000,
                'stok' => 50,
                'gambar' => 'kaos.jpg',
                'berat' => 0.2,
                'diskon' => 15,
                'is_active' => true,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'kategori_id' => 3,
                'nama_produk' => 'Blender Multifungsi',
                'slug' => 'blender-multifungsi',
                'deskripsi' => 'Blender dengan 5 kecepatan dan gelas kaca yang aman',
                'harga' => 450000,
                'stok' => 12,
                'gambar' => 'blender.jpg',
                'berat' => 1.8,
                'diskon' => 5,
                'is_active' => true,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ]
        ];

        $this->db->table('products')->insertBatch($data);
    }
}