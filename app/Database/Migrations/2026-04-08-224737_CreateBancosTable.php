<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBancosTable extends Migration
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
            'nombre_banco' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'tipo_cuenta' => [
                'type' => 'ENUM',
                'constraint' => ['ahorros', 'corriente', 'otro'],
                'default' => 'ahorros'
            ],
            'numero_cuenta' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'titular' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'logo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true // Por si al admin se le olvida subir la imagen
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1 // 1 = Activo, 0 = Inactivo (Oculto para los usuarios)
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

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('bancos');
    }

    public function down()
    {
        $this->forge->dropTable('bancos');
    }
}
