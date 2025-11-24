<?php
namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Sistem Manajemen Perusahaan',
            'divisions' => [
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
            ]
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
}