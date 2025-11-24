<?php
// test_db_connection.php
try {
    $host = 'localhost';
    $dbname = 'toko_online';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully!<br>";
    
    // Test table users
    $stmt = $conn->query("SELECT * FROM users LIMIT 1");
    $users = $stmt->fetchAll();
    echo "✅ Users table accessible!<br>";
    
} catch(PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>