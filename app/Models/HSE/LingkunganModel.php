<?php
namespace App\Models\HSE;

use CodeIgniter\Model;


class LingkunganModel extends Model
{
    protected $table = 'hse_lingkungan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tanggal_pantau', 'parameter', 'nilai_ukur', 
                               'satuan', 'batas_normal', 'status', 'pengukur_id', 'lokasi_id', 'catatan_perubahan', 'alasan_hapus', 'dihapus_oleh', 'dihapus_pada'];
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
            ->where('l.dihapus_pada IS NULL')
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
                   ->where('dihapus_pada IS NULL')
                   ->orderBy('tanggal_pantau', 'DESC')
                   ->findAll();
    }
    
    public function getLingkunganStats($startDate = null, $endDate = null)
    {
        $builder = $this->where('dihapus_pada IS NULL');
        
        if ($startDate && $endDate) {
            $builder->where('tanggal_pantau >=', $startDate)
                   ->where('tanggal_pantau <=', $endDate);
        }
        
        return [
            'total' => $builder->countAllResults(),
            'normal' => $builder->where('status', 'normal')->countAllResults(),
            'melebihi_batas' => $builder->where('status', 'melebihi_batas')->countAllResults(),
            'by_parameter' => $builder->select('parameter, COUNT(*) as total, 
                                            SUM(CASE WHEN status = "melebihi_batas" THEN 1 ELSE 0 END) as melebihi')
                                  ->groupBy('parameter')
                                  ->orderBy('parameter')
                                  ->get()
                                  ->getResultArray(),
            'trend' => $builder->select("DATE_FORMAT(tanggal_pantau, '%Y-%m') as bulan, parameter, 
                                     AVG(nilai_ukur) as rata_rata, 
                                     SUM(CASE WHEN status = 'melebihi_batas' THEN 1 ELSE 0 END) as melebihi")
                          ->groupBy("DATE_FORMAT(tanggal_pantau, '%Y-%m'), parameter")
                          ->orderBy('bulan', 'DESC')
                          ->limit(12)
                          ->get()
                          ->getResultArray(),
        ];
    }

    public function getLingkunganWithFilter($filters = [])
    {
        $builder = $this->db->table('hse_lingkungan l')
            ->select('l.*, u.nama_lengkap as pengukur_nama, d.nama_divisi as lokasi_divisi')
            ->join('users u', 'u.id = l.pengukur_id', 'left')
            ->join('divisi d', 'd.id = l.lokasi_id', 'left')
            ->where('l.dihapus_pada IS NULL')
            ->orderBy('l.tanggal_pantau', 'DESC')
            ->orderBy('l.created_at', 'DESC');
        
        if (!empty($filters['parameter'])) {
            $builder->where('l.parameter', $filters['parameter']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('l.status', $filters['status']);
        }
        
        if (!empty($filters['lokasi_id'])) {
            $builder->where('l.lokasi_id', $filters['lokasi_id']);
        }
        
        if (!empty($filters['pengukur_id'])) {
            $builder->where('l.pengukur_id', $filters['pengukur_id']);
        }
        
        if (!empty($filters['start_date'])) {
            $builder->where('l.tanggal_pantau >=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $builder->where('l.tanggal_pantau <=', $filters['end_date']);
        }
        
        return $builder->get()->getResultArray();
    }

    public function getParameterStats()
    {
        return $this->where('dihapus_pada IS NULL')
                    ->select('parameter, COUNT(*) as total, 
                            SUM(CASE WHEN status = "normal" THEN 1 ELSE 0 END) as normal,
                            SUM(CASE WHEN status = "melebihi_batas" THEN 1 ELSE 0 END) as melebihi,
                            AVG(nilai_ukur) as rata_rata')
                    ->groupBy('parameter')
                    ->orderBy('parameter')
                    ->get()
                    ->getResultArray();
    }

    public function checkDuplicate($tanggal, $parameter, $lokasiId, $excludeId = null)
    {
        $builder = $this->where('tanggal_pantau', $tanggal)
                    ->where('parameter', $parameter)
                    ->where('lokasi_id', $lokasiId)
                    ->where('dihapus_pada IS NULL');
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }
    
    public function getLatestMeasurements($limit = 10, $parameter = null)
    {
        $builder = $this->db->table('hse_lingkungan l')
            ->select('l.*, u.nama_lengkap as pengukur_nama, d.nama_divisi as lokasi')
            ->join('users u', 'u.id = l.pengukur_id', 'left')
            ->join('divisi d', 'd.id = l.lokasi_id', 'left')
            ->where('l.dihapus_pada IS NULL')
            ->orderBy('l.tanggal_pantau', 'DESC')
            ->limit($limit);
        
        if ($parameter) {
            $builder->where('l.parameter', $parameter);
        }
        
        return $builder->get()->getResultArray();
    }
    
    // =============== TAMBAHKAN KEMBALI METHOD INI ===============
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
    
    // =============== TAMBAHKAN KEMBALI METHOD INI ===============
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
        return $this->db->table('hse_lingkungan l')
            ->select("l.parameter, MAX(l.tanggal_pantau) as last_measured")
            ->where('l.dihapus_pada IS NULL')
            ->groupBy('l.parameter')
            ->orderBy('l.parameter')
            ->get()
            ->getResultArray();
    }
    
    public function getParameterOptionsWithDefault()
    {
        $parameters = $this->where('dihapus_pada IS NULL')
                          ->distinct()
                          ->select('parameter')
                          ->orderBy('parameter')
                          ->findAll();
        
        if (empty($parameters)) {
            return [
                ['parameter' => 'Suhu', 'last_measured' => null],
                ['parameter' => 'Kelembaban', 'last_measured' => null],
                ['parameter' => 'Kebisingan', 'last_measured' => null],
                ['parameter' => 'Pencahayaan', 'last_measured' => null],
                ['parameter' => 'Debu', 'last_measured' => null],
                ['parameter' => 'CO', 'last_measured' => null],
                ['parameter' => 'CO2', 'last_measured' => null],
                ['parameter' => 'O2', 'last_measured' => null],
                ['parameter' => 'H2S', 'last_measured' => null],
                ['parameter' => 'NH3', 'last_measured' => null],
                ['parameter' => 'pH', 'last_measured' => null],
                ['parameter' => 'COD', 'last_measured' => null],
                ['parameter' => 'BOD', 'last_measured' => null],
                ['parameter' => 'TSS', 'last_measured' => null],
                ['parameter' => 'Minyak & Lemak', 'last_measured' => null]
            ];
        }
        
        $result = [];
        foreach ($parameters as $param) {
            $lastMeasured = $this->select('tanggal_pantau')
                                ->where('parameter', $param['parameter'])
                                ->where('dihapus_pada IS NULL')
                                ->orderBy('tanggal_pantau', 'DESC')
                                ->first();
            
            $result[] = [
                'parameter' => $param['parameter'],
                'last_measured' => $lastMeasured ? date('d M Y', strtotime($lastMeasured['tanggal_pantau'])) : null
            ];
        }
        
        return $result;
    }
    
    public function getDeletedData()
    {
        return $this->db->table('hse_lingkungan l')
            ->select('l.*, u.nama_lengkap as pengukur_nama, d.nama_divisi as lokasi_divisi, 
                     u2.nama_lengkap as penghapus_nama')
            ->join('users u', 'u.id = l.pengukur_id', 'left')
            ->join('users u2', 'u2.id = l.dihapus_oleh', 'left')
            ->join('divisi d', 'd.id = l.lokasi_id', 'left')
            ->where('l.dihapus_pada IS NOT NULL')
            ->orderBy('l.dihapus_pada', 'DESC')
            ->get()
            ->getResultArray();
    }
}