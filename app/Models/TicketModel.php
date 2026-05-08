<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table            = 'tickets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [
        'numero', 'participant_id', 'transaccion_id', 'status',
        'fecha_asignacion', 'fecha_pago', 'reserved_at', 'confirmed_at', 'expired_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public const STATUS_DISPONIBLE = 'disponible';
    public const STATUS_RESERVADO = 'reservado';
    public const STATUS_PROCESANDO = 'procesando';
    public const STATUS_VENDIDO = 'vendido';
    public const STATUS_PAGADO = 'pagado';
    public const STATUS_ASIGNADO = 'asignado';
    public const STATUS_EXPIRADO = 'expirado';

    public function getAvailableCount(): int
    {
        return (int) $this->where('status', self::STATUS_DISPONIBLE)->countAllResults();
    }

    public function getTotalCount(): int
    {
        return (int) $this->countAll();
    }

    public function getAvailablePercentage(): float
    {
        $total = $this->getTotalCount();
        if ($total === 0) {
            return 0;
        }
        return ($this->getAvailableCount() / $total) * 100;
    }

    public function getSoldPercentage(): float
    {
        $total = $this->getTotalCount();
        if ($total === 0) {
            return 0;
        }
        $sold = $this->whereIn('status', [self::STATUS_VENDIDO, self::STATUS_PAGADO, self::STATUS_ASIGNADO])->countAllResults();
        return ($sold / $total) * 100;
    }

    public function isScarcityMode(): bool
    {
        $percentage = $this->getAvailablePercentage();
        return $percentage <= 5;
    }


    public function getExpiredReservedTickets(int $hours = 2): array
    {
        return $this->where('status', self::STATUS_RESERVADO)
            ->where('expired_at <', date('Y-m-d H:i:s'))
            ->findAll();
    }

    public function reserveTickets(array $ticketIds, string $transaccionId, int $participantId, int $hours = 2): int
    {
        if (empty($ticketIds)) {
            return 0;
        }

        $expiredAt = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));

        $this->db->table($this->table)
            ->whereIn('id', $ticketIds)
            ->where('status', self::STATUS_DISPONIBLE)
            ->update([
                'status'          => self::STATUS_RESERVADO,
                'transaccion_id'   => $transaccionId,
                'participant_id'   => $participantId,
                'reserved_at'      => date('Y-m-d H:i:s'),
                'expired_at'       => $expiredAt,
            ]);

        return $this->db->affectedRows();
    }

    public function reserveTicketsProcessing(array $ticketIds, string $transaccionId, int $participantId, int $minutes = 15): int
    {
        if (empty($ticketIds)) {
            return 0;
        }

        $expiredAt = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));

        $this->db->table($this->table)
            ->whereIn('id', $ticketIds)
            ->where('status', self::STATUS_DISPONIBLE)
            ->update([
                'status'          => self::STATUS_PROCESANDO,
                'transaccion_id'   => $transaccionId,
                'participant_id'   => $participantId,
                'reserved_at'      => date('Y-m-d H:i:s'),
                'expired_at'       => $expiredAt,
            ]);

        return $this->db->affectedRows();
    }

    public function releaseProcessingTickets(array $ticketIds): int
    {
        if (empty($ticketIds)) {
            return 0;
        }

        $this->db->table($this->table)
            ->whereIn('id', $ticketIds)
            ->where('status', self::STATUS_PROCESANDO)
            ->update([
                'status'          => self::STATUS_DISPONIBLE,
                'transaccion_id'   => null,
                'participant_id'   => null,
                'reserved_at'      => null,
                'expired_at'       => null,
            ]);

        return $this->db->affectedRows();
    }

    public function releaseTickets(array $ticketIds): int
    {
        if (empty($ticketIds)) {
            return 0;
        }

        $this->db->table($this->table)
            ->whereIn('id', $ticketIds)
            ->whereIn('status', [self::STATUS_RESERVADO, self::STATUS_VENDIDO])
            ->update([
                'status'          => self::STATUS_DISPONIBLE,
                'transaccion_id'   => null,
                'participant_id'   => null,
                'reserved_at'      => null,
                'expired_at'       => null,
            ]);

        return $this->db->affectedRows();
    }

    public function confirmTickets(array $ticketIds): int
    {
        if (empty($ticketIds)) {
            return 0;
        }

        $this->db->table($this->table)
            ->whereIn('id', $ticketIds)
            ->where('status', self::STATUS_RESERVADO)
            ->update([
                'status'        => self::STATUS_PAGADO,
                'fecha_pago'    => date('Y-m-d H:i:s'),
                'confirmed_at'  => date('Y-m-d H:i:s'),
            ]);

        return $this->db->affectedRows();
    }

    public function assignTicketsToParticipant(array $ticketIds, int $participantId): int
    {
        if (empty($ticketIds)) {
            return 0;
        }

        $this->db->table($this->table)
            ->whereIn('id', $ticketIds)
            ->where('status', self::STATUS_RESERVADO)
            ->update([
                'status'           => self::STATUS_ASIGNADO,
                'participant_id'    => $participantId,
                'fecha_asignacion' => date('Y-m-d H:i:s'),
            ]);

        return $this->db->affectedRows();
    }

    public function findByTransaccionId(string $transaccionId): array
    {
        return $this->where('transaccion_id', $transaccionId)->findAll();
    }

    public function findByParticipant(int $participantId): array
    {
        return $this->where('participant_id', $participantId)
            ->whereIn('status', [self::STATUS_PAGADO, self::STATUS_ASIGNADO])
            ->orderBy('fecha_pago', 'DESC')
            ->findAll();
    }

    public function cleanupExpiredReservations(int $hours = 2): int
    {
        $expiredTickets = $this->getExpiredReservedTickets($hours);

        if (empty($expiredTickets)) {
            return 0;
        }

        $ticketIds = array_column($expiredTickets, 'id');

        $this->db->table($this->table)
            ->whereIn('id', $ticketIds)
            ->update([
                'status'          => self::STATUS_DISPONIBLE,
                'transaccion_id'   => null,
                'participant_id'   => null,
                'reserved_at'      => null,
                'expired_at'       => null,
            ]);

        return $this->db->affectedRows();
    }
}