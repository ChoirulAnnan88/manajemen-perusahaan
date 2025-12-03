<?php

namespace App\Controllers\HRGA;

use App\Controllers\BaseController;
use App\Models\HRGA\KaryawanModel;
use App\Models\UserModel;

class KaryawanController extends BaseController
{
    protected $karyawanModel;
    protected $userModel;

    public function __construct()
    {
        $this->karyawanModel = new KaryawanModel();
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Otomatis sync users yang belum memiliki data karyawan
        $this->autoSyncUnlinkedUsers();
        
        $data = [
            'title' => 'Data Karyawan',
            'karyawan' => $this->karyawanModel->getAllKaryawan()
        ];
        return view('hrga/karyawan', $data);
    }
    
    // Method untuk auto-sync users yang belum terlink ke hrga_karyawan
    private function autoSyncUnlinkedUsers()
    {
        $usersWithoutKaryawan = $this->karyawanModel->getUsersWithoutKaryawan();
        
        foreach ($usersWithoutKaryawan as $user) {
            $this->karyawanModel->syncUserToKaryawan($user['id']);
        }
    }

    public function tambah()
    {
        $data = [
            'title' => 'Tambah Data Karyawan',
            'divisi' => $this->karyawanModel->getDivisi(),
            'users' => $this->userModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('hrga/karyawan_tambah', $data);
    }

    public function store()
    {
        // Validasi input
        $rules = [
            'user_id' => [
                'rules' => 'required|numeric|is_unique[hrga_karyawan.user_id]',
                'errors' => [
                    'required' => 'User harus dipilih',
                    'numeric' => 'User tidak valid',
                    'is_unique' => 'User sudah memiliki data karyawan'
                ]
            ],
            'nip' => [
                'rules' => 'required|is_unique[hrga_karyawan.nip]',
                'errors' => [
                    'required' => 'NIP harus diisi',
                    'is_unique' => 'NIP sudah terdaftar'
                ]
            ],
            'nama_lengkap' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Nama lengkap harus diisi',
                    'min_length' => 'Nama minimal 3 karakter'
                ]
            ],
            'divisi_id' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Divisi harus dipilih',
                    'numeric' => 'Divisi tidak valid'
                ]
            ],
            'jabatan' => [
                'rules' => 'required|in_list[manager,staff,operator]',
                'errors' => [
                    'required' => 'Jabatan harus dipilih',
                    'in_list' => 'Jabatan tidak valid'
                ]
            ],
            'tanggal_masuk' => [
                'rules' => 'required|valid_date',
                'errors' => [
                    'required' => 'Tanggal masuk harus diisi',
                    'valid_date' => 'Format tanggal tidak valid'
                ]
            ],
            'status_karyawan' => [
                'rules' => 'required|in_list[tetap,kontrak,probation]',
                'errors' => [
                    'required' => 'Status karyawan harus dipilih',
                    'in_list' => 'Status tidak valid'
                ]
            ],
            'gaji_pokok' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Gaji pokok harus diisi'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/hrga/karyawan/tambah')
                ->withInput()
                ->with('validation', $this->validator);
        }

        // Format gaji - Hapus titik separator
        $gaji_pokok = $this->request->getPost('gaji_pokok');
        $gaji_pokok = str_replace(['.', ','], '', $gaji_pokok);
        $gaji_pokok = (float) $gaji_pokok;

        // Siapkan data untuk disimpan
        $data = [
            'user_id' => (int) $this->request->getPost('user_id'),
            'nip' => $this->request->getPost('nip'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'divisi_id' => (int) $this->request->getPost('divisi_id'),
            'jabatan' => $this->request->getPost('jabatan'),
            'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
            'status_karyawan' => $this->request->getPost('status_karyawan'),
            'gaji_pokok' => $gaji_pokok
        ];

        try {
            $this->karyawanModel->save($data);
            return redirect()->to('/hrga/karyawan')
                ->with('success', 'Data karyawan berhasil ditambahkan');
                
        } catch (\Exception $e) {
            return redirect()->to('/hrga/karyawan/tambah')
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        
        if (!$karyawan) {
            return redirect()->to('/hrga/karyawan')
                ->with('error', 'Data karyawan tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Data Karyawan',
            'karyawan' => $karyawan,
            'divisi' => $this->karyawanModel->getDivisi(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('hrga/karyawan_edit', $data);
    }

    public function update($id)
    {
        // Cek apakah data ada
        $karyawan = $this->karyawanModel->find($id);
        if (!$karyawan) {
            return redirect()->to('/hrga/karyawan')
                ->with('error', 'Data tidak ditemukan');
        }

        // Validasi
        $rules = [
            'nip' => [
                'rules' => "required|is_unique[hrga_karyawan.nip,id,{$id}]",
                'errors' => [
                    'required' => 'NIP harus diisi',
                    'is_unique' => 'NIP sudah terdaftar'
                ]
            ],
            'nama_lengkap' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'Nama lengkap harus diisi',
                    'min_length' => 'Nama minimal 3 karakter'
                ]
            ],
            'divisi_id' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Divisi harus dipilih',
                    'numeric' => 'Divisi tidak valid'
                ]
            ],
            'jabatan' => [
                'rules' => 'required|in_list[manager,staff,operator]',
                'errors' => [
                    'required' => 'Jabatan harus dipilih',
                    'in_list' => 'Jabatan tidak valid'
                ]
            ],
            'tanggal_masuk' => [
                'rules' => 'required|valid_date',
                'errors' => [
                    'required' => 'Tanggal masuk harus diisi',
                    'valid_date' => 'Format tanggal tidak valid'
                ]
            ],
            'status_karyawan' => [
                'rules' => 'required|in_list[tetap,kontrak,probation]',
                'errors' => [
                    'required' => 'Status karyawan harus dipilih',
                    'in_list' => 'Status tidak valid'
                ]
            ],
            'gaji_pokok' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Gaji pokok harus diisi'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/hrga/karyawan/edit/' . $id)
                ->withInput()
                ->with('validation', $this->validator);
        }

        // Format gaji - Hapus titik separator
        $gaji_pokok = $this->request->getPost('gaji_pokok');
        $gaji_pokok = str_replace(['.', ','], '', $gaji_pokok);
        $gaji_pokok = (float) $gaji_pokok;

        // Update data
        $data = [
            'nip' => $this->request->getPost('nip'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'divisi_id' => (int) $this->request->getPost('divisi_id'),
            'jabatan' => $this->request->getPost('jabatan'),
            'tanggal_masuk' => $this->request->getPost('tanggal_masuk'),
            'status_karyawan' => $this->request->getPost('status_karyawan'),
            'gaji_pokok' => $gaji_pokok
        ];

        try {
            $this->karyawanModel->update($id, $data);
            return redirect()->to('/hrga/karyawan')
                ->with('success', 'Data karyawan berhasil diperbarui');
                
        } catch (\Exception $e) {
            return redirect()->to('/hrga/karyawan/edit/' . $id)
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function detail($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        
        if (!$karyawan) {
            return redirect()->to('/hrga/karyawan')
                ->with('error', 'Data karyawan tidak ditemukan');
        }

        // Ambil data user terkait jika ada
        $user = null;
        if (!empty($karyawan['user_id'])) {
            $user = $this->userModel->find($karyawan['user_id']);
        }

        $data = [
            'title' => 'Detail Karyawan',
            'karyawan' => $karyawan,
            'user' => $user
        ];
        
        return view('hrga/karyawan_detail', $data);
    }

    public function hapus($id)
    {
        $karyawan = $this->karyawanModel->find($id);
        
        if (!$karyawan) {
            return redirect()->to('/hrga/karyawan')
                ->with('error', 'Data karyawan tidak ditemukan');
        }

        try {
            $this->karyawanModel->delete($id);
            return redirect()->to('/hrga/karyawan')
                ->with('success', 'Data karyawan berhasil dihapus');
                
        } catch (\Exception $e) {
            return redirect()->to('/hrga/karyawan')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
    
    public function syncAllUsers()
    {
        $users = $this->userModel->findAll();
        $synced = 0;
        $alreadyExists = 0;
        
        foreach ($users as $user) {
            $existingKaryawan = $this->karyawanModel->where('user_id', $user['id'])->first();
            
            if (!$existingKaryawan) {
                $this->karyawanModel->syncUserToKaryawan($user['id']);
                $synced++;
            } else {
                $alreadyExists++;
            }
        }
        
        return redirect()->to('/hrga/karyawan')
            ->with('success', "Sync selesai: $synced user baru ditambahkan, $alreadyExists sudah ada.");
    }
}