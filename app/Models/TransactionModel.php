<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'transaccion_id', 'participant_id', 'cantidad_boletos', 'total',
        'metodo_pago', 'status', 'comprobante', 'boletos_asignados', 'admin_id',
        'completed_at', 'failed_at', 'expired_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'transaccion_id'    => 'required|max_length[200]',
        'participant_id'   => 'required|is_natural',
        'cantidad_boletos'  => 'required|is_natural',
        'total'            => 'required|decimal',
        'metodo_pago'       => 'required|in_list[fisico,transferencia,tarjeta]',
        'status'           => 'required|in_list[pendiente,completada,rechazada,cancelado,expirado,procesando_pago]',
    ];

    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateTransactionId'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function generateTransactionId(array $data): array
    {
        if (empty($data['data']['transaccion_id'])) {
            $data['data']['transaccion_id'] = 'TXN-' . strtoupper(bin2hex(random_bytes(8)));
        }
        return $data;
    }

    public function findByTransaccionId(string $transaccionId): ?array
    {
        return $this->where('transaccion_id', $transaccionId)->first();
    }

    public function updateComprobante(int $id, string $comprobante): bool
    {
        return $this->update($id, [
            'comprobante' => $comprobante,
        ]) !== false;
    }

    public function getPendingTransactions(): array
    {
        return $this->where('status', 'pendiente')
            ->where('metodo_pago', 'transferencia')
            ->findAll();
    }

    public function getExpiredPendingTransactions(int $hours = 2): array
    {
        return $this->where('status', 'pendiente')
            ->where('expired_at <', date('Y-m-d H:i:s'))
            ->findAll();
    }

    public function markAsExpired(int $id): bool
    {
        return $this->update($id, [
            'status'     => 'expirado',
            'expired_at' => date('Y-m-d H:i:s'),
        ]) !== false;
    }

    public function markAsCompleted(int $id, ?int $adminId = null): bool
    {
        return $this->update($id, [
            'status'       => 'completado',
            'admin_id'     => $adminId,
            'completed_at' => date('Y-m-d H:i:s'),
        ]) !== false;
    }

    public function markAsRejected(int $id): bool
    {
        return $this->update($id, [
            'status'    => 'rechazada',
            'failed_at' => date('Y-m-d H:i:s'),
        ]) !== false;
    }
}