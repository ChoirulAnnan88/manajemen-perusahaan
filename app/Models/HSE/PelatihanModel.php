<?php
namespace App\Models\HSE;

use CodeIgniter\Model;

class PelatihanModel extends Model
{
    protected $table = 'hse_pelatihan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['judul_pelatihan', 'tanggal_pelatihan', 'peserta', 
                               'materi', 'hasil', 'instruktur_id', 'divisi_target'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    
    protected function initialize()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function getPelatihanWithRelations($id = null)
    {
        $builder = $this->db->table('hse_pelatihan p')
            ->select('p.*, u.nama_lengkap as instruktur_nama, d.nama_divisi')
            ->join('users u', 'u.id = p.instruktur_id', 'left')
            ->join('divisi d', 'd.id = p.divisi_target', 'left')
            ->orderBy('p.tanggal_pelatihan', 'DESC');
        
        if ($id) {
            $builder->where('p.id', $id);
            return $builder->get()->getRowArray();
        }
        
        return $builder->get()->getResultArray();
    }
    
    public function getPelatihanByPeserta($karyawanId)
    {
        return $this->like('peserta', $karyawanId)
                   ->orderBy('tanggal_pelatihan', 'DESC')
                   ->findAll();
    }
    
    public function parsePesertaIds($pesertaString)
    {
        // Format: "1,2,3" atau "1, 2, 3"
        $ids = array_map('trim', explode(',', $pesertaString));
        $ids = array_filter($ids, 'is_numeric');
        return $ids;
    }
    
    public function getPesertaDetails($pesertaIds)
    {
        if (empty($pesertaIds)) {
            return [];
        }
        
        return $this->db->table('hrga_karyawan k')
            ->select('k.id, k.nip, k.nama_lengkap, k.divisi_id, d.nama_divisi')
            ->join('divisi d', 'd.id = k.divisi_id')
            ->whereIn('k.id', $pesertaIds)
            ->orderBy('k.nama_lengkap')
            ->get()
            ->getResultArray();
    }
    
    public function getPelatihanStats($year = null)
    {
        $builder = $this;
        
        if ($year) {
            $builder->where('YEAR(tanggal_pelatihan)', $year);
        }
        
        return [
            'total' => $builder->countAllResults(),
            'bulan_ini' => $builder->where('MONTH(tanggal_pelatihan)', date('m'))
                                  ->where('YEAR(tanggal_pelatihan)', date('Y'))
                                  ->countAllResults(),
            'by_tahun' => $this->select("YEAR(tanggal_pelatihan) as tahun, COUNT(*) as jumlah")
                              ->groupBy('YEAR(tanggal_pelatihan)')
                              ->orderBy('tahun', 'DESC')
                              ->get()
                              ->getResultArray(),
        ];
    }
    
    public function getInstrukturOptions()
    {
        return $this->db->table('users u')
            ->select('u.id, u.nama_lengkap, u.divisi_id, d.nama_divisi')
            ->join('divisi d', 'd.id = u.divisi_id', 'left')
            ->where('u.is_active', 1)
            ->where('u.divisi_id', 2) // HSE divisi
            ->orderBy('u.nama_lengkap')
            ->get()
            ->getResultArray();
    }
    
    public function getKaryawanForPelatihan($divisiId = null)
    {
        $builder = $this->db->table('hrga_karyawan k')
            ->select('k.id, k.nip, k.nama_lengkap, k.divisi_id, d.nama_divisi')
            ->join('divisi d', 'd.id = k.divisi_id');
        
        if ($divisiId) {
            $builder->where('k.divisi_id', $divisiId);
        }
        
        return $builder->orderBy('k.nama_lengkap')
                      ->get()
                      ->getResultArray();
    }
}