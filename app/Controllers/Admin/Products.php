<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\CategoryModel;

class Products extends BaseController
{
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        
        helper(['form', 'text', 'filesystem']);
    }

    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        try {
            // 🆕 TAMBAHAN: Search, filter dan pagination
            $search = $this->request->getGet('search');
            $category = $this->request->getGet('category');
            $status = $this->request->getGet('status');
            
            $productModel = $this->productModel;
            
            if ($search) {
                $productModel->groupStart()
                    ->like('nama_produk', $search)
                    ->orLike('deskripsi', $search)
                    ->groupEnd();
            }
            
            if ($category && $category !== 'all') {
                $productModel->where('kategori_id', $category);
            }
            
            if ($status && $status !== 'all') {
                if ($status === 'in_stock') {
                    $productModel->where('stok >', 0);
                } elseif ($status === 'out_of_stock') {
                    $productModel->where('stok <=', 0);
                } elseif ($status === 'low_stock') {
                    $productModel->where('stok <', 10)->where('stok >', 0);
                }
            }

            $data = [
                'title' => 'Manage Products',
                'products' => $productModel->getProductsWithCategory(),
                'categories' => $this->categoryModel->findAll(),
                // 🆕 TAMBAHAN: Filter data
                'search' => $search,
                'selected_category' => $category,
                'status_filter' => $status
            ];

            return view('admin/products/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Products index error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data produk');
        }
    }

    public function create()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Tambah Produk',
            'categories' => $this->categoryModel->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('admin/products/create', $data);
    }

    public function store()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        // 🆕 TAMBAHAN: CSRF Verification
        if (!csrf_hash_is_valid($this->request->getPost('csrf_token'))) {
            return redirect()->back()->with('error', 'Token CSRF tidak valid!')->withInput();
        }

        // Validation rules
        $rules = [
            'nama_produk' => 'required|min_length[3]|max_length[255]',
            'harga' => 'required|numeric|greater_than[0]',
            'stok' => 'required|integer|greater_than_equal_to[0]',
            'kategori_id' => 'required',
            'gambar' => 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png]'
        ];

        if ($this->validate($rules)) {
            try {
                // Handle file upload
                $gambar = $this->request->getFile('gambar');
                $gambarName = 'default.jpg';

                if ($gambar && $gambar->isValid() && !$gambar->hasMoved()) {
                    $gambarName = $gambar->getRandomName();
                    $gambar->move('uploads/products', $gambarName);
                    
                    // 🆕 TAMBAHAN: Create thumbnail
                    $this->createThumbnail('uploads/products/' . $gambarName);
                }

                // Generate slug
                $slug = url_title($this->request->getPost('nama_produk'), '-', true) . '-' . time();

                $productData = [
                    'kategori_id' => $this->request->getPost('kategori_id'),
                    'nama_produk' => $this->request->getPost('nama_produk'),
                    'slug' => $slug,
                    'deskripsi' => $this->request->getPost('deskripsi'),
                    'harga' => $this->request->getPost('harga'),
                    'stok' => $this->request->getPost('stok'),
                    'gambar' => $gambarName,
                    'berat' => $this->request->getPost('berat') ?? 0,
                    'diskon' => $this->request->getPost('diskon') ?? 0,
                    'is_active' => $this->request->getPost('is_active') ? 1 : 0 // 🆕 TAMBAHAN: Active status
                ];

                if ($this->productModel->save($productData)) {
                    // 🆕 TAMBAHAN: Log activity
                    log_message('info', 'Produk baru dibuat: ' . $productData['nama_produk'] . ' oleh admin ' . session()->get('username'));
                    
                    return redirect()->to('/admin/products')->with('success', 'Produk berhasil ditambahkan');
                } else {
                    return redirect()->back()->withInput()->with('error', 'Gagal menyimpan produk');
                }
            } catch (\Exception $e) {
                log_message('error', 'Error creating product: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        }

        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }
    
    // 🆕 TAMBAHAN METHOD BARU
    
    /**
     * Edit product
     */
    public function edit($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/admin/products')->with('error', 'Produk tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Produk',
            'product' => $product,
            'categories' => $this->categoryModel->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('admin/products/edit', $data);
    }
    
    /**
     * Update product
     */
    public function update($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/admin/products')->with('error', 'Produk tidak ditemukan');
        }

        // 🆕 TAMBAHAN: CSRF Verification
        if (!csrf_hash_is_valid($this->request->getPost('csrf_token'))) {
            return redirect()->back()->with('error', 'Token CSRF tidak valid!')->withInput();
        }

        // Validation rules (gambar optional untuk update)
        $rules = [
            'nama_produk' => "required|min_length[3]|max_length[255]|is_unique[products.nama_produk,id,{$id}]",
            'harga' => 'required|numeric|greater_than[0]',
            'stok' => 'required|integer|greater_than_equal_to[0]',
            'kategori_id' => 'required',
            'gambar' => 'max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png]'
        ];

        if ($this->validate($rules)) {
            try {
                $gambarName = $product['gambar'];
                $gambar = $this->request->getFile('gambar');

                // Handle new image upload
                if ($gambar && $gambar->isValid() && !$gambar->hasMoved()) {
                    // Delete old image if not default
                    if ($gambarName !== 'default.jpg') {
                        $oldImagePath = 'uploads/products/' . $gambarName;
                        $oldThumbPath = 'uploads/products/thumbs/' . $gambarName;
                        
                        if (file_exists($oldImagePath)) unlink($oldImagePath);
                        if (file_exists($oldThumbPath)) unlink($oldThumbPath);
                    }
                    
                    $gambarName = $gambar->getRandomName();
                    $gambar->move('uploads/products', $gambarName);
                    
                    // Create thumbnail
                    $this->createThumbnail('uploads/products/' . $gambarName);
                }

                // Generate new slug if product name changed
                $slug = $product['slug'];
                if ($product['nama_produk'] !== $this->request->getPost('nama_produk')) {
                    $slug = url_title($this->request->getPost('nama_produk'), '-', true) . '-' . time();
                }

                $productData = [
                    'id' => $id,
                    'kategori_id' => $this->request->getPost('kategori_id'),
                    'nama_produk' => $this->request->getPost('nama_produk'),
                    'slug' => $slug,
                    'deskripsi' => $this->request->getPost('deskripsi'),
                    'harga' => $this->request->getPost('harga'),
                    'stok' => $this->request->getPost('stok'),
                    'gambar' => $gambarName,
                    'berat' => $this->request->getPost('berat') ?? 0,
                    'diskon' => $this->request->getPost('diskon') ?? 0,
                    'is_active' => $this->request->getPost('is_active') ? 1 : 0
                ];

                if ($this->productModel->save($productData)) {
                    log_message('info', 'Produk diperbarui: ' . $productData['nama_produk'] . ' oleh admin ' . session()->get('username'));
                    return redirect()->to('/admin/products')->with('success', 'Produk berhasil diperbarui');
                } else {
                    return redirect()->back()->withInput()->with('error', 'Gagal memperbarui produk');
                }
            } catch (\Exception $e) {
                log_message('error', 'Error updating product: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        }

        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }
    
    /**
     * Delete product
     */
    public function delete($id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan']);
        }

        try {
            // Delete product image if not default
            if ($product['gambar'] !== 'default.jpg') {
                $imagePath = 'uploads/products/' . $product['gambar'];
                $thumbPath = 'uploads/products/thumbs/' . $product['gambar'];
                
                if (file_exists($imagePath)) unlink($imagePath);
                if (file_exists($thumbPath)) unlink($thumbPath);
            }

            if ($this->productModel->delete($id)) {
                log_message('info', 'Produk dihapus: ' . $product['nama_produk'] . ' oleh admin ' . session()->get('username'));
                return $this->response->setJSON(['success' => true, 'message' => 'Produk berhasil dihapus']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus produk']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting product: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Toggle product active status
     */
    public function toggleStatus($id)
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            return $this->response->setJSON(['success' => false, 'message' => 'Produk tidak ditemukan']);
        }

        try {
            $newStatus = $product['is_active'] ? 0 : 1;
            $this->productModel->update($id, ['is_active' => $newStatus]);
            
            $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => "Produk berhasil {$statusText}",
                'new_status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create thumbnail for product image
     */
    private function createThumbnail($sourcePath)
    {
        try {
            $thumbConfig = [
                'image_library' => 'gd2',
                'source_image' => $sourcePath,
                'new_image' => dirname($sourcePath) . '/thumbs/' . basename($sourcePath),
                'maintain_ratio' => true,
                'width' => 300,
                'height' => 300,
                'quality' => '80%'
            ];
            
            // Ensure thumbs directory exists
            $thumbsDir = dirname($sourcePath) . '/thumbs/';
            if (!is_dir($thumbsDir)) {
                mkdir($thumbsDir, 0755, true);
            }
            
            // You might need to implement your own thumbnail creation logic
            // or use CodeIgniter's image manipulation library
            service('image')->withFile($sourcePath)
                ->fit(300, 300, 'center')
                ->save($thumbConfig['new_image']);
                
        } catch (\Exception $e) {
            log_message('error', 'Thumbnail creation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk product actions
     */
    public function bulkAction()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $action = $this->request->getJSON()->action;
        $productIds = $this->request->getJSON()->product_ids;

        if (empty($productIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada produk yang dipilih']);
        }

        try {
            $successCount = 0;
            
            foreach ($productIds as $productId) {
                switch ($action) {
                    case 'activate':
                        $this->productModel->update($productId, ['is_active' => 1]);
                        $successCount++;
                        break;
                    case 'deactivate':
                        $this->productModel->update($productId, ['is_active' => 0]);
                        $successCount++;
                        break;
                    case 'delete':
                        $product = $this->productModel->find($productId);
                        if ($product) {
                            // Delete image if not default
                            if ($product['gambar'] !== 'default.jpg') {
                                $imagePath = 'uploads/products/' . $product['gambar'];
                                $thumbPath = 'uploads/products/thumbs/' . $product['gambar'];
                                
                                if (file_exists($imagePath)) unlink($imagePath);
                                if (file_exists($thumbPath)) unlink($thumbPath);
                            }
                            
                            $this->productModel->delete($productId);
                            $successCount++;
                        }
                        break;
                }
            }
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => "Berhasil memproses {$successCount} produk"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}