<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DivisiSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'kode_divisi' => 'HRGA',
                'nama_divisi' => 'Human Resources & General Affairs',
                'deskripsi' => 'Mengelola sumber daya manusia dan urusan umum perusahaan',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'kode_divisi' => 'HSE',
                'nama_divisi' => 'Health, Safety & Environment', 
                'deskripsi' => 'Menangani kesehatan, keselamatan, dan lingkungan kerja',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'kode_divisi' => 'FINACC',
                'nama_divisi' => 'Finance & Accounting',
                'deskripsi' => 'Mengelola keuangan dan akuntansi perusahaan',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'kode_divisi' => 'PPIC',
                'nama_divisi' => 'Production Planning & Inventory Control',
                'deskripsi' => 'Perencanaan produksi dan pengendalian persediaan',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'kode_divisi' => 'PROD',
                'nama_divisi' => 'Produksi',
                'deskripsi' => 'Divisi produksi dan operasional manufacturing',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'kode_divisi' => 'MKT',
                'nama_divisi' => 'Marketing',
                'deskripsi' => 'Pemasaran dan penjualan produk perusahaan',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('divisi')->insertBatch($data);
    }
}