<?php
namespace App\Models\HSE;

use CodeIgniter\Model;

class InsidenModel extends Model
{
    protected $table = 'hse_insiden';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'nomor_laporan', 
        'tanggal_kejadian', 
        'lokasi', 
        'jenis_insiden', 
        'deskripsi', 
        'tindakan', 
        'status',
        'pelapor_id', 
        'karyawan_id'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Validasi
    protected $validationRules = [
        'nomor_laporan' => 'required',
        'tanggal_kejadian' => 'required',
        'lokasi' => 'required',
        'jenis_insiden' => 'required',
        'deskripsi' => 'required',
        'status' => 'required'
    ];
    
    protected $validationMessages = [];
    protected $skipValidation = false;
    
    // Generate nomor laporan
    public function generateNomorLaporan()
    {
        $year = date('Y');
        $month = date('m');
        
        // Cari nomor terakhir untuk bulan ini
        $last = $this->like('nomor_laporan', 'INS/' . $year . '/' . $month . '/')
                    ->orderBy('id', 'DESC')
                    ->first();
        
        if ($last) {
            $lastNumber = (int) substr($last['nomor_laporan'], -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'INS/' . $year . '/' . $month . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    // Get data dengan relasi (DIPERBAIKI - hapus nip)
    public function getInsidenWithRelations($id = null)
    {
        $builder = $this->db->table('hse_insiden i');
        $builder->select('i.*, 
                         u_pelapor.nama_lengkap as pelapor_nama, 
                         u_pelapor.username as pelapor_username,
                         u_karyawan.nama_lengkap as nama_karyawan,
                         u_karyawan.username as username_karyawan,
                         d.nama_divisi');
        
        $builder->join('users u_pelapor', 'u_pelapor.id = i.pelapor_id', 'left');
        $builder->join('users u_karyawan', 'u_karyawan.id = i.karyawan_id', 'left');
        $builder->join('divisi d', 'd.id = u_karyawan.divisi_id', 'left');
        
        if ($id) {
            $builder->where('i.id', $id);
            return $builder->get()->getRowArray();
        }
        
        $builder->orderBy('i.tanggal_kejadian', 'DESC');
        return $builder->get()->getResultArray();
    }
    
    // Get insiden by divisi (DIPERBAIKI - hapus nip)
    public function getInsidenByDivisi($divisiId)
    {
        $builder = $this->db->table('hse_insiden i');
        $builder->select('i.*, 
                         u_pelapor.nama_lengkap as pelapor_nama, 
                         u_pelapor.username as pelapor_username,
                         u_karyawan.nama_lengkap as nama_karyawan,
                         u_karyawan.username as username_karyawan,
                         d.nama_divisi');
        
        $builder->join('users u_pelapor', 'u_pelapor.id = i.pelapor_id', 'left');
        $builder->join('users u_karyawan', 'u_karyawan.id = i.karyawan_id', 'left');
        $builder->join('divisi d', 'd.id = u_karyawan.divisi_id', 'left');
        $builder->groupStart()
               ->where('u_karyawan.divisi_id', $divisiId)
               ->orWhere('u_pelapor.divisi_id', $divisiId)
               ->groupEnd();
        
        $builder->orderBy('i.tanggal_kejadian', 'DESC');
        return $builder->get()->getResultArray();
    }
    
    // Get stats untuk dashboard
    public function getInsidenStats($userId, $divisiId)
    {
        $session = session();
        $userRole = $session->get('role');
        
        $builder = $this;
        
        if ($userRole === 'manager') {
            // Manager lihat semua
        } elseif ($userRole === 'staff') {
            // Staff lihat berdasarkan divisi
            $builder->join('users u', 'u.id = hse_insiden.karyawan_id', 'left')
                   ->where('u.divisi_id', $divisiId);
        } else {
            // Operator hanya lihat laporannya sendiri
            $builder->where('pelapor_id', $userId);
        }
        
        $total = $builder->countAllResults();
        $selesai = $builder->where('status', 'selesai')->countAllResults();
        $investigasi = $builder->where('status', 'investigasi')->countAllResults();
        $dilaporkan = $builder->where('status', 'dilaporkan')->countAllResults();
        
        return [
            'total' => $total,
            'selesai' => $selesai,
            'investigasi' => $investigasi,
            'dilaporkan' => $dilaporkan,
        ];
    }
    
    // Get options untuk dropdown (DIPERBAIKI - hapus nip)
    public function getPelaporOptions()
    {
        $builder = $this->db->table('users u');
        $builder->select('u.id, u.nama_lengkap, u.username, d.nama_divisi');
        $builder->join('divisi d', 'd.id = u.divisi_id', 'left');
        $builder->where('u.is_active', 1);
        $builder->orderBy('u.nama_lengkap', 'ASC');
        
        return $builder->get()->getResultArray();
    }
    
    public function getKaryawanOptions()
    {
        $builder = $this->db->table('users u');
        $builder->select('u.id, u.nama_lengkap, u.username, d.nama_divisi');
        $builder->join('divisi d', 'd.id = u.divisi_id', 'left');
        $builder->where('u.is_active', 1);
        $builder->orderBy('u.nama_lengkap', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}