<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PayphoneTransactionModel;

class PayphoneTransactionController extends BaseController
{
    protected $payphoneTransactionModel;

    public function __construct()
    {
        $this->payphoneTransactionModel = new PayphoneTransactionModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $transactions = $db->table('payphone_transactions pt')
            ->select('pt.*, t.transaccion_id as internal_transaction_id, t.total as transaction_total, t.created_at as transaction_created_at')
            ->join('transactions t', 't.id = pt.transaction_id', 'left')
            ->orderBy('pt.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Transacciones Payphone',
            'payphoneTransactions' => $transactions,
        ];

        return view('admin/payphone_transactions/index', $data);
    }
}