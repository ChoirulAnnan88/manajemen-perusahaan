<?php
namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Coba ambil data dari database, jika error tetap tampilkan data default
        try {
            $db = \Config\Database::connect();
            
            // Cek apakah tabel divisi ada
            if ($db->tableExists('divisi')) {
                $divisiModel = $db->table('divisi');
                $divisionsData = $divisiModel->get()->getResultArray();
                
                // Format data dari database
                $divisions = [];
                foreach ($divisionsData as $div) {
                    $key = strtolower($div['kode_divisi']);
                    $divisions[$key] = [
                        'code' => $div['kode_divisi'],
                        'name' => $div['nama_divisi'],
                        'color' => $this->getDivisionColor($div['kode_divisi'])
                    ];
                }
            } else {
                // Jika tabel tidak ada, gunakan data default
                $divisions = $this->getDefaultDivisions();
            }
        } catch (\Exception $e) {
            // Jika ada error, gunakan data default
            $divisions = $this->getDefaultDivisions();
        }

        $data = [
            'title' => 'Sistem Manajemen Perusahaan',
            'divisions' => $divisions
        ];
        
        return view('dashboard', $data);
    }

    public function division($division)
    {
        $divisionNames = [
            'hrga' => 'HRGA - Human Resources & General Affairs',
            'hse' => 'HSE - Health, Safety & Environment',
            'finance' => 'FINANCE ACCOUNTING - Finance & Accounting',
            'ppic' => 'PPIC - Production Planning & Inventory Control',
            'produksi' => 'PRODUKSI - Production Department',
            'marketing' => 'MARKETING - Marketing & Sales'
        ];

        $data = [
            'title' => $divisionNames[$division] ?? 'Divisi',
            'division' => $division
        ];

        return view('division_dashboard', $data);
    }

    /**
     * Data default divisions jika database belum siap
     */
    private function getDefaultDivisions()
    {
        return [
            'hrga' => [
                'code' => 'HRGA',
                'name' => 'Human Resources & General Affairs',
                'color' => 'primary'
            ],
            'hse' => [
                'code' => 'HSE', 
                'name' => 'Health, Safety & Environment',
                'color' => 'success'
            ],
            'finance' => [
                'code' => 'FINANCE ACCOUNTING',
                'name' => 'Finance & Accounting',
                'color' => 'info'
            ],
            'ppic' => [
                'code' => 'PPIC',
                'name' => 'Production Planning & Inventory Control',
                'color' => 'warning'
            ],
            'produksi' => [
                'code' => 'PRODUKSI',
                'name' => 'Production Department',
                'color' => 'danger'
            ],
            'marketing' => [
                'code' => 'MARKETING',
                'name' => 'Marketing & Sales',
                'color' => 'dark'
            ]
        ];
    }

    /**
     * Get color for division based on code
     */
    private function getDivisionColor($kodeDivisi)
    {
        $colors = [
            'HRGA' => 'primary',
            'HSE' => 'success', 
            'FINACC' => 'info',
            'PPIC' => 'warning',
            'PROD' => 'danger',
            'MKT' => 'dark',
            'FINANCE ACCOUNTING' => 'info',
            'PRODUKSI' => 'danger',
            'MARKETING' => 'dark'
        ];

        return $colors[$kodeDivisi] ?? 'secondary';
    }
}