<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHrgaPenilaianTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_penilaian' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'karyawan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'periode' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'penilai_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'kedisiplinan' => [
                'type'       => 'INT',
                'constraint' => 1,
            ],
            'kinerja' => [
                'type'       => 'INT',
                'constraint' => 1,
            ],
            'teamwork' => [
                'type'       => 'INT',
                'constraint' => 1,
            ],
            'kreativitas' => [
                'type'       => 'INT',
                'constraint' => 1,
            ],
            'loyalitas' => [
                'type'       => 'INT',
                'constraint' => 1,
            ],
            'nilai_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,2',
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'rekomendasi' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_penilaian', true);
        $this->forge->addKey(['karyawan_id', 'periode']);
        $this->forge->addForeignKey('karyawan_id', 'hrga_karyawan', 'id_karyawan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('hrga_penilaian');
    }

    public function down()
    {
        $this->forge->dropTable('hrga_penilaian');
    }
}