<?php

namespace App\Controllers\PPIC;

use App\Controllers\BaseController;
use App\Models\PPIC\InventoriModel;
use App\Models\PPIC\ProduksiModel;
use App\Models\PPIC\MaterialModel;
use App\Models\PPIC\PemasokModel;
use App\Models\PPIC\PembeliModel;

class PpicController extends BaseController
{
    protected $inventoriModel;
    protected $produksiModel;
    protected $materialModel;
    protected $pemasokModel;
    protected $pembeliModel;

    public function __construct()
    {
        $this->inventoriModel = new InventoriModel();
        $this->produksiModel = new ProduksiModel();
        $this->materialModel = new MaterialModel();
        $this->pemasokModel = new PemasokModel();
        $this->pembeliModel = new PembeliModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Dashboard PPIC',
            'active_menu' => 'dashboard',
            'stok_rendah' => $this->inventoriModel->getStokRendah(),
            'produksi_aktif' => $this->produksiModel->whereIn('status', ['planned', 'progress'])->countAllResults(),
            'total_material' => $this->materialModel->countAll(),
            'total_pemasok' => $this->pemasokModel->countAll(),
            'total_pembeli' => $this->pembeliModel->countAll(),
            'inventori' => $this->inventoriModel->findAll(), // Ditambahkan untuk statistik
        ];
        return view('ppic/dashboard', $data);
    }

    public function inventori()
    {
        $data = [
            'title' => 'Inventori',
            'active_menu' => 'inventori',
            'inventori' => $this->inventoriModel->findAll(),
        ];
        return view('ppic/inventori/dashboard', $data);
    }

    public function produksi()
    {
        $data = [
            'title' => 'Produksi',
            'active_menu' => 'produksi',
            'produksi' => $this->produksiModel->findAll(),
        ];
        return view('ppic/produksi/dashboard', $data);
    }

    public function material()
    {
        $data = [
            'title' => 'Material',
            'active_menu' => 'material',
            'material' => $this->materialModel->findAll(),
        ];
        return view('ppic/material/dashboard', $data);
    }

    public function pemasok()
    {
        $data = [
            'title' => 'Pemasok',
            'active_menu' => 'pemasok',
            'pemasok' => $this->pemasokModel->findAll(),
        ];
        return view('ppic/pemasok/dashboard', $data);
    }

    public function pembeli()
    {
        $data = [
            'title' => 'Pembeli',
            'active_menu' => 'pembeli',
            'pembeli' => $this->pembeliModel->findAll(),
        ];
        return view('ppic/pembeli/dashboard', $data);
    }
}