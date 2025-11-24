<?php
namespace App\Controllers;

use App\Models\UserModel;

class SimpleAuth extends BaseController
{
    public function register()
    {
        echo "<h1>Simple Register Test</h1>";
        
        if ($this->request->getMethod() === 'post') {
            echo "<h2 style='color:green'>✅ POST DITERIMA!</h2>";
            
            $userData = [
                'username' => 'simple_' . time(),
                'email' => 'simple_' . time() . '@test.com',
                'password' => 'simple123',
                'nama_lengkap' => 'Simple User',
                'telepon' => '08123456789',
                'role' => 'customer'
            ];
            
            try {
                $userModel = new UserModel();
                $result = $userModel->save($userData);
                echo $result ? "✅ INSERT BERHASIL" : "❌ INSERT GAGAL";
            } catch (\Exception $e) {
                echo "❌ ERROR: " . $e->getMessage();
            }
            return;
        }
        
        echo '
        <form method="post">
            <button type="submit">TEST SIMPLE REGISTER</button>
        </form>
        ';
    }
}