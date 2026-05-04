<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ParticipantModel;
use App\Models\TicketModel;
use App\Models\TransactionModel;
use App\Models\SettingsModel;

class OrdenController extends BaseController
{
    private const RATE_LIMIT_MAX = 3;
    private const RATE_LIMIT_SECONDS = 60;
    private const MAX_TICKETS_NORMAL = 20;
    private const MAX_TICKETS_SCARCITY = 5;
    private const RESERVATION_HOURS = 2;

    private ParticipantModel $participantModel;
    private TicketModel $ticketModel;
    private TransactionModel $transactionModel;
    private SettingsModel $settingsModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->ticketModel = new TicketModel();
        $this->transactionModel = new TransactionModel();
        $this->settingsModel = new SettingsModel();
    }

    public function crear()
    {
        try {
            $throttler = service('throttler');
            $ip = $this->request->getIPAddress();

            if (!$throttler->check('orden_' . $ip, self::RATE_LIMIT_MAX, self::RATE_LIMIT_SECONDS)) {
                return $this->response
                    ->setStatusCode(429)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Demasiadas solicitudes. Por favor espera un momento.',
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $data = $this->request->getJSON(true);

            $validation = $this->validateOrderData($data);
            if (!$validation['valid']) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON([
                        'success' => false,
                        'message' => $validation['message'],
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $qty = (int) ($data['qty'] ?? 0);
            $maxTickets = $this->getMaxTickets();

            if ($qty > $maxTickets) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON([
                        'success' => false,
                        'message' => "Solo puedes reservar máximo {$maxTickets} boletos por transacción.",
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $availableCount = $this->ticketModel->getAvailableCount();
            if ($availableCount < $qty) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON([
                        'success' => false,
                        'message' => 'No hay suficientes boletos disponibles.',
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $nameParts = $this->splitName($data['nombre'] ?? '');

            $participant = $this->participantModel->findOrCreate([
                'nombres'   => $nameParts['nombres'],
                'apellidos' => $nameParts['apellidos'],
                'email'     => $data['email'] ?? '',
                'cedula'    => $data['cedula'] ?? '',
                'telefono'  => $data['whatsapp'] ?? '',
            ]);

            if (!$participant) {
                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Error al procesar el participante.',
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $settings = $this->getSettings();
            $price = (float) ($settings['precio_boleto'] ?? 3.00);
            $total = $price * $qty;

            $ticketIds = $this->ticketModel
                ->where('status', 'disponible')
                ->orderBy('id', 'RANDOM')
                ->limit($qty)
                ->findColumn('id');

            if (empty($ticketIds) || count($ticketIds) < $qty) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON([
                        'success' => false,
                        'message' => 'No se pudieron bloquear los boletos. Intenta nuevamente.',
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $transaccionId = 'TXN-' . strtoupper(bin2hex(random_bytes(8)));
            $expiredAt = date('Y-m-d H:i:s', strtotime('+' . self::RESERVATION_HOURS . ' hours'));

            $reservedCount = $this->ticketModel->reserveTickets(
                $ticketIds,
                $transaccionId,
                $participant['id'],
                self::RESERVATION_HOURS
            );

            if ($reservedCount !== $qty) {
                return $this->response
                    ->setStatusCode(409)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Algunos boletos fueron tomados por otro usuario. Por favor selecciona nuevamente.',
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $this->transactionModel->insert([
                'transaccion_id'   => $transaccionId,
                'participant_id'   => $participant['id'],
                'cantidad_boletos' => $qty,
                'total'            => $total,
                'metodo_pago'      => 'transferencia',
                'status'           => 'pendiente',
                'boletos_asignados' => implode(',', $ticketIds),
                'expired_at'       => $expiredAt,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Reservación creada exitosamente',
                'data' => [
                    'transaccion_id' => $transaccionId,
                    'boletos' => $qty,
                    'total' => $total,
                    'expira_en' => self::RESERVATION_HOURS . ' horas',
                    'participant_id' => $participant['id']
                ],
                'csrfHash' => csrf_hash()
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Error en OrdenController::crear: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Error interno del servidor',
                    'csrfHash' => csrf_hash()
                ]);
        }
    }

    private function splitName(string $fullName): array
    {
        $parts = array_filter(explode(' ', trim($fullName)));

        if (count($parts) === 0) {
            return ['nombres' => '', 'apellidos' => ''];
        }

        if (count($parts) === 1) {
            return ['nombres' => $parts[0], 'apellidos' => ''];
        }

        $nombres = $parts[0];
        $apellidos = implode(' ', array_slice($parts, 1));

        return [
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
        ];
    }

    private function validateOrderData(array $data): array
    {
        if (empty($data['cedula'])) {
            return ['valid' => false, 'message' => 'La cédula es obligatoria'];
        }

        if (empty($data['nombre'])) {
            return ['valid' => false, 'message' => 'El nombre es obligatorio'];
        }

        if (empty($data['email'])) {
            return ['valid' => false, 'message' => 'El correo electrónico es obligatorio'];
        }

        if (empty($data['whatsapp'])) {
            return ['valid' => false, 'message' => 'El número de WhatsApp es obligatorio'];
        }

        if (empty($data['qty']) || (int) $data['qty'] < 1) {
            return ['valid' => false, 'message' => 'La cantidad de boletos debe ser al menos 1'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'El correo electrónico no es válido'];
        }

        return ['valid' => true];
    }

    private function getMaxTickets(): int
    {
        if ($this->ticketModel->isScarcityMode()) {
            return self::MAX_TICKETS_SCARCITY;
        }

        return self::MAX_TICKETS_NORMAL;
    }

    private function getSettings(): array
    {
        $settings = $this->settingsModel->first();
        return $settings ?? [
            'precio_boleto' => 3.00,
            'total_boletos' => 1000,
        ];
    }

    public function verificar()
    {
        try {
            $transaccionId = $this->request->getGet('transaccion_id');

            if (empty($transaccionId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transacción no especificada'
                ]);
            }

            $transaction = $this->transactionModel->findByTransaccionId($transaccionId);

            if (!$transaction) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transacción no encontrada'
                ]);
            }

            $tickets = $this->ticketModel->findByTransaccionId($transaccionId);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'transaccion' => $transaction,
                    'boletos' => $tickets,
                    'cantidad' => count($tickets)
                ]
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Error en OrdenController::verificar: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Error interno del servidor'
                ]);
        }
    }
}