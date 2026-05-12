<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use App\Models\TicketModel;
use App\Models\ParticipantModel;
use App\Services\AprobarPagoService;

class TransactionController extends BaseController
{
    protected $transactionModel;
    protected $ticketModel;
    protected $participantModel;
    protected $aprobarPagoService;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->ticketModel = new TicketModel();
        $this->participantModel = new ParticipantModel();
        $this->aprobarPagoService = new AprobarPagoService();
        helper(['transaction_status']);
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $metodo = $this->request->getGet('metodo') ?? '';
        $status = $this->request->getGet('status') ?? '';

        $builder = $db->table('transactions t')
            ->select('t.id, t.transaccion_id, t.cantidad_boletos, t.total, t.metodo_pago, t.status, t.created_at, t.completed_at, t.expired_at,
                      p.nombres, p.apellidos, p.cedula, p.email')
            ->join('participants p', 'p.id = t.participant_id', 'left');

        if (!empty($metodo)) {
            $builder->where('t.metodo_pago', $metodo);
        }

        if (!empty($status)) {
            $builder->where('t.status', $status);
        }

        $transactions = $builder->orderBy('t.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Transacciones',
            'transactions' => $transactions,
            'filterMetodo' => $metodo,
            'filterStatus' => $status,
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

            $approved = $this->aprobarPagoService->approvePayment($id);

            if (!$approved) {
                log_message('error', "TransactionController::markAsPaid - Falló aprobación: " . json_encode($this->aprobarPagoService->result));
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $this->aprobarPagoService->result['message'] ?? 'Error al aprobar la transacción'
                ]);
            }

            log_message('info', "TransactionController::markAsPaid - Transacción aprobada exitosamente via AprobarPagoService");

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

    public function tickets(int $transactionId)
    {
        helper('ticket');
        $transaction = $this->transactionModel->find($transactionId);

        if (!$transaction) {
            return redirect()->to(url_to('admin.transactions.index'))
                ->with('error', 'Transacción no encontrada');
        }

        if ($transaction['status'] !== 'completado') {
            return redirect()->to(url_to('admin.transactions.index'))
                ->with('error', 'Solo se pueden ver los boletos de transacciones completadas');
        }

        $tickets = $this->ticketModel->where('transaccion_id', $transaction['transaccion_id'])->findAll();

        $participant = $this->participantModel->find($transaction['participant_id']);

        $data = [
            'title' => 'Boletos de Transacción',
            'transaction' => $transaction,
            'tickets' => $tickets,
            'participant' => $participant,
        ];

        return view('admin/transactions/tickets', $data);
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