<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePhysicalPaymentsTable extends Migration
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
                'constraint' => 200
            ],
            'admin_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'monto_recibido' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'vuelto' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('transaccion_id', 'transactions', 'transaccion_id');
        $this->forge->addForeignKey('admin_id', 'admins', 'id');
        $this->forge->createTable('physical_payments');
    }

    public function down()
    {
        $this->forge->dropTable('physical_payments');
    }
}
