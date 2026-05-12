<?php
namespace App\Controllers\Payphone;

use App\Controllers\BaseController;
use App\Models\PayphoneTransactionModel;
use TransactionStatus;

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
            return $this->viewError('Datos de pago incompletos.');
        }

        // ── 2. Confirmar con Payphone ──────────────────────────────────────────
        try {
            $confirmationResult = $this->payphoneConfirmService->confirmTransaction($id, $clientTransactionId);
        } catch (\Exception $e) {
            return $this->viewError('No se pudo confirmar el pago con Payphone.');
        }

        // ── 3. Pago NO aprobado por Payphone ──────────────────────────────────
        if ($confirmationResult['success'] !== true) {
            $reason = $confirmationResult['data']['message'] ?? 'Pago no completado';

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
            return $this->viewError('No se encontró la transacción interna.');
        }

        $isAlreadyApproved = in_array($transaction['status'], [TransactionStatus::Completado]);

        if ($isAlreadyApproved) {
            $approveResult = ['message' => 'La transacción ya fue aprobada previamente.'];
        } else {
            $approved      = $this->aprobarPagoService->approvePayment($transaction['id']);
            $approveResult = $this->aprobarPagoService->result;

            if (!$approved) {
                return $this->viewError('No se pudo completar la aprobación del pago.');
            }
        }

        // ── 5. Guardar datos de Payphone en payphone_transactions ─────────────
        $payphoneData = $confirmationResult['data'] ?? [];
        $payphoneTransactionModel = new PayphoneTransactionModel();
        $payphoneTransactionModel->insert([
            'transaction_id'        => $transaction['id'],
            'client_transaction_id' => $clientTransactionId,
            'email'                 => $payphoneData['email'] ?? null,
            'amount'                => $payphoneData['amount'] ?? null,
            'phone_number'          => $payphoneData['phoneNumber'] ?? null,
            'status_code'           => $payphoneData['statusCode'] ?? null,
            'transaction_status'    => $payphoneData['transactionStatus'] ?? null,
            'authorization_code'    => $payphoneData['authorizationCode'] ?? null,
            'message'               => $payphoneData['message'] ?? null,
            'message_code'          => $payphoneData['messageCode'] ?? null,
            'payphone_transaction_id' => $payphoneData['transactionId'] ?? null,
            'document'              => $payphoneData['document'] ?? null,
            'currency'              => $payphoneData['currency'] ?? null,
            'transaction_date'      => isset($payphoneData['date']) ? date('Y-m-d H:i:s', strtotime($payphoneData['date'])) : null,
            'card_type'             => $payphoneData['cardType'] ?? null,
            'card_brand'            => $payphoneData['cardBrand'] ?? null,
        ]);
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