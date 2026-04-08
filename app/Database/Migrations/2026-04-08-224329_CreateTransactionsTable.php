<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionsTable extends Migration
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
            'transaccion_id' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'unique' => true
            ],
            'participant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'cantidad_boletos' => [
                'type' => 'INT',
                'constraint' => 11
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'metodo_pago' => [
                'type' => 'ENUM',
                'constraint' => ['fisico', 'transferencia', 'tarjeta']
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'completada', 'rechazada'],
                'default' => 'pendiente'
            ],
            'comprobante' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'boletos_asignados' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'admin_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
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
        $this->forge->addForeignKey('participant_id', 'participants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admin_id', 'admins', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('transactions');
    }

    public function down()
    {
        $this->forge->dropTable('transactions');
    }
}
