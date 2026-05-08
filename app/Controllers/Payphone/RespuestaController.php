<?php
namespace App\Controllers\Payphone;

use App\Controllers\BaseController;

class RespuestaController extends BaseController
{
    protected $payphoneConfirmService;
    protected $aprobarPagoService;

    public function __construct()
    {
        $this->payphoneConfirmService = new \App\Services\PayphoneConfirmService();
        $this->aprobarPagoService     = new \App\Services\AprobarPagoService();
    }

    public function index()
    {
        return view('payphone/respuesta');
    }

    public function respuesta()
    {
        // ── 1. Validar parámetros entrantes ────────────────────────────────────
        $id                  = $this->request->getGet('id');
        $clientTransactionId = $this->request->getGet('clientTransactionId');

        if (!$id || !$clientTransactionId) {
            log_message('warning', '[Payphone::respuesta] Parámetros incompletos.'
                . ' id=' . ($id ?? 'null')
                . ' clientTransactionId=' . ($clientTransactionId ?? 'null')
            );
            return $this->viewError('Datos de pago incompletos.');
        }

        // ── 2. Confirmar con Payphone ──────────────────────────────────────────
        try {
            $confirmationResult = $this->payphoneConfirmService->confirmTransaction($id, $clientTransactionId);
        } catch (\Exception $e) {
            log_message('error', '[Payphone::respuesta] Error al confirmar con Payphone: ' . $e->getMessage());
            return $this->viewError('No se pudo confirmar el pago con Payphone.');
        }

        // ── 3. Pago NO aprobado por Payphone ──────────────────────────────────
        if ($confirmationResult['success'] !== true) {
            $reason = $confirmationResult['data']['message'] ?? 'Pago no completado';
            log_message('info', '[Payphone::respuesta] Pago no aprobado por Payphone.'
                . ' clientTransactionId=' . $clientTransactionId
                . ' motivo=' . $reason
            );
            return view('payphone/respuesta', [
                'success' => false,
                'data'    => $confirmationResult['data'],
                'message' => $reason,
            ]);
        }

        // ── 4. Buscar la transacción interna por transaccion_id de Payphone ────
        $transactionModel = new \App\Models\TransactionModel();
        $transaction      = $transactionModel
            ->where('transaccion_id', $clientTransactionId)
            ->first();

        if (!$transaction) {
            log_message('error', '[Payphone::respuesta] Transacción interna no encontrada.'
                . ' clientTransactionId=' . $clientTransactionId
            );
            return $this->viewError('No se encontró la transacción interna.');
        }

        log_message('debug', '[Payphone::respuesta] Transacción interna encontrada.'
            . ' id=' . $transaction['id']
            . ' status=' . $transaction['status']
            . ' boletos_asignados=' . $transaction['boletos_asignados']
        );

        // ── 5. Aprobar el pago internamente ───────────────────────────────────
        $approved      = $this->aprobarPagoService->approvePayment($transaction['id']);
        $approveResult = $this->aprobarPagoService->result;

        if (!$approved) {
            log_message('error', '[Payphone::respuesta] Fallo al aprobar internamente.'
                . ' transactionId=' . $transaction['id']
                . ' motivo=' . $approveResult['message']
            );
            return $this->viewError('No se pudo completar la aprobación del pago.');
        }

        log_message('info', '[Payphone::respuesta] Pago aprobado correctamente.'
            . ' transactionId=' . $transaction['id']
            . ' transaccion_id=' . $clientTransactionId
            . ' tickets_actualizados=' . ($approveResult['data']['tickets_updated'] ?? 0)
        );

        // ── 6. Respuesta exitosa ───────────────────────────────────────────────
        return view('payphone/respuesta', [
            'success' => true,
            'data'    => $confirmationResult['data'],
            'message' => $approveResult['message'],
        ]);
    }

    private function viewError(string $message)
    {
        return view('payphone/respuesta', [
            'success' => false,
            'message' => $message,
        ]);
    }
}