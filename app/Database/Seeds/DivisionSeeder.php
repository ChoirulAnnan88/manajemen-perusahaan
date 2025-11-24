// app/Database/Seeds/DivisionSeeder.php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'division_name' => 'HRGA',
                'division_code' => 'HRGA',
                'description' => 'Human Resources & General Affairs'
            ],
            [
                'division_name' => 'HSE',
                'division_code' => 'HSE', 
                'description' => 'Health, Safety & Environment'
            ],
            [
                'division_name' => 'FINANCE ACCOUNTING',
                'division_code' => 'FINACC',
                'description' => 'Finance & Accounting'
            ],
            [
                'division_name' => 'PPIC',
                'division_code' => 'PPIC',
                'description' => 'Production Planning & Inventory Control'
            ],
            [
                'division_name' => 'PRODUKSI',
                'division_code' => 'PROD',
                'description' => 'Production Department'
            ],
            [
                'division_name' => 'MARKETING',
                'division_code' => 'MKT',
                'description' => 'Marketing & Sales'
            ]
        ];

        $this->db->table('divisions')->insertBatch($data);
    }
}