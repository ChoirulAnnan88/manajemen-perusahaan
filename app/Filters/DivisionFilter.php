<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DivisionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $userRole = $session->get('role');
        $userDivisi = $session->get('divisi_id');
        $currentURI = $request->getUri()->getPath();

        // Manager bisa akses semua
        if ($userRole === 'manager') {
            return;
        }

        // Staff dan Operator hanya bisa akses divisi mereka
        $allowedDivisions = [
            'hrga' => 1,
            'hse' => 2, 
            'finance' => 3,
            'ppic' => 4,
            'produksi' => 5,
            'marketing' => 6
        ];

        foreach ($allowedDivisions as $division => $divisiId) {
            if (strpos($currentURI, $division) !== false && $userDivisi != $divisiId) {
                return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke divisi ' . ucfirst($division));
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}