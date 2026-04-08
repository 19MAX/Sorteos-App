<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketsTable extends Migration
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
            'numero' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'unique' => true
            ],
            'participant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'transaccion_id' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['disponible', 'reservado', 'pagado', 'asignado'],
                'default' => 'disponible'
            ],
            'fecha_asignacion' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'fecha_pago' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('tickets');
    }

    public function down()
    {
        $this->forge->dropTable('tickets');
    }
}
