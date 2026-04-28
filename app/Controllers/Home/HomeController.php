<?php

namespace App\Controllers\Home;

use App\Controllers\BaseController;
use App\Models\BancosModel;

class HomeController extends BaseController
{
    public function index()
    {
        $data = [
            'titulo' => "Samsung Galaxy S25 Ultra",
            'descripcion' => "512GB, Titanio Plateado. Nuevo en caja.",
            'imagen' => "xyz789.jpg",
            'carrusel' => ["c1.jpg", "c2.jpg", "c3.jpg"],
            'precio' => 3.00,
            'moneda' => "USD",
            'porcentaje' => 0
        ];

        return view('home/index', $data);
    }

    public function comprar()
    {
        $bancosModel = new BancosModel();
        $data = [
            'titulo' => "Samsung Galaxy S25 Ultra",
            'precio' => 3.00,
            'moneda' => "USD",
            'bancos' => $bancosModel->where('activo', 1)->findAll()
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

            $service = new \App\Services\ApiPrivadaService();
            $result = $service->getDataUser($cedula);

            if (!$result) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'No se encontraron datos',
                    'csrfHash' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                ...$result,
                'csrfHash' => csrf_hash()
            ]);

        } catch (\Throwable $e) {

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
}
