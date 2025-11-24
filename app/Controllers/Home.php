<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;

class Home extends BaseController
{
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        helper(['text', 'form', 'url']);
    }

    public function index()
    {
        try {
            // DIRECT QUERY - No models
            $db = \Config\Database::connect();
            
            // Products with categories
            $builder = $db->table('products p');
            $builder->select('p.*, c.nama_kategori');
            $builder->join('categories c', 'c.id = p.kategori_id', 'left');
            $builder->where('p.is_active', 1);
            $builder->limit(8);
            $products = $builder->get()->getResultArray();
            
            // Categories
            $categories = $db->table('categories')->get()->getResultArray();
            
            $data = [
                'title' => 'Toko Online - Beranda',
                'products' => $products,
                'categories' => $categories
            ];

            // Tambahkan flash messages dari auth
            if (session()->has('success')) {
                $data['success'] = session('success');
            }
            if (session()->has('error')) {
                $data['error'] = session('error');
            }

            return view('home/index', $data);
            
        } catch (\Exception $e) {
            $data = [
                'title' => 'Toko Online - Beranda',
                'products' => [],
                'categories' => [],
                'error' => $e->getMessage()
            ];
            return view('home/index', $data);
        }
    }

    public function products()
    {
        try {
            $categoryId = $this->request->getGet('category');
            $search = $this->request->getGet('search');

            $db = \Config\Database::connect();
            $builder = $db->table('products p');
            $builder->select('p.*, c.nama_kategori');
            $builder->join('categories c', 'c.id = p.kategori_id', 'left');
            $builder->where('p.is_active', 1);
            
            if (!empty($categoryId)) {
                $builder->where('p.kategori_id', $categoryId);
            }
            
            if (!empty($search)) {
                $builder->groupStart()
                        ->like('p.nama_produk', $search)
                        ->orLike('p.deskripsi', $search)
                        ->groupEnd();
            }
            
            $products = $builder->get()->getResultArray();
            
            // Categories for filter
            $categories = $db->table('categories')->get()->getResultArray();

            $data = [
                'title' => 'Toko Online - Produk',
                'products' => $products,
                'categories' => $categories,
                'categoryId' => $categoryId,
                'search' => $search
            ];

            return view('home/products', $data);
            
        } catch (\Exception $e) {
            $data = [
                'title' => 'Toko Online - Produk',
                'products' => [],
                'categories' => [],
                'categoryId' => null,
                'search' => null,
                'error' => $e->getMessage()
            ];
            return view('home/products', $data);
        }
    }

    public function productDetail($slug)
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('products p');
            $builder->select('p.*, c.nama_kategori');
            $builder->join('categories c', 'c.id = p.kategori_id', 'left');
            $builder->where('p.slug', $slug);
            $builder->where('p.is_active', 1);
            
            $product = $builder->get()->getRowArray();
            
            if (!$product) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $data = [
                'title' => $product['nama_produk'],
                'product' => $product
            ];

            return view('home/product_detail', $data);
            
        } catch (\Exception $e) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

        public function categories()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get categories
            $categories = $db->table('categories')
                            ->where('is_active', 1)
                            ->get()
                            ->getResultArray();
            
            // Get products count per category
            $productsCount = $db->table('products p')
                               ->select('p.kategori_id, COUNT(*) as total_products')
                               ->where('p.is_active', 1)
                               ->groupBy('p.kategori_id')
                               ->get()
                               ->getResultArray();
            
            $countMap = [];
            foreach ($productsCount as $count) {
                $countMap[$count['kategori_id']] = $count['total_products'];
            }
            
            // Add product count to categories
            foreach ($categories as &$category) {
                $category['total_products'] = $countMap[$category['id']] ?? 0;
            }

            $data = [
                'title' => 'Toko Online - Kategori',
                'categories' => $categories
            ];

            return view('home/categories', $data);
            
        } catch (\Exception $e) {
            $data = [
                'title' => 'Toko Online - Kategori',
                'categories' => [],
                'error' => $e->getMessage()
            ];
            return view('home/categories', $data);
        }
    }

    public function cart()
    {
        // Simple cart page for now
        $data = [
            'title' => 'Toko Online - Keranjang Belanja'
        ];
        return view('home/cart', $data);
    }

    public function productSearch()
    {
        // Redirect to products page with search parameter
        $search = $this->request->getGet('q');
        return redirect()->to('/products?search=' . urlencode($search));
    }
}