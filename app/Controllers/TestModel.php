<?php
namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;

class TestModel extends BaseController
{
    public function index()
    {
        echo "<h1>🧪 Model Test</h1>";
        
        // Test Product Model
        echo "<h2>Product Model Test</h2>";
        try {
            $productModel = new ProductModel();
            
            // Test different methods
            echo "<h3>getProductsWithCategory():</h3>";
            $products = $productModel->getProductsWithCategory(5);
            echo "Results: " . count($products) . "<br>";
            echo "<pre>"; print_r($products); echo "</pre>";
            
            echo "<h3>findAll():</h3>";
            $allProducts = $productModel->findAll();
            echo "Results: " . count($allProducts) . "<br>";
            
        } catch(\Exception $e) {
            echo "❌ Product Model Error: " . $e->getMessage() . "<br>";
        }
        
        // Test Category Model
        echo "<h2>Category Model Test</h2>";
        try {
            $categoryModel = new CategoryModel();
            $categories = $categoryModel->findAll();
            echo "Results: " . count($categories) . "<br>";
            echo "<pre>"; print_r($categories); echo "</pre>";
            
        } catch(\Exception $e) {
            echo "❌ Category Model Error: " . $e->getMessage() . "<br>";
        }
    }
}