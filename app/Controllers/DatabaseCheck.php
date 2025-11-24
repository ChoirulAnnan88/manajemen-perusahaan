<?php
namespace App\Controllers;

use CodeIgniter\Database\Database;

class DatabaseCheck extends BaseController
{
    public function index()
    {
        $db = db_connect();
        
        echo "<h1>🔍 Database Structure Check</h1>";
        
        // Cek tables
        $tables = $db->listTables();
        echo "<h3>📊 Existing Tables:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>{$table}</li>";
        }
        echo "</ul>";
        
        // Cek users table (jika ada)
        if (in_array('users', $tables)) {
            $users = $db->table('users')->countAll();
            echo "<p>Total Users: <strong>{$users}</strong></p>";
        }
        
        echo "<p>✅ Database connection: <strong>OK</strong></p>";
    }
}