<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\TicketModel;
use App\Models\TransactionModel;

class CleanupExpiredTickets extends BaseCommand
{
    protected $group = 'tickets';
    protected $name = 'tickets:cleanup';
    protected $description = 'Limpia boletos y transacciones expiradas';

    public function run(array $params = [])
    {
        $ticketModel = new TicketModel();
        $transactionModel = new TransactionModel();

        $expiredTransactions = $transactionModel->getExpiredPendingTransactions();
        $count = 0;

        foreach ($expiredTransactions as $transaction) {
            $ticketIds = array_filter(explode(',', $transaction['boletos_asignados'] ?? ''));

            if (!empty($ticketIds)) {
                $ticketModel->releaseTickets($ticketIds);
            }

            $transactionModel->markAsExpired($transaction['id']);
            $count++;
        }

        CLI::write("Transacciones expiradas procesadas: {$count}", 'green');

        $releasedReservations = $ticketModel->cleanupExpiredReservations();
        CLI::write("Reservas expiradas liberadas: {$releasedReservations}", 'green');

        if ($total > 0) {
            CLI::write("Total de elementos procesados: {$total}", 'green');
        } else {
            CLI::write('No había elementos expirados por limpiar.', 'yellow');
        }
    }
}