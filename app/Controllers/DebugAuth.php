<?php
namespace App\Controllers;

class DebugAuth extends BaseController
{
    public function register()
    {
        // CLEAR OUTPUT BUFFER DAN HEADER
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: text/html; charset=utf-8');
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Debug Test - Fixed</title>
            <style>
                body { font-family: Arial; margin: 40px; background: #f5f5f5; }
                .success { color: green; font-weight: bold; }
                .error { color: red; font-weight: bold; }
                .info { color: blue; }
                .warning { color: orange; }
                .box { border: 2px solid #333; padding: 20px; margin: 10px 0; background: white; }
                .post-box { border-color: green; background: #f0fff0; }
                .get-box { border-color: blue; background: #f0f8ff; }
                pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
            </style>
        </head>
        <body>";
        
        echo "<h1>🔴 DEBUG TEST - FIXED VERSION</h1>";
        
        // **FIX 1: CEK MULTIPLE WAYS UNTUK DETECT POST**
        $method = $this->request->getMethod();
        $postData = $this->request->getPost();
        $rawPost = $_POST;
        $input = $this->request->getVar();
        
        echo "<div class='box'>";
        echo "<h2>📊 DEBUG INFORMATION</h2>";
        echo "<p><strong>Waktu:</strong> " . date('H:i:s') . "</p>";
        echo "<p><strong>Method:</strong> " . $method . "</p>";
        echo "<p><strong>SERVER['REQUEST_METHOD']:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET') . "</p>";
        echo "</div>";
        
        // **FIX 2: DETECT POST DENGAN BERBAGAI CARA**
        $isPost = ($method === 'POST') || 
                 (!empty($postData)) || 
                 (!empty($rawPost)) || 
                 (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST');
        
        if ($isPost) {
            echo "<div class='box post-box'>";
            echo "<h2 class='success'>✅ POST REQUEST TERDETEKSI!</h2>";
            
            echo "<h3>📦 Data dari getPost():</h3>";
            echo "<pre>";
            if (empty($postData)) {
                echo "❌ getPost() KOSONG";
            } else {
                print_r($postData);
            }
            echo "</pre>";
            
            echo "<h3>📦 Data dari \$_POST:</h3>";
            echo "<pre>";
            if (empty($rawPost)) {
                echo "❌ \$_POST KOSONG";
            } else {
                print_r($rawPost);
            }
            echo "</pre>";
            
            echo "<h3>📦 Data dari getVar():</h3>";
            echo "<pre>";
            if (empty($input)) {
                echo "❌ getVar() KOSONG";
            } else {
                print_r($input);
            }
            echo "</pre>";
            
            echo "<h3>📦 Raw Input (php://input):</h3>";
            echo "<pre>";
            $rawInput = file_get_contents('php://input');
            echo htmlspecialchars($rawInput ?: "❌ TIDAK ADA RAW INPUT");
            echo "</pre>";
            
            // **FIX 3: SIMPAN KE FILE DENGAN MULTIPLE FORMAT**
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'method' => $method,
                'getPost' => $postData,
                '_POST' => $rawPost,
                'getVar' => $input,
                'raw_input' => $rawInput
            ];
            
            $logMessage = "=== DEBUG LOG ===\n" . print_r($logData, true) . "================\n";
            file_put_contents(WRITEPATH . 'debug_log.txt', $logMessage, FILE_APPEND);
            
            echo "<p class='info'>📝 <strong>Data lengkap disimpan ke: writable/debug_log.txt</strong></p>";
            
            // **FIX 4: STOP EXECUTION DENGAN CLEAN OUTPUT**
            echo "<hr>";
            echo "<h2 class='success'>🎉 DEBUG BERHASIL - PROGRAM BERHENTI</h2>";
            echo "<p>Jika Anda melihat ini, berarti POST data berhasil ditangkap!</p>";
            echo "</div>";
            
            // HENTIKAN PROGRAM - KUNCI UTAMA
            echo "</body></html>";
            exit(); // Gunakan exit() bukan die() untuk lebih clean
        }
        
        // **JIKA BUKAN POST, TAMPILKAN FORM**
        echo "<div class='box get-box'>";
        echo "<h2>📝 FORM TEST - FIXED</h2>";
        echo '<form method="POST" action="/debug/register">
            <table>
                <tr>
                    <td><strong>test_value:</strong></td>
                    <td><input type="text" name="test_value" value="nilai_test_fixed" style="padding: 10px; width: 250px;" required></td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <button type="submit" style="padding: 12px 25px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                            🚀 KLIK UNTUK TEST SUBMIT
                        </button>
                    </td>
                </tr>
            </table>
        </form>';
        echo "</div>";
        
        echo "<div class='box'>";
        echo "<h3>📋 INSTRUKSI TEST:</h3>";
        echo "<ol>
            <li>Field: <strong>test_value</strong> dengan nilai: <code>nilai_test_fixed</code></li>
            <li>Klik tombol <strong>🚀 KLIK UNTUK TEST SUBMIT</strong></li>
            <li>HASIL akan tampil di bawah form (tidak refresh)</li>
            <li>Cek file: <code>writable/debug_log.txt</code></li>
        </ol>";
        echo "</div>";
        
        echo "</body></html>";
        return;
    }
    
    public function testRegister()
    {
        echo "<h1>🧪 testRegister Method</h1>";
        echo "<p><strong>Method:</strong> " . $this->request->getMethod() . "</p>";
        
        if ($this->request->getMethod() === 'post') {
            echo "<h3 style='color: green;'>✅ POST DITERIMA di testRegister</h3>";
            echo "<pre>";
            print_r($this->request->getPost());
            echo "</pre>";
            exit();
        }
        
        echo '<form method="POST"><input name="test_field"><button>TEST</button></form>';
    }
    
    public function checkSession()
    {
        echo "<h1>🔍 Session Check</h1>";
        echo "<p><strong>URL:</strong> " . current_url() . "</p>";
        
        echo "<h3>CI4 Session:</h3>";
        echo "<pre>";
        print_r(session()->get());
        echo "</pre>";
    }
}