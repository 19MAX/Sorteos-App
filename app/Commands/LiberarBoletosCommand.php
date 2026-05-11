<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\TransactionModel;
use App\Models\TicketModel;

class LiberarBoletosCommand extends BaseCommand
{
    protected $group       = 'tickets';
    protected $name        = 'tickets:liberar';
    protected $description = 'Encola el job de liberación de boletos expirados';

    public function run(array $params = [])
    {
        $queue = service('queue');

        $queue->push('default', 'liberar_boletos', []);

        CLI::write('[tickets:liberar] Job encolado correctamente', 'green');
    }
}
