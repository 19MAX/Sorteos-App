<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayphoneTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'transaction_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'client_transaction_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'amount' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'status_code' => [
                'type' => 'INT',
                'null' => true,
            ],
            'transaction_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'authorization_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'message' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'message_code' => [
                'type' => 'INT',
                'null' => true,
            ],
            'payphone_transaction_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'document' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 3,
                'null' => true,
            ],
            'transaction_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'card_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'card_brand' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('client_transaction_id');
        $this->forge->addKey('payphone_transaction_id');
        $this->forge->createTable('payphone_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('payphone_transactions');
    }
}
