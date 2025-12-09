<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProduksiSeeder extends Seeder
{
    public function run()
    {
        // Sample data for produksi_hasil
        $produksiData = [
            [
                'nomor_produksi' => 'PROD-240101-001',
                'tanggal_produksi' => '2024-01-01',
                'jumlah_hasil' => 100,
                'kualitas' => 'baik',
                'status_produksi' => 'completed',
                'operator_id' => 1,
                'keterangan' => 'Produksi sample 1'
            ]
        ];
        
        $this->db->table('produksi_hasil')->insertBatch($produksiData);
    }
}