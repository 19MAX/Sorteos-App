<?php
namespace App\Services;

use TicketStatus;
use TransactionStatus;
use App\Models\ParticipantModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class AprobarPagoService
{
    protected $transactionModel;
    protected $ticketModel;

    public array $result = [
        'success' => false,
        'message' => '',
        'data' => [],
    ];

    public function __construct()
    {
        $this->transactionModel = new \App\Models\TransactionModel();
        $this->ticketModel = new \App\Models\TicketModel();
        helper('transaction_status');
    }

    public function approvePayment(int $transactionId): bool
    {
        log_message('debug', "[AprobarPagoService] ── INICIO approvePayment transactionId={$transactionId}");

        // ── 1. Buscar la transacción por ID interno ────────────────────────────
        $transaction = $this->transactionModel->find($transactionId);

        if (!$transaction) {
            return $this->fail("Transacción ID={$transactionId} no encontrada en BD.");
        }

        log_message(
            'debug',
            "[AprobarPagoService] Transacción encontrada."
            . " status={$transaction['status']}"
            . " transaccion_id={$transaction['transaccion_id']}"
            . " boletos_asignados={$transaction['boletos_asignados']}"
        );

        // ── 2. Validar estado de la transacción ───────────────────────────────
        $estadosPermitidos = [TransactionStatus::Pendiente, TransactionStatus::ProcesandoPago];

        if (!in_array($transaction['status'], $estadosPermitidos, true)) {
            return $this->fail(
                "Estado inválido para aprobar. status={$transaction['status']} transactionId={$transactionId}"
            );
        }

        // ── 3. Buscar tickets por transaccion_id externo (VARCHAR de Payphone) ─
        $tickets = $this->ticketModel
            ->where('transaccion_id', $transaction['transaccion_id'])
            ->findAll();

        log_message(
            'debug',
            "[AprobarPagoService] Tickets encontrados: " . count($tickets)
            . " usando transaccion_id={$transaction['transaccion_id']}"
        );

        if (empty($tickets)) {
            return $this->fail(
                "No se encontraron boletos para transaccion_id={$transaction['transaccion_id']}"
                . " (transactionId={$transactionId})"
            );
        }

        // ── 4. Validar estado de cada ticket ──────────────────────────────────
        $estadosTicketPermitidos = [TicketStatus::Reservado, TicketStatus::Procesando];

        foreach ($tickets as $ticket) {
            log_message(
                'debug',
                "[AprobarPagoService] Ticket ID={$ticket['id']}"
                . " numero={$ticket['numero']} status={$ticket['status']}"
            );

            if (!in_array($ticket['status'], $estadosTicketPermitidos, true)) {
                return $this->fail(
                    "Ticket ID={$ticket['id']} tiene estado inválido: {$ticket['status']}"
                );
            }
        }

        // ── 5. Validar que los IDs de ticket coincidan con boletos_asignados
        $boletosRaw = $transaction['boletos_asignados'] ?? '';
        if (is_string($boletosRaw) && strpos($boletosRaw, '[') === 0) {
            $assignedIds = array_map('intval', json_decode($boletosRaw, true) ?? []);
        } else {
            $assignedIds = array_filter(array_map('intval', explode(',', $boletosRaw)));
        }

        log_message(
            'debug',
            "[AprobarPagoService] IDs asignados en transacción: ["
            . implode(', ', $assignedIds) . "]"
        );

        foreach ($tickets as $ticket) {
            $ticketId = (int) $ticket['id'];
            $coincide = in_array($ticketId, $assignedIds, true);

            log_message(
                'debug',
                "[AprobarPagoService] Comparando ticket ID={$ticketId}"
                . " numero={$ticket['numero']} ¿coincide? " . ($coincide ? 'SÍ' : 'NO')
            );

            if (!$coincide) {
                return $this->fail(
                    "Ticket ID={$ticketId} (numero={$ticket['numero']})"
                    . " no está en boletos_asignados: [" . implode(', ', $assignedIds) . "]"
                );
            }
        }

        // ── 6. Transacción DB ──────────────────────────────────────────────────
        log_message('debug', "[AprobarPagoService] Iniciando transacción DB...");
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Verificar el estado actual antes de actualizar
            $currentTx = $this->transactionModel->find($transactionId);
            log_message('debug', "[AprobarPagoService] Estado actual antes de update: " . json_encode($currentTx));

            $db->query("UPDATE transactions SET status = ?, completed_at = ? WHERE id = ?", [
                TransactionStatus::Completado,
                date('Y-m-d H:i:s'),
                $transactionId
            ]);

            $affectedTx = $db->affectedRows();
            log_message('debug', "[AprobarPagoService] Update RAW transacción filas afectadas: {$affectedTx}");

            if ($affectedTx === 0) {
                $lastError = $db->error();
                log_message('error', "[AprobarPagoService] Sin filas afectadas. DB error: " . json_encode($lastError));
                $db->transRollback();
                return $this->fail("No se pudo actualizar la transacción ID={$transactionId}. Error: " . json_encode($lastError));
            }

            foreach ($tickets as $ticket) {
                $this->ticketModel->update($ticket['id'], [
                    'status' => TicketStatus::Pagado,
                    'fecha_pago' => date('Y-m-d H:i:s'),
                    'confirmed_at' => date('Y-m-d H:i:s'),
                ]);

                log_message('debug', "[AprobarPagoService] Update ticket ID={$ticket['id']}: filas=" . $db->affectedRows());
            }

            $db->transComplete();

        } catch (DatabaseException $e) {
            $db->transRollback();
            return $this->fail("DatabaseException al actualizar: " . $e->getMessage());
        }

        // Verificar ANTES de transComplete (mover esta validación)
        if ($db->transStatus() === false) {
            return $this->fail("Rollback silencioso para transactionId={$transactionId}");
        }

        // ── 7. Verificar commit exitoso ───────────────────────────────────────
        if ($db->transStatus() === false) {
            return $this->fail(
                "transComplete() hizo rollback silencioso para transactionId={$transactionId}"
            );
        }

        log_message(
            'info',
            "[AprobarPagoService] ── Pago aprobado OK."
            . " transactionId={$transactionId}"
            . " transaccion_id={$transaction['transaccion_id']}"
            . " tickets_actualizados=" . count($tickets)
        );

        $this->result = [
            'success' => true,
            'message' => 'Transacción aprobada exitosamente.',
            'data' => [
                'transaction_id' => $transactionId,
                'transaccion_id' => $transaction['transaccion_id'],
                'tickets_updated' => count($tickets),
            ],
        ];

        $this->sendConfirmationEmail($transaction, $tickets);

        return true;
    }

    private function sendConfirmationEmail(array $transaction, array $tickets): void
    {
        $participantModel = new ParticipantModel();
        $participant = $participantModel->find($transaction['participant_id']);

        if (!$participant) {
            log_message('warning', "[AprobarPagoService] No se encontró participant_id={$transaction['participant_id']} para enviar correo");
            return;
        }

        $email = $participant['email'] ?? null;
        if (empty($email)) {
            log_message('warning', "[AprobarPagoService] Participant sin email: id={$transaction['participant_id']}");
            return;
        }

        service('queue')->push('emails', 'email', [
            'to' => $email,
            'subject' => 'Confirmación de pago - Sorteo Quickluck',
            'template' => 'emails/tickets_confirmation',
            'viewData' => [
                'participant' => $participant,
                'transaction' => $transaction,
                'tickets' => $tickets,
            ],
        ]);

        log_message('info', "[AprobarPagoService] Email en cola para {$email}, transactionId={$transaction['id']}");
    }

    private function fail(string $message): bool
    {
        log_message('error', "[AprobarPagoService] FALLO: {$message}");
        $this->result = [
            'success' => false,
            'message' => $message,
            'data' => [],
        ];
        return false;
    }
}