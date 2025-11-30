<?php
namespace App\Models;

use CodeIgniter\Model;

class HrgaPerizinanModel extends Model
{
    protected $table = 'hrga_perizinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'karyawan_id', 'jenis_izin', 'tanggal_mulai', 'tanggal_selesai',
        'alasan', 'status', 'created_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $dateFormat = 'datetime';
    
    public function getPerizinanPending()
    {
        return $this->where('status', 'pending')->findAll();
    }
    
    public function getPerizinanKaryawan($karyawan_id)
    {
        return $this->where('karyawan_id', $karyawan_id)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    public function getPerizinanPeriode($start_date, $end_date)
    {
        return $this->where('tanggal_mulai >=', $start_date)
                    ->where('tanggal_selesai <=', $end_date)
                    ->findAll();
    }
    
    public function getPerizinanByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }
    
    public function approvePerizinan($id)
    {
        return $this->update($id, ['status' => 'approved']);
    }
    
    public function rejectPerizinan($id)
    {
        return $this->update($id, ['status' => 'rejected']);
    }

    // FIX: Tambahkan method untuk join dengan karyawan
    public function getPerizinanWithKaryawan()
    {
        return $this->select('hrga_perizinan.*, hrga_karyawan.nama_lengkap, hrga_karyawan.nip')
                    ->join('hrga_karyawan', 'hrga_karyawan.id = hrga_perizinan.karyawan_id')
                    ->orderBy('hrga_perizinan.created_at', 'DESC')
                    ->findAll();
    }

    public function getPerizinanPendingWithKaryawan()
    {
        return $this->select('hrga_perizinan.*, hrga_karyawan.nama_lengkap, hrga_karyawan.nip')
                    ->join('hrga_karyawan', 'hrga_karyawan.id = hrga_perizinan.karyawan_id')
                    ->where('hrga_perizinan.status', 'pending')
                    ->orderBy('hrga_perizinan.created_at', 'DESC')
                    ->findAll();
    }
}