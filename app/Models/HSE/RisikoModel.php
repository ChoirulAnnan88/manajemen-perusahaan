<?php
namespace App\Models\HSE;

use CodeIgniter\Model;

class RisikoModel extends Model
{
    protected $table = 'hse_risiko';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode_risiko', 'deskripsi', 'lokasi', 'tingkat_risiko', 
                               'tindakan_pengendalian', 'status', 'penanggung_jawab_id', 'divisi_id'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    
    protected function initialize()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function getRisikoWithRelations($id = null)
    {
        $builder = $this->db->table('hse_risiko r')
            ->select('r.*, d.nama_divisi, u.nama_lengkap as penanggung_jawab_nama')
            ->join('divisi d', 'd.id = r.divisi_id', 'left')
            ->join('users u', 'u.id = r.penanggung_jawab_id', 'left')
            ->orderBy('r.tingkat_risiko', 'DESC')
            ->orderBy('r.created_at', 'DESC');
        
        if ($id) {
            $builder->where('r.id', $id);
            return $builder->get()->getRowArray();
        }
        
        return $builder->get()->getResultArray();
    }
    
    public function generateKodeRisiko()
    {
        $prefix = 'RSK-' . date('Ym') . '-';
        $last = $this->select('kode_risiko')
                    ->like('kode_risiko', $prefix)
                    ->orderBy('id', 'DESC')
                    ->first();
        
        if ($last) {
            $lastNumber = (int) substr($last['kode_risiko'], -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
    
    public function getRisikoByPenanggungJawab($userId)
    {
        return $this->where('penanggung_jawab_id', $userId)
                   ->orderBy('tingkat_risiko', 'DESC')
                   ->findAll();
    }
    
    public function getRisikoStats($divisiId = null)
    {
        $builder = $this;
        
        if ($divisiId) {
            $builder->where('divisi_id', $divisiId);
        }
        
        return [
            'total' => $builder->countAllResults(),
            'open' => $builder->where('status', 'open')->countAllResults(),
            'closed' => $builder->where('status', 'closed')->countAllResults(),
            'by_tingkat' => $this->select('tingkat_risiko, COUNT(*) as jumlah')
                                ->groupBy('tingkat_risiko')
                                ->orderBy('FIELD(tingkat_risiko, "sangat_tinggi", "tinggi", "sedang", "rendah")')
                                ->get()
                                ->getResultArray(),
            'by_divisi' => $this->db->table('hse_risiko r')
                ->select('d.nama_divisi, COUNT(r.id) as jumlah')
                ->join('divisi d', 'd.id = r.divisi_id', 'left')
                ->groupBy('r.divisi_id')
                ->get()
                ->getResultArray(),
        ];
    }
    
    public function getDivisiOptions()
    {
        return $this->db->table('divisi')
            ->select('id, kode_divisi, nama_divisi')
            ->orderBy('nama_divisi')
            ->get()
            ->getResultArray();
    }
    
    public function getPenanggungJawabOptions()
    {
        return $this->db->table('users u')
            ->select('u.id, u.username, u.nama_lengkap, u.divisi_id, d.nama_divisi')
            ->join('divisi d', 'd.id = u.divisi_id', 'left')
            ->where('u.is_active', 1)
            ->whereIn('u.role', ['manager', 'staff'])
            ->orderBy('u.nama_lengkap')
            ->get()
            ->getResultArray();
    }
}