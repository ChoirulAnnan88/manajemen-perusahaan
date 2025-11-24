<?php
// test_models_simple.php - Simple Model Test
echo "<h1>🧪 SIMPLE MODEL TEST</h1>";

// Define constants
define('APPPATH', realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR);
define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

echo "<h3>1. Testing Database Connection...</h3>";

// Test manual database connection first
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'toko_online';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo "❌ Manual DB: " . $conn->connect_error . "<br>";
} else {
    echo "✅ Manual DB: CONNECTED<br>";
    
    // Test direct query
    $result = $conn->query("SELECT p.*, c.nama_kategori FROM products p LEFT JOIN categories c ON p.kategori_id = c.id WHERE p.is_active = 1 LIMIT 5");
    echo "Direct Join Query: " . $result->num_rows . " records<br>";
    
    if ($result->num_rows > 0) {
        echo "<h4>Sample Products with Categories:</h4>";
        while ($row = $result->fetch_assoc()) {
            echo "&nbsp;&nbsp;• " . $row['nama_produk'] . " - " . $row['nama_kategori'] . "<br>";
        }
    }
}

echo "<h3>2. Testing Models with Simple CI4...</h3>";

try {
    // Load minimal CI4
    require_once ROOTPATH . 'vendor/autoload.php';
    
    // Create simple database config
    $dbConfig = [
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'toko_online',
        'DBDriver' => 'MySQLi',
        'port' => 3306
    ];
    
    // Create database connection manually
    $db = \CodeIgniter\Database\Config::connect($dbConfig);
    echo "✅ CI4 Database: CONNECTED<br>";
    
    // Test ProductModel
    echo "<h4>ProductModel Test:</h4>";
    
    // Include and create model manually
    require_once APPPATH . 'Models/ProductModel.php';
    
    // Create model instance with manual DB
    $productModel = new \App\Models\ProductModel();
    $productModel->initConnection($db); // We'll add this method
    
    $products = $productModel->getProductsWithCategory(5);
    echo "getProductsWithCategory(): " . count($products) . " records<br>";
    
    if (count($products) > 0) {
        foreach($products as $product) {
            echo "&nbsp;&nbsp;• " . $product['nama_produk'] . " - " . $product['nama_kategori'] . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ CI4 Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>🎯 DIAGNOSIS:</h3>";

if (isset($products) && count($products) > 0) {
    echo "<div style='color: green; padding: 10px; background: #f0fff0; border: 1px solid green;'>";
    echo "✅ MODELS WORKING! Data flows correctly from Database → Models → Output";
    echo "</div>";
    echo "<p>The issue is in the Controller-View communication, not Models.</p>";
} else {
    echo "<div style='color: red; padding: 10px; background: #fff0f0; border: 1px solid red;'>";
    echo "❌ MODELS NOT WORKING! Data stops at Models";
    echo "</div>";
    echo "<p>The issue is in Model queries or database connection in Models.</p>";
}

echo '<br><a href="http://localhost:8081/">Test Homepage</a>';
?>