<?php

namespace App\Controllers\PRODUKSI;

use App\Controllers\BaseController;
use App\Models\PRODUKSI\AlatdanBahanModel;

class AlatdanBahanController extends BaseController
{
    protected $model;
    
    public function __construct()
    {
        $this->model = new AlatdanBahanModel();
    }
    
    public function index()
    {
        $data = [
            'title' => 'Data Material',
            'materials' => $this->model->findAll()
        ];
        
        return view('produksi/alat_dan_bahan/index', $data);
    }
    
    public function view($id)
    {
        $data = [
            'title' => 'Detail Material',
            'material' => $this->model->find($id)
        ];
        
        if (!$data['material']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        return view('produksi/alat_dan_bahan/view', $data);
    }
    
    public function create()
    {
        $data = [
            'title' => 'Tambah Material',
            'validation' => \Config\Services::validation()
        ];
        
        return view('produksi/alat_dan_bahan/create', $data);
    }
    
    public function store()
    {
        $rules = [
            'kode_material' => 'required|is_unique[ppic_material.kode_material]',
            'nama_material' => 'required',
            'stok_aktual' => 'required|numeric',
            'satuan' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'kode_material' => $this->request->getPost('kode_material'),
            'nama_material' => $this->request->getPost('nama_material'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'stok_aktual' => $this->request->getPost('stok_aktual'),
            'stok_minimal' => $this->request->getPost('stok_minimal') ?: 10,
            'satuan' => $this->request->getPost('satuan'),
            'harga_satuan' => $this->request->getPost('harga_satuan') ?: 0,
            'lokasi' => $this->request->getPost('lokasi') ?: 'Gudang Material Produksi',
            'keterangan' => $this->request->getPost('keterangan'),
            'status_stok' => $this->request->getPost('stok_aktual') <= 0 ? 'habis' : 
                           ($this->request->getPost('stok_aktual') <= ($this->request->getPost('stok_minimal') ?: 10) ? 'terbatas' : 'tersedia')
        ];
        
        if ($this->model->insert($data)) {
            return redirect()->to('/produksi/alat-dan-bahan')->with('success', 'Material berhasil ditambahkan!');
        }
        
        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan material');
    }
    
    public function edit($id)
    {
        $data = [
            'title' => 'Edit Material',
            'material' => $this->model->find($id),
            'validation' => \Config\Services::validation()
        ];
        
        if (!$data['material']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        return view('produksi/alat_dan_bahan/edit', $data);
    }
    
    public function update($id)
    {
        $rules = [
            'kode_material' => "required|is_unique[ppic_material.kode_material,id,{$id}]",
            'nama_material' => 'required',
            'stok_aktual' => 'required|numeric',
            'satuan' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'kode_material' => $this->request->getPost('kode_material'),
            'nama_material' => $this->request->getPost('nama_material'),
            'spesifikasi' => $this->request->getPost('spesifikasi'),
            'stok_aktual' => $this->request->getPost('stok_aktual'),
            'stok_minimal' => $this->request->getPost('stok_minimal') ?: 10,
            'satuan' => $this->request->getPost('satuan'),
            'harga_satuan' => $this->request->getPost('harga_satuan') ?: 0,
            'lokasi' => $this->request->getPost('lokasi'),
            'keterangan' => $this->request->getPost('keterangan'),
            'status_stok' => $this->request->getPost('stok_aktual') <= 0 ? 'habis' : 
                           ($this->request->getPost('stok_aktual') <= ($this->request->getPost('stok_minimal') ?: 10) ? 'terbatas' : 'tersedia')
        ];
        
        if ($this->model->update($id, $data)) {
            return redirect()->to('/produksi/alat-dan-bahan')->with('success', 'Material berhasil diupdate!');
        }
        
        return redirect()->back()->withInput()->with('error', 'Gagal mengupdate material');
    }
    
    public function delete($id)
    {
        if ($this->model->delete($id)) {
            return redirect()->to('/produksi/alat-dan-bahan')->with('success', 'Material berhasil dihapus!');
        }
        
        return redirect()->back()->with('error', 'Gagal menghapus material');
    }
}