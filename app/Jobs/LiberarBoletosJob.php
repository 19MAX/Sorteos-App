<?php

namespace App\Jobs;

use CodeIgniter\Queue\BaseJob;
use App\Models\TransactionModel;
use App\Models\TicketModel;

class LiberarBoletosJob extends BaseJob
{
    public function process(): bool
    {
        $transactionModel = model(TransactionModel::class);
        $ticketModel = model(TicketModel::class);

        $expiredTransactions = $transactionModel->getExpiredProcessingTarjetaTransactions();

        if (empty($expiredTransactions)) {
            log_message('info', '[LiberarBoletosJob] No hay transacciones expiradas');
            return true;
        }

        $db = \Config\Database::connect();
        $totalLiberados = 0;

        foreach ($expiredTransactions as $transaction) {
            $db->transStart();

            try {
                $ticketCount = $ticketModel->releaseProcessingTicketsByTransaction(
                    $transaction['transaccion_id']
                );
                $transactionModel->markAsExpired($transaction['id']);

                $db->transComplete();

                $totalLiberados += $ticketCount;
                log_message('info', "[LiberarBoletosJob] Transacción {$transaction['transaccion_id']}: {$ticketCount} boletos liberados");

            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', "[LiberarBoletosJob] Error en transacción {$transaction['transaccion_id']}: {$e->getMessage()}");
                // Retornar false hace que el job se marque como fallido
                return false;
            }
        }

        log_message('info', "[LiberarBoletosJob] Total: {$totalLiberados} boletos liberados de " . count($expiredTransactions) . " transacciones");

        return true;
    }
}