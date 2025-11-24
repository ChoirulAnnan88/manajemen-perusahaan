<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        // CEK APAKAH TABEL SUDAH ADA
        if ($this->db->tableExists('products')) {
            echo "Table 'products' already exists. Skipping creation...\n";
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nama_produk' => [
                'type' => 'VARCHAR',
                'constraint' => '255'
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'harga' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'stok' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ],
            'gambar' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
            'kategori' => [
                'type' => 'VARCHAR',
                'constraint' => '100'
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
        $this->forge->createTable('products');
        
        echo "Table 'products' created successfully.\n";
        
        // Insert sample products
        $sampleProducts = [
            [
                'nama_produk' => 'Laptop ASUS ROG',
                'deskripsi' => 'Laptop gaming dengan spesifikasi tinggi',
                'harga' => 15000000,
                'stok' => 10,
                'kategori' => 'Elektronik',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_produk' => 'Smartphone Samsung S21',
                'deskripsi' => 'Smartphone flagship dengan kamera canggih',
                'harga' => 8000000,
                'stok' => 15,
                'kategori' => 'Elektronik',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nama_produk' => 'Sepatu Nike Air Max',
                'deskripsi' => 'Sepatu olahraga dengan teknologi terbaru',
                'harga' => 1200000,
                'stok' => 20,
                'kategori' => 'Fashion',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('products')->insertBatch($sampleProducts);
        echo "Sample products inserted successfully.\n";
    }

    public function down()
    {
        $this->forge->dropTable('products');
        echo "Table 'products' dropped successfully.\n";
    }
}