<?php
namespace App\Models;

use CodeIgniter\Model;

class HrgaInventarisModel extends Model
{
    protected $table = 'hrga_inventaris';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kode_inventaris', 'nama_barang', 'kategori', 'jumlah',
        'kondisi', 'lokasi', 'created_at'
    ];
    
    // HAPUS useTimestamps karena tabel tidak ada updated_at
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $dateFormat = 'datetime';
    
    public function getInventarisAktif()
    {
        return $this->findAll();
    }
    
    public function getByKode($kode_inventaris)
    {
        return $this->where('kode_inventaris', $kode_inventaris)->first();
    }
    
    public function getByKategori($kategori)
    {
        return $this->where('kategori', $kategori)->findAll();
    }
    
    public function getByKondisi($kondisi)
    {
        return $this->where('kondisi', $kondisi)->findAll();
    }
    
    public function updateJumlah($id, $jumlah)
    {
        return $this->update($id, ['jumlah' => $jumlah]);
    }

    // FIX: Generate kode inventaris otomatis
    public function generateKodeInventaris()
    {
        $prefix = 'INV';
        $lastItem = $this->orderBy('id', 'DESC')->first();
        
        if ($lastItem) {
            $lastCode = $lastItem['kode_inventaris'];
            $lastNumber = intval(substr($lastCode, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}