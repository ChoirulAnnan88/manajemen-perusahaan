<?php
namespace App\Models;

use CodeIgniter\Model;

class DivisiModel extends Model
{
    protected $table = 'divisi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode_divisi', 'nama_divisi', 'created_at', 'updated_at'];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    
    public function getDivisiAktif()
    {
        return $this->findAll();
    }
    
    public function getByKode($kode_divisi)
    {
        return $this->where('kode_divisi', $kode_divisi)->first();
    }
    
    public function getDivisiList()
    {
        $result = $this->findAll();
        $divisiList = [];
        foreach ($result as $row) {
            $divisiList[$row['id']] = $row['nama_divisi'];
        }
        return $divisiList;
    }
    
    public function getDivisiForDropdown()
    {
        $result = $this->findAll();
        $options = [];
        foreach ($result as $row) {
            $options[$row['id']] = $row['nama_divisi'] . ' (' . $row['kode_divisi'] . ')';
        }
        return $options;
    }
}