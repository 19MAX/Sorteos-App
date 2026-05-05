<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use App\Models\TicketModel;
use App\Models\ParticipantModel;

class TransactionController extends BaseController
{
    protected $transactionModel;
    protected $ticketModel;
    protected $participantModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->ticketModel = new TicketModel();
        $this->participantModel = new ParticipantModel();
        helper(['transaction_status']);
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $transactions = $db->table('transactions t')
            ->select('t.id, t.transaccion_id, t.cantidad_boletos, t.total, t.metodo_pago, t.status, t.created_at, t.completed_at, t.expired_at,
                      p.nombres, p.apellidos, p.cedula, p.email')
            ->join('participants p', 'p.id = t.participant_id', 'left')
            ->orderBy('t.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Transacciones',
            'transactions' => $transactions,
        ];

        return view('admin/transactions/index', $data);
    }

    public function markAsPaid()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(403)
                    ->setJSON(['status' => 'error', 'message' => 'Acceso denegado']);
            }

            $id = $this->request->getGet('id') ?? $this->request->getPost('id');

            if (empty($id)) {
                log_message('error', "TransactionController::markAsPaid - ID no proporcionado");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID de transacción no proporcionado'
                ]);
            }

            $transaction = $this->transactionModel->find($id);

            if (!$transaction) {
                log_message('error', "TransactionController::markAsPaid - Transacción no encontrada ID: {$id}");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Transacción no encontrada'
                ]);
            }

            log_message('info', "TransactionController::markAsPaid - Transacción encontrada: " . json_encode($transaction));

            if ($transaction['status'] !== 'pendiente') {
                log_message('warning', "TransactionController::markAsPaid - Transacción no pendiente. Status actual: {$transaction['status']}");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Solo se pueden marcar como pagadas las transacciones pendientes'
                ]);
            }

            $adminId = session()->get('admin_id');
            log_message('info', "TransactionController::markAsPaid - AdminID: {$adminId}");

            $updated = $this->transactionModel->db->table('transactions')
                ->where('id', $id)
                ->update([
                    'status'       => 'completado',
                    'admin_id'     => $adminId,
                    'completed_at' => date('Y-m-d H:i:s'),
                ]);

            $affected = $this->transactionModel->db->affectedRows();
            log_message('info', "TransactionController::markAsPaid - Transaction update affected rows: {$affected}");

            if (!$updated && $affected === 0) {
                log_message('error', "TransactionController::markAsPaid - Error al actualizar transacción");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al actualizar la transacción'
                ]);
            }

            log_message('info', "TransactionController::markAsPaid - Transacción actualizada exitosamente");

            $boletosAsignados = $transaction['boletos_asignados'] ?? '';
            log_message('info', "TransactionController::markAsPaid - boletos_asignados raw: " . json_encode($boletosAsignados));

            if (!empty($boletosAsignados)) {
                if (is_string($boletosAsignados)) {
                    if (strpos($boletosAsignados, '[') === 0) {
                        $boletosAsignados = json_decode($boletosAsignados, true) ?? [];
                    } else {
                        $boletosAsignados = array_filter(array_map('intval', explode(',', $boletosAsignados)));
                    }
                    log_message('info', "TransactionController::markAsPaid - boletos_asignados procesada: " . json_encode($boletosAsignados));
                }

                if (!empty($boletosAsignados)) {
                    log_message('info', "TransactionController::markAsPaid - Confirmando " . count($boletosAsignados) . " boletos: " . json_encode($boletosAsignados));

                    $boletosConfirmados = $this->ticketModel->confirmTickets($boletosAsignados);

                    log_message('info', "TransactionController::markAsPaid - Boletos confirmados: {$boletosConfirmados}");

                    if ($boletosConfirmados === 0) {
                        log_message('warning', "TransactionController::markAsPaid - No se confirmaron boletos. Verificando estado de los tickets...");

                        $tickets = $this->ticketModel->whereIn('id', $boletosAsignados)->findAll();
                        log_message('info', "TransactionController::markAsPaid - Estado actual de los tickets: " . json_encode($tickets));
                    }
                } else {
                    log_message('warning', "TransactionController::markAsPaid - boletos_asignados vacío después de decodificar");
                }
            } else {
                log_message('warning', "TransactionController::markAsPaid - No hay boletos_asignados en la transacción");
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Transacción marcada como pagada correctamente'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'TransactionController::markAsPaid - Excepción: ' . $e->getMessage());
            log_message('error', 'TransactionController::markAsPaid - Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }

    public function reject()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(403)
                    ->setJSON(['status' => 'error', 'message' => 'Acceso denegado']);
            }

            $id = $this->request->getGet('id') ?? $this->request->getPost('id');

            if (empty($id)) {
                log_message('error', "TransactionController::reject - ID no proporcionado");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID de transacción no proporcionado'
                ]);
            }

            $transaction = $this->transactionModel->find($id);

            if (!$transaction) {
                log_message('error', "TransactionController::reject - Transacción no encontrada ID: {$id}");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Transacción no encontrada'
                ]);
            }

            log_message('info', "TransactionController::reject - Transacción encontrada: " . json_encode($transaction));

            if ($transaction['status'] !== 'pendiente') {
                log_message('warning', "TransactionController::reject - Transacción no pendiente. Status actual: {$transaction['status']}");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Solo se pueden rechazar las transacciones pendientes'
                ]);
            }

            $boletosAsignados = $transaction['boletos_asignados'] ?? '';

            if (!empty($boletosAsignados)) {
                if (is_string($boletosAsignados)) {
                    if (strpos($boletosAsignados, '[') === 0) {
                        $boletosAsignados = json_decode($boletosAsignados, true) ?? [];
                    } else {
                        $boletosAsignados = array_filter(array_map('intval', explode(',', $boletosAsignados)));
                    }
                }

                if (!empty($boletosAsignados)) {
                    log_message('info', "TransactionController::reject - Liberando " . count($boletosAsignados) . " boletos");

                    $boletosLiberados = $this->ticketModel->releaseTickets($boletosAsignados);

                    log_message('info', "TransactionController::reject - Boletos liberados: {$boletosLiberados}");
                }
            }

            $updated = $this->transactionModel->db->table('transactions')
                ->where('id', $id)
                ->update([
                    'status'    => 'rechazada',
                    'failed_at' => date('Y-m-d H:i:s'),
                ]);

            $affected = $this->transactionModel->db->affectedRows();
            log_message('info', "TransactionController::reject - Transaction update affected rows: {$affected}");

            if (!$updated && $affected === 0) {
                log_message('error', "TransactionController::reject - Error al actualizar transacción");
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al rechazar la transacción'
                ]);
            }

            log_message('info', "TransactionController::reject - Transacción rechazada exitosamente");

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Transacción rechazada correctamente'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'TransactionController::reject - Excepción: ' . $e->getMessage());
            log_message('error', 'TransactionController::reject - Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }

    public function expireExpired()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(403)
                    ->setJSON(['status' => 'error', 'message' => 'Acceso denegado']);
            }

            $totalTicketsReleased = 0;
            $totalTransactionsExpired = 0;

            $expiredTransactions = $this->transactionModel->where('status', 'pendiente')
                ->where('expired_at <', date('Y-m-d H:i:s'))
                ->findAll();

            log_message('info', "TransactionController::expireExpired - Transacciones pendientes expiradas: " . count($expiredTransactions));

            foreach ($expiredTransactions as $transaction) {
                $boletosAsignados = $transaction['boletos_asignados'] ?? '';

                if (!empty($boletosAsignados)) {
                    if (is_string($boletosAsignados)) {
                        if (strpos($boletosAsignados, '[') === 0) {
                            $boletosAsignados = json_decode($boletosAsignados, true) ?? [];
                        } else {
                            $boletosAsignados = array_filter(array_map('intval', explode(',', $boletosAsignados)));
                        }
                    }

                    if (!empty($boletosAsignados)) {
                        $boletosLiberados = $this->ticketModel->releaseTickets($boletosAsignados);
                        $totalTicketsReleased += $boletosLiberados;
                        log_message('info', "TransactionController::expireExpired - Transacción {$transaction['id']}: {$boletosLiberados} boletos liberados");
                    }
                }

                $this->transactionModel->db->table('transactions')
                    ->where('id', $transaction['id'])
                    ->update([
                        'status' => 'expirado',
                    ]);

                $totalTransactionsExpired++;
            }

            $orphanedTickets = $this->ticketModel->where('status', 'reservado')
                ->where('expired_at <', date('Y-m-d H:i:s'))
                ->findAll();

            log_message('info', "TransactionController::expireExpired - Tickets huérfanos expirados: " . count($orphanedTickets));

            if (!empty($orphanedTickets)) {
                foreach ($orphanedTickets as $ticket) {
                    $transaccionId = $ticket['transaccion_id'];
                    $transaction = $this->transactionModel->where('transaccion_id', $transaccionId)->first();

                    if (!$transaction || $transaction['status'] !== 'pendiente') {
                        $this->ticketModel->db->table('tickets')
                            ->where('id', $ticket['id'])
                            ->update([
                                'status'          => 'disponible',
                                'transaccion_id'   => null,
                                'participant_id'   => null,
                                'reserved_at'      => null,
                                'expired_at'       => null,
                            ]);
                        $totalTicketsReleased++;
                        log_message('info', "TransactionController::expireExpired - Ticket huérfano {$ticket['id']} liberado");
                    }
                }
            }

            log_message('info', "TransactionController::expireExpired - Total: {$totalTransactionsExpired} transacciones expiradas, {$totalTicketsReleased} boletos liberados");

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "{$totalTransactionsExpired} transacciones expiradas procesadas, {$totalTicketsReleased} boletos liberados",
                'data' => [
                    'transactions_expired' => $totalTransactionsExpired,
                    'tickets_released' => $totalTicketsReleased
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'TransactionController::expireExpired - Excepción: ' . $e->getMessage());
            log_message('error', 'TransactionController::expireExpired - Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }
}