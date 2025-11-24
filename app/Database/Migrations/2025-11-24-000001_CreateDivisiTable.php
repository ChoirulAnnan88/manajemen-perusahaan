<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDivisiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_divisi' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'unique'     => true,
            ],
            'nama_divisi' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'deskripsi' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_divisi');
        $this->forge->createTable('divisi');
    }

    public function down()
    {
        $this->forge->dropTable('divisi');
    }
}