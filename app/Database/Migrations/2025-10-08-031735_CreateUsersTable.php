<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        // CEK APAKAH TABEL SUDAH ADA SEBELUM MEMBUAT
        if ($this->db->tableExists('users')) {
            echo "Table 'users' already exists. Skipping creation...\n";
            
            // Cek dan tambahkan admin user jika belum ada
            $adminExists = $this->db->table('users')
                                   ->where('username', 'admin')
                                   ->countAllResults();
            
            if ($adminExists === 0) {
                $this->db->table('users')->insert([
                    'username' => 'admin',
                    'email' => 'admin@toko.com',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'nama_lengkap' => 'Administrator',
                    'telepon' => '081234567890',
                    'role' => 'admin',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                echo "Admin user created successfully.\n";
            } else {
                echo "Admin user already exists.\n";
            }
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => true
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => true
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '255'
            ],
            'nama_lengkap' => [
                'type' => 'VARCHAR',
                'constraint' => '255'
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'telepon' => [
                'type' => 'VARCHAR',
                'constraint' => '20'
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'customer'],
                'default' => 'customer'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
        
        echo "Table 'users' created successfully.\n";
        
        // Insert admin user
        $this->db->table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@toko.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'nama_lengkap' => 'Administrator',
            'telepon' => '081234567890',
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "Admin user created successfully.\n";
    }

    public function down()
    {
        $this->forge->dropTable('users');
        echo "Table 'users' dropped successfully.\n";
    }
}