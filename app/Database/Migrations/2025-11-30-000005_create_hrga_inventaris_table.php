<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHrgaInventarisTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_inventaris' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'unique'     => true,
            ],
            'nama_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'merk' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'tipe' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'jumlah' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'Unit',
            ],
            'kondisi' => [
                'type'       => 'ENUM',
                'constraint' => ['Baik', 'Rusak Ringan', 'Rusak Berat'],
                'default'    => 'Baik',
            ],
            'lokasi' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'tanggal_pembelian' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'harga' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'masa_garansi' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_inventaris', true);
        $this->forge->addKey('kode_barang');
        $this->forge->createTable('hrga_inventaris');
    }

    public function down()
    {
        $this->forge->dropTable('hrga_inventaris');
    }
}