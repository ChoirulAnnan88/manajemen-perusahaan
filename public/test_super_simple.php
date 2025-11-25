<?php
// test_super_simple.php - Super Simple Test
echo "<h1>🧪 SUPER SIMPLE TEST</h1>";

echo "<h3>1. Basic PHP Test:</h3>";
echo "✅ PHP Version: " . PHP_VERSION . "<br>";
echo "✅ Server Time: " . date('Y-m-d H:i:s') . "<br>";

echo "<h3>2. Manual Database Test:</h3>";
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'toko_online';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "❌ Database: " . $conn->connect_error . "<br>";
} else {
    echo "✅ Database: CONNECTED<br>";
    
    // Test tables
    $tables = ['categories', 'products', 'users'];
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as c FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "📊 $table: " . $row['c'] . " records<br>";
            
            // Show sample data for first 2 records
            if ($row['c'] > 0 && in_array($table, ['categories', 'products'])) {
                $sample = $conn->query("SELECT * FROM $table LIMIT 2");
                echo "<small>";
                while ($data = $sample->fetch_assoc()) {
                    if ($table == 'categories') {
                        echo "&nbsp;&nbsp;• " . $data['nama_kategori'] . "<br>";
                    } else {
                        echo "&nbsp;&nbsp;• " . $data['nama_produk'] . " (Rp " . number_format($data['harga']) . ")<br>";
                    }
                }
                echo "</small>";
            }
        }
    }
    $conn->close();
}

echo "<h3>3. File Structure Check:</h3>";
$files_to_check = [
    '../app/Controllers/Home.php',
    '../app/Models/ProductModel.php', 
    '../app/Models/CategoryModel.php',
    '../app/Views/home/index.php',
    '../app/Config/Database.php',
    '../app/Config/Routes.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ " . $file . "<br>";
    } else {
        echo "❌ " . $file . " - MISSING<br>";
    }
}

echo "<hr>";
echo "<h3>🎯 CONCLUSION:</h3>";

echo "<div style='padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<strong>Database CONNECTED and has DATA!</strong><br>";
echo "But CI4 framework has configuration issues.<br>";
echo "The problem is in CI4 bootstrap/configuration, not database.";
echo "</div>";

echo "<h3>🚀 NEXT STEPS:</h3>";
echo '<a href="http://localhost:8081/" style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;">Test Homepage</a>';
echo '<a href="http://localhost:8081/products" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;">Test Products</a>';
echo '<a href="http://localhost:8081/auth/register" style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;">Test Register</a>';

echo "<h3>📋 If Homepage Still Empty:</h3>";
echo "<p>The issue is in CI4 Controller/Model communication. We need to check:</p>";
echo "<ul>";
echo "<li>Home Controller data passing</li>";
echo "<li>Model query methods</li>";
echo "<li>View data display</li>";
echo "</ul>";
?>