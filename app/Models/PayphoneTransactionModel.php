<?php

namespace App\Models;

use CodeIgniter\Model;

class PayphoneTransactionModel extends Model
{
    protected $table            = 'payphone_transactions';
    protected $returnType       = 'array';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'transaction_id',
        'client_transaction_id',
        'email',
        'amount',
        'phone_number',
        'status_code',
        'transaction_status',
        'authorization_code',
        'message',
        'message_code',
        'payphone_transaction_id',
        'document',
        'currency',
        'transaction_date',
        'card_type',
        'card_brand',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}