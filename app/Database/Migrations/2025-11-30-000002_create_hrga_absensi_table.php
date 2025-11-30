<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHrgaAbsensiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_absensi' => [
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
            'tanggal' => [
                'type' => 'DATE',
            ],
            'jam_masuk' => [
                'type'       => 'TIME',
                'null'       => true,
            ],
            'jam_pulang' => [
                'type'       => 'TIME',
                'null'       => true,
            ],
            'status_kehadiran' => [
                'type'       => 'ENUM',
                'constraint' => ['Hadir', 'Telat', 'Izin', 'Cuti', 'Alpha'],
                'default'    => 'Hadir',
            ],
            'keterlambatan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'lembur' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
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

        $this->forge->addKey('id_absensi', true);
        $this->forge->addKey(['karyawan_id', 'tanggal']);
        $this->forge->addForeignKey('karyawan_id', 'hrga_karyawan', 'id_karyawan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('hrga_absensi');
    }

    public function down()
    {
        $this->forge->dropTable('hrga_absensi');
    }
}