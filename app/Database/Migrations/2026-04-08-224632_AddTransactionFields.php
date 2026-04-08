<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransactionFields extends Migration
{
       public function up()
    {
        // Modificar la columna status para incluir los nuevos estados
        $this->forge->modifyColumn('transactions', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'completado', 'rechazada', 'cancelado', 'fallido', 'expirado', 'procesando_pago'],
                'default' => 'pendiente'
            ]
        ]);

        // Agregar nuevas columnas
        $fields = [
            // 'payment_id' => [
            //     'type' => 'VARCHAR',
            //     'constraint' => 100,
            //     'null' => true,
            //     'after' => 'transaccion_id'
            // ],
            // 'payphone_transaction_id' => [
            //     'type' => 'VARCHAR',
            //     'constraint' => 150,
            //     'null' => true,
            //     'after' => 'participant_id'
            // ],
            // 'payphone_data' => [
            //     'type' => 'TEXT',
            //     'null' => true,
            //     'after' => 'boletos_asignados'
            // ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at'
            ],
            'failed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'completed_at'
            ],
            'expired_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'failed_at'
            ]
        ];

        $this->forge->addColumn('transactions', $fields);

        // Agregar índices para mejorar el rendimiento
        // $this->forge->addKey('client_transaction_id');
        // $this->forge->addKey('payment_id');
        $this->forge->addKey('status');
        $this->forge->processIndexes('transactions');
    }

    public function down()
    {
        // Eliminar las columnas agregadas
        $this->forge->dropColumn('transactions', [
            // 'payment_id',
            // 'client_transaction_id',
            // 'payphone_data',
            'completed_at',
            'failed_at',
            'expired_at'
        ]);

        // Restaurar la columna status original
        $this->forge->modifyColumn('transactions', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'completada', 'rechazada'],
                'default' => 'pendiente'
            ]
        ]);
    }
}
