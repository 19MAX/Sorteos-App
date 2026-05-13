<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ParticipantModel;
use App\Models\TicketModel;
use App\Models\TransactionModel;
use App\Services\ApiPrivadaService;
use App\Services\PhysicalSaleService;
use CodeIgniter\Database\Exceptions\DatabaseException;

class PhysicalSaleController extends BaseController
{
    protected $participantModel;
    protected $ticketModel;
    protected $transactionModel;
    protected $apiPrivada;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->ticketModel = new TicketModel();
        $this->transactionModel = new TransactionModel();
        $this->apiPrivada = new ApiPrivadaService();
        helper(['transaction_status']);
    }

    public function index()
    {
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getSettings();

        $data = [
            'title' => 'Venta de Boletos Físicos',
            'precio_boleto' => (float) ($settings['precio_boleto'] ?? 1),
            'boletos_minimos' => (int) ($settings['boletos_minimos'] ?? 1),
            'boletos_maximos' => (int) ($settings['boletos_maximos'] ?? 200),
            'boletos_disponibles' => $this->ticketModel->getAvailableCount(),
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash(),
        ];

        return view('admin/physical-sales/index', $data);
    }

    public function buscarCedula()
    {
        if (!session()->get('admin_logged_in')) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Acceso denegado',
                'csrfHash' => csrf_hash()
            ]);
        }

        $input = file_get_contents('php://input');
        log_message('debug', '[PhysicalSale] Raw input: ' . $input);
        $json = json_decode($input);

        log_message('debug', '[PhysicalSale] JSON decode result: ' . ($json === null ? 'NULL' : 'OK') . ', error: ' . json_last_error_msg());
        log_message('debug', '[PhysicalSale] JSON cedula: ' . ($json->cedula ?? 'MISSING'));

        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', '[PhysicalSale] JSON parse error: ' . json_last_error_msg() . ' | input: ' . $input);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al procesar datos',
                'csrfHash' => csrf_hash()
            ]);
        }

        $cedula = trim($json->cedula ?? '');

        if (empty($cedula)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cédula es requerida',
                'csrfHash' => csrf_hash()
            ]);
        }

        $cedula = preg_replace('/[^0-9]/', '', $cedula);

        $participant = $this->participantModel->findByCedula($cedula);

        if ($participant) {
            return $this->response->setJSON([
                'status' => 'success',
                'found' => true,
                'participant' => [
                    'id' => $participant['id'],
                    'cedula' => $participant['cedula'],
                    'nombres' => $participant['nombres'],
                    'apellidos' => $participant['apellidos'],
                    'full_name' => $participant['full_name'],
                    'email' => $participant['email'],
                    'telefono' => $participant['telefono'],
                    'verificado' => $participant['verificado'],
                ],
                'csrfHash' => csrf_hash()
            ]);
        }

        $apiData = $this->apiPrivada->getDataUser($cedula);

        if ($apiData && !empty($apiData['nombre'])) {
            $split = $this->splitNameApi($apiData['nombre'] ?? '');
            return $this->response->setJSON([
                'status' => 'success',
                'found' => false,
                'from_api' => true,
                'participant' => [
                    'cedula' => $cedula,
                    'nombres' => $split['nombres'],
                    'apellidos' => $split['apellidos'],
                    'full_name' => trim($apiData['nombre'] ?? ''),
                    'email' => $apiData['email'] ?? '',
                    'telefono' => $apiData['telefono'] ?? '',
                ],
                'csrfHash' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'found' => false,
            'from_api' => false,
            'participant' => [
                'cedula' => $cedula,
                'nombres' => '',
                'apellidos' => '',
                'full_name' => '',
                'email' => '',
                'telefono' => '',
            ],
            'csrfHash' => csrf_hash()
        ]);
    }

    private function splitNameApi(string $fullName): array
    {
        $parts = array_filter(explode(' ', trim($fullName)));
        $count = count($parts);

        if ($count === 0) {
            return ['nombres' => '', 'apellidos' => ''];
        }
        if ($count === 1) {
            return ['nombres' => $parts[0], 'apellidos' => ''];
        }
        if ($count === 2) {
            return ['nombres' => $parts[1], 'apellidos' => $parts[0]];
        }
        if ($count === 3) {
            return ['nombres' => $parts[2], 'apellidos' => $parts[0] . ' ' . $parts[1]];
        }

        return [
            'apellidos' => $parts[0] . ' ' . $parts[1],
            'nombres' => implode(' ', array_slice($parts, 2)),
        ];
    }

    public function guardarParticipante()
    {
        if (!session()->get('admin_logged_in')) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Acceso denegado',
                'csrfHash' => csrf_hash()
            ]);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?? [];

        $cedula = preg_replace('/[^0-9]/', '', $data['cedula'] ?? '');

        if (empty($cedula)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cédula es requerida',
                'csrfHash' => csrf_hash()
            ]);
        }

        if (empty($data['nombres']) || empty($data['apellidos'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nombres y apellidos son requeridos',
                'csrfHash' => csrf_hash()
            ]);
        }

        $participantData = [
            'nombres' => trim($data['nombres']),
            'apellidos' => trim($data['apellidos']),
            'full_name' => trim($data['nombres']) . ' ' . trim($data['apellidos']),
            'email' => trim($data['email'] ?? ''),
            'cedula' => $cedula,
            'telefono' => preg_replace('/[^0-9]/', '', $data['telefono'] ?? ''),
            'verificado' => 1,
        ];

        $existing = $this->participantModel->findByCedula($cedula);

        if ($existing) {
            $this->participantModel->update($existing['id'], $participantData);
            $participantId = $existing['id'];
        } else {
            $this->participantModel->skipValidation(true)->insert($participantData);
            $participantId = $this->participantModel->getInsertID();
        }

        $participant = $this->participantModel->find($participantId);

        return $this->response->setJSON([
            'status' => 'success',
            'participant_id' => $participantId,
            'participant' => $participant,
            'csrfHash' => csrf_hash()
        ]);
    }

    public function venderBoletos()
    {
        if (!session()->get('admin_logged_in')) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Acceso denegado', 'csrfHash' => csrf_hash()]);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $service = new PhysicalSaleService();
        $ok = $service->sell(
            participantId: (int) ($input['participant_id'] ?? 0),
            cantidad: (int) ($input['cantidad'] ?? 0),
            montoRecibido: (float) ($input['monto_recibido'] ?? 0),
            observaciones: ($input['observaciones'] ?? ''),
            adminId: (int) session()->get('admin_id'),
        );

        return $this->response->setJSON([
            'status' => $ok ? 'success' : 'error',
            'message' => $service->result['message'],
            'data' => $service->result['data'],
            'csrfHash' => csrf_hash(),
        ]);
    }

}