<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTicketsTable extends Migration
{
    public function up()
    {
        // Modificar la columna status para incluir los nuevos estados
        $this->forge->modifyColumn('tickets', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['disponible', 'reservado', 'procesando', 'vendido', 'pagado', 'asignado', 'expirado'],
                'default' => 'disponible'
            ]
        ]);

        // Modificar la columna transaccion_id existente para que coincida con transactions
        $this->forge->modifyColumn('tickets', [
            'transaccion_id' => [
                'type' => 'VARCHAR',
                'constraint' => 200, // Aumentamos el tamaño para los IDs más largos
                'null' => true
            ]
        ]);

        // Agregar nuevas columnas (SIN el transaction_id INT)
        $fields = [
            'reserved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'fecha_pago'
            ],
            'confirmed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'reserved_at'
            ],
            'expired_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'confirmed_at'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'expired_at'
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'created_at'
            ]
        ];

        $this->forge->addColumn('tickets', $fields);

        // Agregar índices para mejorar el rendimiento
        $this->forge->addKey('status');
        $this->forge->addKey('transaccion_id');
        $this->forge->addKey('reserved_at');
        $this->forge->processIndexes('tickets');

        // Actualizar registros existentes con timestamps si no los tienen
        $this->db->query("
            UPDATE tickets 
            SET created_at = NOW(), updated_at = NOW() 
            WHERE created_at IS NULL
        ");
    }

    public function down()
    {
        // Eliminar las columnas agregadas (solo las nuevas, no transaccion_id)
        $this->forge->dropColumn('tickets', [
            'reserved_at',
            'confirmed_at',
            'expired_at',
            'created_at',
            'updated_at'
        ]);

        // Restaurar la columna status original
        $this->forge->modifyColumn('tickets', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['disponible', 'reservado', 'pagado', 'asignado'],
                'default' => 'disponible'
            ]
        ]);

        // Restaurar tamaño original de transaccion_id
        $this->forge->modifyColumn('tickets', [
            'transaccion_id' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true
            ]
        ]);
    }
}
