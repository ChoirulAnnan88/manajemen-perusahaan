<?php

namespace App\Controllers;

use App\Models\UserModel;

class TestDb extends BaseController
{
    public function dbTest()
    {
        echo "<h2>🔧 TEST KONEKSI DATABASE</h2>";
        echo "<div style='background: #f0f0f0; padding: 20px; border: 1px solid #ccc;'>";
        
        try {
            // Test 1: Koneksi Database
            $db = \Config\Database::connect();
            echo "✅ Koneksi database BERHASIL<br>";
            
            // Test 2: Query sederhana
            $result = $db->query('SELECT 1 as test');
            $row = $result->getRow();
            echo "✅ Query test BERHASIL - Result: " . $row->test . "<br>";
            
            // Test 3: Cek tabel users
            $tables = $db->listTables();
            echo "📊 Total tabel dalam database: " . count($tables) . "<br>";
            
            if (in_array('users', $tables)) {
                echo "✅ Tabel 'users' ADA<br>";
                
                // Test 4: Cek struktur tabel users
                $fields = $db->getFieldData('users');
                echo "📋 Kolom dalam tabel users:<br>";
                foreach ($fields as $field) {
                    echo "&nbsp;&nbsp;- {$field->name} ({$field->type})<br>";
                }
            } else {
                echo "❌ Tabel 'users' TIDAK ADA<br>";
                echo "💡 Buat tabel users terlebih dahulu<br>";
            }
            
            // Test 5: Insert data test
            $userModel = new UserModel();
            $testData = [
                'username' => 'test_' . time(),
                'email' => 'test_' . time() . '@test.com',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Test User',
                'telepon' => '0812345678',
                'role' => 'customer'
            ];
            
            echo "<br>🔄 Mencoba insert data test...<br>";
            echo "<pre>Data: ";
            print_r($testData);
            echo "</pre>";
            
            if ($userModel->insert($testData)) {
                $userId = $userModel->getInsertID();
                echo "✅ Insert data test BERHASIL - ID: " . $userId . "<br>";
                
                // Test 6: Verifikasi data masuk
                $insertedUser = $userModel->find($userId);
                if ($insertedUser) {
                    echo "✅ Verifikasi data: Data ditemukan di database<br>";
                    echo "<pre>Data yang disimpan: ";
                    print_r($insertedUser);
                    echo "</pre>";
                } else {
                    echo "❌ Verifikasi data: Data TIDAK ditemukan<br>";
                }
            } else {
                echo "❌ Insert data test GAGAL<br>";
                $errors = $userModel->errors();
                if (!empty($errors)) {
                    echo "Error details: <pre>";
                    print_r($errors);
                    echo "</pre>";
                }
            }
            
        } catch (\Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "<br>";
            echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
        }
        
        echo "</div>";
    }
    
    public function checkUsersTable()
    {
        echo "<h2>🔍 CHECK TABEL USERS</h2>";
        
        $db = \Config\Database::connect();
        
        // Cek apakah tabel users ada
        if (!$db->tableExists('users')) {
            echo "❌ Tabel 'users' TIDAK ADA<br>";
            echo "💡 Jalankan query SQL berikut di phpMyAdmin:<br>";
            echo "<pre style='background: #fff; padding: 10px; border: 1px solid #ccc;'>";
            echo "CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(255),
    alamat TEXT,
    telepon VARCHAR(20),
    role ENUM('admin','customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";
            echo "</pre>";
            return;
        }
        
        echo "✅ Tabel 'users' ADA<br>";
        
        // Cek data dalam tabel
        $userModel = new UserModel();
        $users = $userModel->findAll();
        
        echo "📊 Total user dalam database: " . count($users) . "<br>";
        
        if (!empty($users)) {
            echo "<br>📋 Data user:<br>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['username']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "💡 Tabel users KOSONG - belum ada data<br>";
        }
    }
}