<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHrgaPenggajianTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_penggajian' => [
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
            'periode_bulan' => [
                'type'       => 'INT',
                'constraint' => 2,
            ],
            'periode_tahun' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'gaji_pokok' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tunjangan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'bonus' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'lembur' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'potongan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'total_gaji' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'status_pembayaran' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'paid'],
                'default'    => 'pending',
            ],
            'tanggal_pembayaran' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'bukti_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_penggajian', true);
        $this->forge->addKey(['karyawan_id', 'periode_bulan', 'periode_tahun']);
        $this->forge->addForeignKey('karyawan_id', 'hrga_karyawan', 'id_karyawan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('hrga_penggajian');
    }

    public function down()
    {
        $this->forge->dropTable('hrga_penggajian');
    }
}