<?php
namespace App\Models\HSE;

use CodeIgniter\Model;

class InsidenModel extends Model
{
    protected $table = 'hse_insiden';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nomor_laporan', 'tanggal_kejadian', 'lokasi', 'jenis_insiden', 
                               'deskripsi', 'tindakan', 'status', 'karyawan_id', 'pelapor_id'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    
    // Relationships
    protected function initialize()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function getInsidenWithRelations($id = null)
    {
        $builder = $this->db->table('hse_insiden i')
            ->select('i.*, k.nama_lengkap as nama_karyawan, k.nip, d.nama_divisi, 
                     u_pelapor.username as pelapor_username, u_pelapor.nama_lengkap as pelapor_nama')
            ->join('hrga_karyawan k', 'k.id = i.karyawan_id', 'left')
            ->join('divisi d', 'd.id = k.divisi_id', 'left')
            ->join('users u_pelapor', 'u_pelapor.id = i.pelapor_id', 'left')
            ->orderBy('i.tanggal_kejadian', 'DESC');
        
        if ($id) {
            $builder->where('i.id', $id);
            return $builder->get()->getRowArray();
        }
        
        return $builder->get()->getResultArray();
    }
    
    public function getInsidenByDivisi($divisiId)
    {
        return $this->db->table('hse_insiden i')
            ->select('i.*, k.nama_lengkap, k.nip, d.nama_divisi')
            ->join('hrga_karyawan k', 'k.id = i.karyawan_id')
            ->join('divisi d', 'd.id = k.divisi_id')
            ->where('k.divisi_id', $divisiId)
            ->orderBy('i.tanggal_kejadian', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    public function generateNomorLaporan()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'INS-' . $year . $month . '-';
        
        $last = $this->select('nomor_laporan')
                    ->like('nomor_laporan', $prefix)
                    ->orderBy('id', 'DESC')
                    ->first();
        
        if ($last) {
            $lastNumber = (int) substr($last['nomor_laporan'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    public function getInsidenStats($userId = null, $divisiId = null)
    {
        $builder = $this->db->table('hse_insiden i');
        
        if ($userId) {
            $builder->where('i.pelapor_id', $userId);
        }
        
        if ($divisiId) {
            $builder->join('hrga_karyawan k', 'k.id = i.karyawan_id')
                    ->where('k.divisi_id', $divisiId);
        }
        
        $total = $builder->countAllResults();
        
        return [
            'total' => $total,
            'dilaporkan' => $this->where('status', 'dilaporkan')->countAllResults(),
            'investigasi' => $this->where('status', 'investigasi')->countAllResults(),
            'selesai' => $this->where('status', 'selesai')->countAllResults(),
            'by_jenis' => $this->select('jenis_insiden, COUNT(*) as jumlah')
                              ->groupBy('jenis_insiden')
                              ->get()
                              ->getResultArray(),
            'by_month' => $this->select("DATE_FORMAT(tanggal_kejadian, '%Y-%m') as bulan, COUNT(*) as jumlah")
                              ->groupBy("DATE_FORMAT(tanggal_kejadian, '%Y-%m')")
                              ->orderBy('bulan', 'DESC')
                              ->limit(6)
                              ->get()
                              ->getResultArray(),
        ];
    }
    
    public function getPelaporOptions()
    {
        return $this->db->table('users u')
            ->select('u.id, u.username, u.nama_lengkap, u.divisi_id, d.nama_divisi')
            ->join('divisi d', 'd.id = u.divisi_id', 'left')
            ->where('u.is_active', 1)
            ->orderBy('u.nama_lengkap')
            ->get()
            ->getResultArray();
    }
    
    public function getKaryawanOptions($divisiId = null)
    {
        $builder = $this->db->table('hrga_karyawan k')
            ->select('k.id, k.nip, k.nama_lengkap, k.divisi_id, d.nama_divisi, k.jabatan')
            ->join('divisi d', 'd.id = k.divisi_id', 'left');
        
        if ($divisiId) {
            $builder->where('k.divisi_id', $divisiId);
        }
        
        return $builder->orderBy('k.nama_lengkap')
                      ->get()
                      ->getResultArray();
    }
}