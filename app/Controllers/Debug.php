<?php

namespace App\Controllers;

use App\Models\UserModel;

class Debug extends BaseController
{
    public function auth()
    {
        echo "<h1>🔧 DEBUG AUTH</h1>";
        
        echo "<h3>📋 SESSION DATA:</h3>";
        echo "<pre>";
        print_r(session()->get());
        echo "</pre>";
        echo "<hr>";
        
        echo "<h3>📮 POST DATA:</h3>";
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        echo "<hr>";
        
        echo "<h3>🗃️ DATABASE USERS:</h3>";
        try {
            $db = \Config\Database::connect();
            $users = $db->table('users')->get()->getResultArray();
            echo "Total users: " . count($users) . "<br>";
            echo "<pre>";
            print_r($users);
            echo "</pre>";
        } catch (\Exception $e) {
            echo "Database error: " . $e->getMessage();
        }
        echo "<hr>";
        
        echo "<h3>🔐 PASSWORD TEST:</h3>";
        $testPassword = '12345678';
        $testHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "Test Password: " . $testPassword . "<br>";
        echo "Test Hash: " . $testHash . "<br>";
        echo "Password Verify: " . (password_verify($testPassword, $testHash) ? '✅ TRUE' : '❌ FALSE');
        echo "<hr>";
        
        echo "<h3>🛣️ ROUTES CHECK:</h3>";
        echo "Current URL: " . current_url() . "<br>";
        echo "Method: " . $this->request->getMethod() . "<br>";
        echo "<hr>";
        
        echo "<h3>📝 AUTH TEST FORM:</h3>";
        echo '
        <form method="post" action="' . base_url('auth/register') . '">
            <h4>Register Test</h4>
            <input type="text" name="username" value="testuser" placeholder="Username"><br>
            <input type="email" name="email" value="test@example.com" placeholder="Email"><br>
            <input type="password" name="password" value="12345678" placeholder="Password"><br>
            <input type="text" name="nama_lengkap" value="Test User" placeholder="Nama Lengkap"><br>
            <button type="submit">Test Register</button>
        </form>
        ';
    }
    
    public function testRegister()
    {
        if ($this->request->getMethod() === 'post') {
            echo "<h1>🧪 TEST REGISTER PROCESS</h1>";
            
            $userData = [
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'nama_lengkap' => $this->request->getPost('nama_lengkap'),
                'alamat' => 'Test Alamat',
                'telepon' => '08123456'
            ];
            
            echo "<h3>Data to Save:</h3>";
            echo "<pre>";
            print_r($userData);
            echo "</pre>";
            
            $userModel = new UserModel();
            
            try {
                if ($userModel->save($userData)) {
                    echo "<h3 style='color: green;'>✅ SAVE SUCCESS!</h3>";
                    echo "User ID: " . $userModel->getInsertID() . "<br>";
                    
                    // Check database
                    $db = \Config\Database::connect();
                    $newUser = $db->table('users')->where('id', $userModel->getInsertID())->get()->getRowArray();
                    echo "<h3>User in Database:</h3>";
                    echo "<pre>";
                    print_r($newUser);
                    echo "</pre>";
                } else {
                    echo "<h3 style='color: red;'>❌ SAVE FAILED!</h3>";
                    echo "Errors: <br>";
                    echo "<pre>";
                    print_r($userModel->errors());
                    echo "</pre>";
                }
            } catch (\Exception $e) {
                echo "<h3 style='color: red;'>❌ EXCEPTION!</h3>";
                echo "Error: " . $e->getMessage() . "<br>";
                echo "File: " . $e->getFile() . " Line: " . $e->getLine();
            }
        } else {
            echo '
            <form method="post">
                <h2>Manual Register Test</h2>
                <input type="text" name="username" value="testuser" required><br>
                <input type="email" name="email" value="test@example.com" required><br>
                <input type="password" name="password" value="12345678" required><br>
                <input type="text" name="nama_lengkap" value="Test User" required><br>
                <button type="submit">Test Save to Database</button>
            </form>
            ';
        }
    }
    public function testDbInsert()
    {
    echo "<h1>🧪 TEST DATABASE INSERT</h1>";
    
    try {
        $db = \Config\Database::connect();
        
        // Test insert manual
        $data = [
            'username' => 'test_' . time(),
            'email' => 'test_' . time() . '@test.com',
            'password' => password_hash('12345678', PASSWORD_DEFAULT),
            'nama_lengkap' => 'Test User',
            'alamat' => 'Test Address',
            'telepon' => '08123456',
            'role' => 'customer'
        ];
        
        $db->table('users')->insert($data);
        $insertId = $db->insertID();
        
        echo "✅ INSERT BERHASIL! ID: " . $insertId . "<br>";
        
        // Verify
        $user = $db->table('users')->where('id', $insertId)->get()->getRow();
        echo "✅ USER DITEMUKAN: " . $user->username . "<br>";
        
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "<br>";
    }
    }
}