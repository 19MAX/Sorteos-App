<?php

namespace App\Services;

use App\Models\ParticipantModel;
use App\Models\TicketModel;
use App\Models\TransactionModel;
use App\Models\SettingsModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class PhysicalSaleService
{
    protected TicketModel $ticketModel;
    protected TransactionModel $transactionModel;

    public array $result = [
        'success' => false,
        'message' => '',
        'data' => [],
    ];

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->transactionModel = new TransactionModel();
    }

    public function sell(int $participantId, int $cantidad, float $montoRecibido, string $observaciones = '', int $adminId = 0): bool
    {
        $settings = (new SettingsModel())->getSettings();

        $min    = (int)   ($settings['boletos_minimos'] ?? 1);
        $max    = (int)   ($settings['boletos_maximos'] ?? 200);
        $precio = (float) ($settings['precio_boleto']   ?? 1);

        // ── Validaciones ──────────────────────────────────────────────────────
        if ($cantidad < $min || $cantidad > $max) {
            return $this->fail("Cantidad debe estar entre {$min} y {$max}");
        }

        $disponibles = $this->ticketModel->getAvailableCount();
        if ($cantidad > $disponibles) {
            return $this->fail("Solo hay {$disponibles} boletos disponibles");
        }

        $total = $cantidad * $precio;

        if ($montoRecibido > 0 && $montoRecibido < $total) {
            return $this->fail("Monto recibido insuficiente");
        }

        $vuelto = max(0, $montoRecibido - $total);
        if ($montoRecibido <= 0) {
            $montoRecibido = $total; // pago exacto
        }

        // ── Reserva de tickets ────────────────────────────────────────────────
        $tickets = $this->ticketModel
            ->where('status', 'disponible')
            ->orderBy('id', 'ASC')
            ->limit($cantidad)
            ->findAll();

        if (count($tickets) < $cantidad) {
            return $this->fail('No hay suficientes boletos disponibles');
        }

        $ticketIds    = array_column($tickets, 'id');
        $transaccionId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
        $now           = date('Y-m-d H:i:s');

        // ── Transacción DB ────────────────────────────────────────────────────
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $reserved = $this->ticketModel->reserveTickets($ticketIds, $transaccionId, $participantId, 24);

            if ($reserved !== $cantidad) {
                $db->transRollback();
                return $this->fail('Error de concurrencia, intente de nuevo');
            }

            $this->transactionModel->skipValidation(true)->insert([
                'transaccion_id'    => $transaccionId,
                'participant_id'    => $participantId,
                'cantidad_boletos'  => $cantidad,
                'total'             => $total,
                'metodo_pago'       => 'fisico',
                'status'            => 'completado',
                'boletos_asignados' => implode(',', $ticketIds),
                'completed_at'      => $now,
            ]);

            $transactionDbId = $this->transactionModel->getInsertID();

            foreach ($ticketIds as $tid) {
                $this->ticketModel->update($tid, [
                    'status'       => 'pagado',
                    'fecha_pago'   => $now,
                    'confirmed_at' => $now,
                ]);
            }

            $db->table('physical_payments')->insert([
                'transaccion_id' => $transaccionId,
                'admin_id'       => $adminId,
                'monto_recibido' => $montoRecibido,
                'vuelto'         => $vuelto,
                'observaciones'  => $observaciones,
                'created_at'     => $now,
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->fail('Rollback silencioso en la transacción');
            }

        } catch (DatabaseException $e) {
            $db->transRollback();
            log_message('error', '[PhysicalSaleService] DatabaseException: ' . $e->getMessage());
            return $this->fail('Error de base de datos');
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[PhysicalSaleService] Exception: ' . $e->getMessage());
            return $this->fail('Error inesperado: ' . $e->getMessage());
        }

        // ── Notificación por correo (igual que AprobarPagoService) ────────────
        $transaction = $this->transactionModel->find($transactionDbId);
        $freshTickets = $this->ticketModel->where('transaccion_id', $transaccionId)->findAll();
        $this->sendConfirmationEmail($transaction, $freshTickets);

        log_message('info', "[PhysicalSaleService] Venta completada: {$transaccionId} participant={$participantId} cantidad={$cantidad} total={$total}");

        $this->result = [
            'success' => true,
            'message' => 'Venta completada exitosamente',
            'data'    => [
                'transaccion_id'    => $transaccionId,
                'transaction_db_id' => $transactionDbId,
                'participant_id'    => $participantId,
                'cantidad'          => $cantidad,
                'total'             => $total,
                'monto_recibido'    => $montoRecibido,
                'vuelto'            => $vuelto,
                'tickets'           => array_column($freshTickets, 'numero'),
            ],
        ];

        return true;
    }

    private function sendConfirmationEmail(array $transaction, array $tickets): void
    {
        $participant = (new ParticipantModel())->find($transaction['participant_id']);

        if (empty($participant['email'])) {
            log_message('warning', "[PhysicalSaleService] Sin email para participant_id={$transaction['participant_id']}");
            return;
        }

        service('queue')->push('emails', 'email', [
            'to'       => $participant['email'],
            'subject'  => 'Confirmación de pago - Sorteo Quickluck',
            'template' => 'emails/tickets_confirmation',
            'viewData' => [
                'participant' => $participant,
                'transaction' => $transaction,
                'tickets'     => $tickets,
            ],
        ]);

        log_message('info', "[PhysicalSaleService] Email en cola para {$participant['email']}");
    }

    private function fail(string $message): bool
    {
        log_message('error', "[PhysicalSaleService] FALLO: {$message}");
        $this->result = ['success' => false, 'message' => $message, 'data' => []];
        return false;
    }
}