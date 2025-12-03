<?php
namespace App\Models\HSE;

use CodeIgniter\Model;

class LingkunganModel extends Model
{
    protected $table = 'hse_lingkungan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tanggal_pantau', 'parameter', 'nilai_ukur', 
                               'satuan', 'batas_normal', 'status', 'pengukur_id', 'lokasi_id'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    
    protected function initialize()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function getLingkunganWithRelations($id = null)
    {
        $builder = $this->db->table('hse_lingkungan l')
            ->select('l.*, u.nama_lengkap as pengukur_nama, d.nama_divisi as lokasi_divisi')
            ->join('users u', 'u.id = l.pengukur_id', 'left')
            ->join('divisi d', 'd.id = l.lokasi_id', 'left')
            ->orderBy('l.tanggal_pantau', 'DESC')
            ->orderBy('l.created_at', 'DESC');
        
        if ($id) {
            $builder->where('l.id', $id);
            return $builder->get()->getRowArray();
        }
        
        return $builder->get()->getResultArray();
    }
    
    public function getLingkunganByPengukur($userId)
    {
        return $this->where('pengukur_id', $userId)
                   ->orderBy('tanggal_pantau', 'DESC')
                   ->findAll();
    }
    
    public function getLingkunganStats($startDate = null, $endDate = null)
    {
        $builder = $this;
        
        if ($startDate && $endDate) {
            $builder->where('tanggal_pantau >=', $startDate)
                   ->where('tanggal_pantau <=', $endDate);
        }
        
        return [
            'total' => $builder->countAllResults(),
            'normal' => $builder->where('status', 'normal')->countAllResults(),
            'melebihi_batas' => $builder->where('status', 'melebihi_batas')->countAllResults(),
            'by_parameter' => $this->select('parameter, COUNT(*) as total, 
                                            SUM(CASE WHEN status = "melebihi_batas" THEN 1 ELSE 0 END) as melebihi')
                                  ->groupBy('parameter')
                                  ->orderBy('parameter')
                                  ->get()
                                  ->getResultArray(),
            'trend' => $this->select("DATE_FORMAT(tanggal_pantau, '%Y-%m') as bulan, parameter, 
                                     AVG(nilai_ukur) as rata_rata, 
                                     SUM(CASE WHEN status = 'melebihi_batas' THEN 1 ELSE 0 END) as melebihi")
                          ->groupBy("DATE_FORMAT(tanggal_pantau, '%Y-%m'), parameter")
                          ->orderBy('bulan', 'DESC')
                          ->limit(12)
                          ->get()
                          ->getResultArray(),
        ];
    }
    
    public function getLatestMeasurements($limit = 10, $parameter = null)
    {
        $builder = $this->db->table('hse_lingkungan l')
            ->select('l.*, u.nama_lengkap as pengukur_nama, d.nama_divisi as lokasi')
            ->join('users u', 'u.id = l.pengukur_id', 'left')
            ->join('divisi d', 'd.id = l.lokasi_id', 'left')
            ->orderBy('l.tanggal_pantau', 'DESC')
            ->limit($limit);
        
        if ($parameter) {
            $builder->where('l.parameter', $parameter);
        }
        
        return $builder->get()->getResultArray();
    }
    
    public function getPengukurOptions()
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
    
    public function getLokasiOptions()
    {
        return $this->db->table('divisi')
            ->select('id, kode_divisi, nama_divisi')
            ->orderBy('nama_divisi')
            ->get()
            ->getResultArray();
    }
    
    public function getParameterOptions()
    {
        return $this->distinct()
                   ->select('parameter')
                   ->orderBy('parameter')
                   ->findAll();
    }
}