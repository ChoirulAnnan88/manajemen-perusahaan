<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class DebugController extends BaseController
{
    public function hrga()
    {
        echo "<h1>🔧 DEBUG HRGA ACCESS</h1>";
        echo "<hr>";
        
        // 1. Check Session
        echo "<h2>1. Session Data</h2>";
        $session = session();
        echo "<pre>";
        print_r([
            'logged_in' => $session->get('logged_in'),
            'user_id' => $session->get('user_id'),
            'username' => $session->get('username'),
            'role' => $session->get('role'),
            'divisi_id' => $session->get('divisi_id'),
            'divisi_name' => $session->get('divisi_name')
        ]);
        echo "</pre>";
        
        // 2. Check Controller & Method
        echo "<h2>2. Controller & Method Check</h2>";
        $router = service('router');
        echo "Controller: " . $router->controllerName() . "<br>";
        echo "Method: " . $router->methodName() . "<br>";
        
        // 3. Check HrgaController Existence
        echo "<h2>3. HrgaController Check</h2>";
        $controllerPath = APPPATH . 'Controllers/HrgaController.php';
        if (file_exists($controllerPath)) {
            echo "✓ HrgaController.php EXISTS<br>";
            
            // Check class exists
            if (class_exists('App\Controllers\HrgaController')) {
                echo "✓ HrgaController CLASS LOADED<br>";
                
                // Check method exists
                if (method_exists('App\Controllers\HrgaController', 'index')) {
                    echo "✓ index() METHOD EXISTS<br>";
                } else {
                    echo "✗ index() METHOD NOT FOUND<br>";
                }
            } else {
                echo "✗ HrgaController CLASS NOT LOADED<br>";
            }
        } else {
            echo "✗ HrgaController.php NOT FOUND at: $controllerPath<br>";
        }
        
        // 4. Check Models
        echo "<h2>4. Models Check</h2>";
        $models = [
            'HrgaKaryawanModel',
            'HrgaAbsensiModel', 
            'HrgaPenggajianModel',
            'HrgaPenilaianModel',
            'HrgaInventarisModel',
            'HrgaPerawatanModel',
            'HrgaPerizinanModel',
            'DivisiModel'
        ];
        
        foreach ($models as $model) {
            if (class_exists("App\Models\\$model")) {
                echo "✓ $model LOADED<br>";
            } else {
                echo "✗ $model NOT FOUND<br>";
            }
        }
        
        // 5. Test Database Connection
        echo "<h2>5. Database Check</h2>";
        try {
            $db = db_connect();
            $db->query('SELECT 1');
            echo "✓ Database CONNECTED<br>";
            
            // Check HRGA tables
            $tables = ['hrga_karyawan', 'hrga_absensi', 'hrga_penggajian'];
            foreach ($tables as $table) {
                if ($db->tableExists($table)) {
                    echo "✓ Table $table EXISTS<br>";
                } else {
                    echo "✗ Table $table NOT FOUND<br>";
                }
            }
        } catch (\Exception $e) {
            echo "✗ Database ERROR: " . $e->getMessage() . "<br>";
        }
        
        // 6. Test Access Control
        echo "<h2>6. Access Control Test</h2>";
        $hrgaController = new \App\Controllers\HrgaController();
        
        // Test reflection to access private method
        $reflection = new \ReflectionClass($hrgaController);
        $method = $reflection->getMethod('checkDivisionAccess');
        $method->setAccessible(true);
        
        $result = $method->invoke($hrgaController, 'hrga');
        echo "checkDivisionAccess('hrga') = " . ($result ? 'TRUE' : 'FALSE') . "<br>";
        
        // 7. Test Route
        echo "<h2>7. Route Test</h2>";
        echo "Current URL: " . current_url() . "<br>";
        echo "Base URL: " . base_url() . "<br>";
        echo "Site URL to HRGA: " . site_url('hrga') . "<br>";
        
        // 8. Test View File
        echo "<h2>8. View File Check</h2>";
        $viewPath = APPPATH . 'Views/hrga/dashboard.php';
        if (file_exists($viewPath)) {
            echo "✓ View: hrga/dashboard.php EXISTS<br>";
        } else {
            echo "✗ View: hrga/dashboard.php NOT FOUND at: $viewPath<br>";
        }
        
        // 9. Try to call HrgaController directly
        echo "<h2>9. Direct HrgaController Test</h2>";
        try {
            $hrga = new \App\Controllers\HrgaController();
            $response = $hrga->index();
            echo "✓ HrgaController::index() EXECUTED SUCCESSFULLY<br>";
        } catch (\Exception $e) {
            echo "✗ HrgaController::index() ERROR: " . $e->getMessage() . "<br>";
            echo "<pre>Stack trace:\n" . $e->getTraceAsString() . "</pre>";
        }
        
        echo "<hr>";
        echo "<h2>🎯 QUICK FIXES TO TRY:</h2>";
        echo "1. <a href='" . site_url('hrga') . "'>Access HRGA Directly</a><br>";
        echo "2. <a href='" . base_url('index.php/hrga') . "'>Access via index.php</a><br>";
        echo "3. <a href='" . site_url('debug/routes') . "'>Check Routes Debug</a><br>";
        
        die();
    }
    
    public function routes()
    {
        echo "<h1>🛣️ DEBUG ROUTES</h1>";
        
        $router = service('router');
        $routes = service('routes');
        
        echo "<h2>Current Route Info:</h2>";
        echo "Controller: " . $router->controllerName() . "<br>";
        echo "Method: " . $router->methodName() . "<br>";
        echo "URI: " . $router->getMatchedRoute() . "<br>";
        
        echo "<h2>All Defined Routes:</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Method</th><th>Route</th><th>Handler</th></tr>";
        
        $methods = ['get', 'post', 'put', 'delete', 'options', 'patch', 'cli'];
        
        foreach ($methods as $method) {
            $methodRoutes = $routes->getRoutes($method);
            foreach ($methodRoutes as $route => $handler) {
                echo "<tr>";
                echo "<td>" . strtoupper($method) . "</td>";
                echo "<td>$route</td>";
                echo "<td>" . (is_string($handler) ? $handler : 'Closure') . "</td>";
                echo "</tr>";
            }
        }
        
        echo "</table>";
        die();
    }
}