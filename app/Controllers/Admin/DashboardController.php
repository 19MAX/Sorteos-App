<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ParticipantModel;
use App\Models\TransactionModel;
use App\Models\TicketModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        $participantModel = new ParticipantModel();
        $transactionModel = new TransactionModel();
        $ticketModel = new TicketModel();

        $data = [
            'total_participants' => $participantModel->countAll(),
            'total_tickets_sold' => $ticketModel->whereIn('status', [
                TicketModel::STATUS_VENDIDO,
                TicketModel::STATUS_PAGADO,
                TicketModel::STATUS_ASIGNADO
            ])->countAllResults(),
            'transactions_completed' => $transactionModel->where('status', 'completado')->countAllResults(),
            'transactions_pending' => $transactionModel->where('status', 'pendiente')->countAllResults(),
            'transactions_expired' => $transactionModel->where('status', 'expirado')->countAllResults(),
            'transactions_rejected' => $transactionModel->where('status', 'rechazada')->countAllResults(),
            'transactions_cancelled' => $transactionModel->where('status', 'cancelado')->countAllResults(),
            'total_tickets' => $ticketModel->countAll(),
            'tickets_available' => $ticketModel->getAvailableCount(),
        ];

        return view('admin/index', $data);
    }
}
