<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParticipantsTable extends Migration
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
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'unique' => true
            ],
            'nombres' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'apellidos' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'cedula' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'unique' => true
            ],
            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'verificado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('participants');
    }

    public function down()
    {
        $this->forge->dropTable('participants');
    }
}
