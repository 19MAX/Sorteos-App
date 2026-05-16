<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShortIdToTransactions extends Migration
{
    public function up()
    {
        $fields = [
            'short_id' => ['type' => 'VARCHAR', 'constraint' => 8, 'unique' => true, 'after' => 'transaccion_id'],
        ];
        $this->forge->addColumn('transactions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'short_id');
    }
}