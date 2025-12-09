<?php

namespace App\Models\PRODUKSI;

use CodeIgniter\Model;

class PpicProduksiModel extends Model
{
    protected $table = 'ppic_produksi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nomor_plan',
        'produk',
        'jumlah_target',
        'jumlah_hasil',
        'persentase_selesai',
        'status',
        'status_produksi'
    ];
    
    /**
     * Get available PPIC plans for production
     */
    public function getAvailablePlans()
    {
        return $this->whereIn('status', ['planned', 'progress'])
                   ->orderBy('tanggal_mulai', 'DESC')
                   ->findAll();
    }
    
    /**
     * Update PPIC progress
     */
    public function updateProgress($id, $jumlahHasil)
    {
        $plan = $this->find($id);
        if (!$plan) return false;
        
        $newJumlahHasil = $plan['jumlah_hasil'] + $jumlahHasil;
        $persentase = ($newJumlahHasil / $plan['jumlah_target']) * 100;
        
        $data = [
            'jumlah_hasil' => $newJumlahHasil,
            'persentase_selesai' => min($persentase, 100)
        ];
        
        if ($persentase >= 100) {
            $data['status'] = 'completed';
            $data['status_produksi'] = 'selesai';
        } else {
            $data['status'] = 'progress';
            $data['status_produksi'] = 'proses';
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Rollback PPIC progress
     */
    public function rollbackProgress($id, $jumlahHasil)
    {
        $plan = $this->find($id);
        if (!$plan) return false;
        
        $newJumlahHasil = max(0, $plan['jumlah_hasil'] - $jumlahHasil);
        $persentase = ($newJumlahHasil / $plan['jumlah_target']) * 100;
        
        $data = [
            'jumlah_hasil' => $newJumlahHasil,
            'persentase_selesai' => $persentase
        ];
        
        if ($newJumlahHasil <= 0) {
            $data['status'] = 'planned';
            $data['status_produksi'] = 'menunggu';
        } elseif ($persentase >= 100) {
            $data['status'] = 'completed';
            $data['status_produksi'] = 'selesai';
        } else {
            $data['status'] = 'progress';
            $data['status_produksi'] = 'proses';
        }
        
        return $this->update($id, $data);
    }
}