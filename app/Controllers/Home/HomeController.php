<?php

namespace App\Controllers\Home;

use App\Controllers\BaseController;
use App\Models\BancosModel;
use App\Models\ParticipantModel;
use App\Models\SettingsModel;
use App\Models\TicketModel;

class HomeController extends BaseController
{
    private function getSettings(): array
    {
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getSettings();

        if (!$settings) {
            return [
                'nombre_producto' => 'Samsung Galaxy S25 Ultra',
                'descripcion_producto' => '512GB, Titanio Plateado. Nuevo en caja.',
                'imagen_producto' => null,
                'boletos_minimos' => 3,
                'total_boletos' => 50000,
                'precio_boleto' => 3.00,
                'sorteo_activo' => 1,
            ];
        }

        return $settings;
    }

    public function index()
    {
        $ticketModel = new TicketModel();
        $settings = $this->getSettings();

        $carrusel = [];
        if (!empty($settings['imagen_producto'])) {
            $carrusel = array_filter(array_map('trim', explode(',', $settings['imagen_producto'])));
        }
        if (empty($carrusel)) {
            $carrusel = ['default.jpg'];
        }

        $data = [
            'titulo' => $settings['nombre_producto'] ?? 'Samsung Galaxy S25 Ultra',
            'descripcion' => $settings['descripcion_producto'] ?? '512GB, Titanio Plateado. Nuevo en caja.',
            'imagen' => $carrusel[0] ?? 'default.jpg',
            'carrusel' => $carrusel,
            'precio' => (float) ($settings['precio_boleto'] ?? 3.00),
            'moneda' => 'USD',
            'boletos_minimos' => (int) ($settings['boletos_minimos'] ?? 3),
            'porcentaje' => round($ticketModel->getSoldPercentage(), 0)
        ];

        return view('home/index', $data);
    }

    public function comprar()
    {
        $bancosModel = new BancosModel();
        $ticketModel = new TicketModel();
        $settings = $this->getSettings();

        $data = [
            'titulo' => $settings['nombre_producto'] ?? 'Samsung Galaxy S25 Ultra',
            'precio' => (float) ($settings['precio_boleto'] ?? 3.00),
            'moneda' => 'USD',
            'bancos' => $bancosModel->where('activo', 1)->findAll(),
            'boletos_disponibles' => $ticketModel->getAvailableCount(),
            'max_boletos' => (int) ($settings['boletos_maximos'] ?? 10),
            'boletos_minimos' => (int) ($settings['boletos_minimos'] ?? 3),
        ];

        return view('home/comprar', $data);
    }

    public function cedula()
    {
        try {

            $throttler = service('throttler');
            $ip = $this->request->getIPAddress();

            $data = $this->request->getJSON(true);
            $cedula = $data['cedula'] ?? null;

            if (!$cedula) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'Cédula requerida',
                    'csrfHash' => csrf_hash()
                ]);
            }

            // throttle por IP + cédula
            $key = $ip . '_' . $cedula;

            if (!$throttler->check($key, 5, MINUTE)) {
                return $this->response
                    ->setStatusCode(429)
                    ->setJSON([
                        'error' => true,
                        'message' => 'Demasiadas consultas',
                        'csrfHash' => csrf_hash()
                    ]);
            }

            $participantModel = new ParticipantModel();
            $participant = $participantModel->findByCedula($cedula);

            if ($participant) {
                return $this->response->setJSON([
                    'nombre'    => $participant['nombres'],
                    'apellidos' => $participant['apellidos'],
                    'cedula'   => $participant['cedula'],
                    'email'    => $participant['email'],
                    'telefono' => $participant['telefono'],
                    'exists'   => true,
                    'locked'   => true,
                    'csrfHash' => csrf_hash()
                ]);
            }

            $service = new \App\Services\ApiPrivadaService();
            $result = $service->getDataUser($cedula);

            if (!$result || empty($result['nombre'])) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'No se encontraron datos',
                    'locked' => false,
                    'csrfHash' => csrf_hash()
                ]);
            }

            $existing = $participantModel->findByCedula($cedula);
            if ($existing) {
                return $this->response->setJSON([
                    'nombre'    => $existing['nombres'],
                    'apellidos' => $existing['apellidos'],
                    'cedula'   => $cedula,
                    'email'    => $existing['email'],
                    'telefono' => $existing['telefono'],
                    'exists'   => true,
                    'locked'   => true,
                    'csrfHash' => csrf_hash()
                ]);
            }

            $apiNombre = trim($result['nombre'] ?? '');
            $split = $this->splitNameApi($apiNombre);

            $participantModel->skipValidation(true)->insert([
                'nombres'   => $split['nombres'],
                'apellidos' => $split['apellidos'],
                'full_name' => $apiNombre,
                'email'     => $result['email'] ?? '',
                'cedula'    => $cedula,
                'telefono'  => $result['telefono'] ?? '',
                'verificado' => 1,
            ]);

            $participant = $participantModel->findByCedula($cedula);

            return $this->response->setJSON([
                'nombre'    => $participant ? $participant['nombres'] : '',
                'apellidos' => $participant ? $participant['apellidos'] : '',
                'cedula'   => $cedula,
                'email'    => $participant ? $participant['email'] : '',
                'telefono' => $participant ? $participant['telefono'] : '',
                'exists'   => false,
                'locked'   => true,
                'csrfHash' => csrf_hash()
            ]);

        } catch (\Exception $e) {

            // log completo del error
            log_message('error', 'Error en API cedula: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'error' => true,
                    'message' => 'Error interno del servidor',
                    'csrfHash' => csrf_hash()
                ]);
        }
    }

    public function misBoletos()
    {
        return view('home/mis-boletos');
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
            'nombres'   => implode(' ', array_slice($parts, 2)),
        ];
    }
}
