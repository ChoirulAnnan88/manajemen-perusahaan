<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['username', 'email', 'password', 'nama_lengkap', 'alamat', 'telepon', 'role'];

    // Validation
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
        'email'    => 'required|valid_email|max_length[255]|is_unique[users.email]',
        'password' => 'required|min_length[3]',
        'nama_lengkap' => 'permit_empty|max_length[255]',
        'alamat'   => 'permit_empty',
        'telepon'  => 'permit_empty|max_length[20]',
        'role'     => 'required|in_list[admin,customer]'
    ];

    protected $validationMessages = [
        'username' => [
            'is_unique' => 'Username sudah digunakan, silakan pilih username lain.'
        ],
        'email' => [
            'is_unique' => 'Email sudah terdaftar, silakan gunakan email lain.'
        ],
        'role' => [
            'in_list' => 'Role harus Admin atau Customer.'
        ]
    ];

    protected $skipValidation = false;

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Cek apakah username sudah ada (untuk custom validation)
     */
    public function usernameExists($username, $exceptId = null)
    {
        $builder = $this->builder();
        $builder->where('username', $username);
        
        if ($exceptId) {
            $builder->where('id !=', $exceptId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Cek apakah email sudah ada (untuk custom validation)
     */
    public function emailExists($email, $exceptId = null)
    {
        $builder = $this->builder();
        $builder->where('email', $email);
        
        if ($exceptId) {
            $builder->where('id !=', $exceptId);
        }
        
        return $builder->countAllResults() > 0;
    }
}