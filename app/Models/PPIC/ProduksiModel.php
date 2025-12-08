<?php
namespace App\Models\PPIC;

use CodeIgniter\Model;

class ProduksiModel extends Model
{
    protected $table = 'ppic_produksi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nomor_plan', 'nomor_produksi', 'produk_id', 'operator_id', 'alat_id',
        'produk', 'jumlah_target', 'jumlah_hasil', 'kualitas', 'persentase_selesai',
        'material_terpakai', 'biaya_produksi', 'tanggal_mulai', 'tanggal_selesai',
        'tanggal_produksi', 'status', 'status_produksi', 'keterangan'
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';
    protected $dateFormat = 'datetime';

    /**
     * Get all active production data (exclude soft deleted)
     */
    public function getAllProduksi()
    {
        return $this->where('deleted_at IS NULL')
                    ->orderBy('tanggal_produksi', 'DESC')
                    ->findAll();
    }

    /**
     * Get only deleted production data
     */
    public function getDeletedProduksi()
    {
        return $this->where('deleted_at IS NOT NULL')
                    ->orderBy('deleted_at', 'DESC')
                    ->findAll();
    }

    /**
     * Restore soft deleted data
     */
    public function restore($id)
    {
        return $this->protect(false)
                    ->update($id, ['deleted_at' => null]);
    }

    public function getProduksiHariIni()
    {
        $today = date('Y-m-d');
        return $this->where('deleted_at IS NULL')
                   ->where('tanggal_produksi', $today)
                   ->findAll();
    }

    public function getProduksiByStatus($status)
    {
        return $this->where('deleted_at IS NULL')
                   ->where('status_produksi', $status)
                   ->findAll();
    }

    public function getProduksiByDateRange($startDate, $endDate)
    {
        return $this->where('deleted_at IS NULL')
                   ->where('tanggal_produksi >=', $startDate)
                   ->where('tanggal_produksi <=', $endDate)
                   ->orderBy('tanggal_produksi', 'DESC')
                   ->findAll();
    }

    public function getProduksiSummary()
    {
        return $this->where('deleted_at IS NULL')
                   ->select('COUNT(*) as total_produksi, SUM(jumlah_hasil) as total_unit, AVG(persentase_selesai) as rata_rata_selesai')
                   ->where('status_produksi', 'selesai')
                   ->first();
    }

    /**
     * Get production statistics for dashboard
     */
    public function getProduksiStats()
    {
        $stats = $this->where('deleted_at IS NULL')
                     ->select("
                         COUNT(*) as total,
                         SUM(CASE WHEN status_produksi = 'proses' THEN 1 ELSE 0 END) as in_progress,
                         SUM(CASE WHEN status_produksi = 'selesai' THEN 1 ELSE 0 END) as completed,
                         SUM(CASE WHEN status_produksi = 'menunggu' THEN 1 ELSE 0 END) as waiting,
                         AVG(persentase_selesai) as avg_progress
                     ")
                     ->first();
        
        return [
            'total' => $stats['total'] ?? 0,
            'in_progress' => $stats['in_progress'] ?? 0,
            'completed' => $stats['completed'] ?? 0,
            'waiting' => $stats['waiting'] ?? 0,
            'avg_progress' => round($stats['avg_progress'] ?? 0, 1)
        ];
    }

    public function generateNomorProduksi()
    {
        $prefix = 'PROD-' . date('Ym') . '-';
        
        // Cari nomor terakhir dengan prefix bulan ini (hanya yang aktif)
        $last = $this->where('deleted_at IS NULL')
                    ->like('nomor_produksi', $prefix, 'after')
                    ->orderBy('nomor_produksi', 'DESC')
                    ->first();
        
        if ($last) {
            $lastNumber = (int) substr($last['nomor_produksi'], strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function hitungPersentaseSelesai($id)
    {
        $produksi = $this->where('deleted_at IS NULL')
                        ->find($id);
        
        if ($produksi && $produksi['jumlah_target'] > 0) {
            $persentase = ($produksi['jumlah_hasil'] / $produksi['jumlah_target']) * 100;
            return min(100, round($persentase));
        }
        return 0;
    }

    /**
     * Auto-generate production number when creating new record
     */
    public function insert($data = null, bool $returnID = true)
    {
        // Auto-generate nomor_produksi jika kosong
        if (empty($data['nomor_produksi'])) {
            $data['nomor_produksi'] = $this->generateNomorProduksi();
        }
        
        return parent::insert($data, $returnID);
    }

    /**
     * Update persentase_selesai automatically
     */
    public function update($id = null, $data = null): bool
    {
        // Auto-calculate persentase_selesai jika jumlah_target dan jumlah_hasil ada
        if (isset($data['jumlah_target']) && isset($data['jumlah_hasil'])) {
            if ($data['jumlah_target'] > 0) {
                $data['persentase_selesai'] = min(100, 
                    round(($data['jumlah_hasil'] / $data['jumlah_target']) * 100)
                );
            } else {
                $data['persentase_selesai'] = 0;
            }
        }
        
        return parent::update($id, $data);
    }

    /**
     * Check if nomor_plan already exists (for validation)
     */
    public function isNomorPlanExist($nomor_plan, $exclude_id = null)
    {
        $builder = $this->where('deleted_at IS NULL')
                       ->where('nomor_plan', $nomor_plan);
        
        if ($exclude_id) {
            $builder->where('id !=', $exclude_id);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Get production with inventory product details
     */
    public function getProduksiWithDetails()
    {
        return $this->where('deleted_at IS NULL')
                   ->orderBy('tanggal_produksi', 'DESC')
                   ->findAll();
    }
}