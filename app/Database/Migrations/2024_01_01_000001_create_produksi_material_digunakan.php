<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProduksiMaterialDigunakan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'produksi_hasil_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'ppic_material_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'kode_material' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'nama_material' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'jumlah_digunakan' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'satuan' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'harga_satuan' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0
            ],
            'total_harga' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0
            ],
            'tanggal_penggunaan' => [
                'type' => 'DATE'
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true
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
        $this->forge->addForeignKey('produksi_hasil_id', 'produksi_hasil', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('ppic_material_id', 'ppic_material', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['produksi_hasil_id', 'ppic_material_id']);
        $this->forge->createTable('produksi_material_digunakan');
    }

    public function down()
    {
        $this->forge->dropTable('produksi_material_digunakan');
    }
}