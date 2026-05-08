<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
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
            'nombre_producto' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'unique' => true
            ],
            'descripcion_producto' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'unique' => true
            ],
            'imagen_producto' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'boletos_minimos' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'total_boletos' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 50000
            ],
            'precio_boleto' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 1.00
            ],
            'boletos_maximos' => [         // Max en modo normal
                'type' => 'INT',
                'constraint' => 11,
                'default' => 20
            ],
            'boletos_escasez' => [         // Max cuando queda <= 5%
                'type' => 'INT',
                'constraint' => 11,
                'default' => 5
            ],
            'sorteo_activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => false
            ],
            'updated_at' => [
                'type' => 'DATETIME'
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        // $this->forge->addForeignKey('updated_by', 'admins', 'id');
        $this->forge->createTable('settings');

    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
