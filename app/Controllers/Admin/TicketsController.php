<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\DataTablesHelper;
use App\Models\SettingsModel;
use App\Models\TicketModel;

class TicketsController extends BaseController
{
    protected $ticketModel;
    protected $settingsModel;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->settingsModel = new SettingsModel();
    }

    public function index()
    {

        $settings = $this->settingsModel->first();
        $totalConfigurados = $settings ? (int) $settings['total_boletos'] : 0;
        $totalGenerados = $this->ticketModel->countAll();

        // ← Ya NO se pasa $tickets al view
        $data = [
            'totalConfigurados' => $totalConfigurados,
            'totalGenerados' => $totalGenerados,
            'title' => 'Generación de Boletos',
        ];

        return view('admin/tickets/generate', $data);
    }

    /**
     * Endpoint AJAX para DataTables server-side processing
     * GET tickets/data
     */
    public function data()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Acceso denegado']);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('tickets')
            ->select('id, numero, status, created_at, updated_at');

        $columns = ['numero', 'status', 'created_at', 'updated_at'];
        $searchable = ['numero', 'status'];   // columnas donde aplica búsqueda global

        return $this->response->setJSON(
            DataTablesHelper::response($this->request, $builder, $columns, $searchable)
        );
    }


    public function generate()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Acceso denegado']);
            }

            $settings = $this->settingsModel->first();
            if (!$settings) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No hay configuración de sorteo']);
            }

            $totalBoletos = (int) $settings['total_boletos'];

            // --- NUEVO: Límite de seguridad máximo ---
            $limiteMaximo = 50000;
            if ($totalBoletos > $limiteMaximo) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'El total de boletos configurado (' . number_format($totalBoletos) . ') supera el límite permitido de ' . number_format($limiteMaximo) . '.'
                ]);
            }

            $yaGenerados = $this->ticketModel->countAll();

            if ($yaGenerados >= $totalBoletos) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Todos los boletos ya han sido generados',
                    'completado' => true,
                    'generados' => $yaGenerados,
                    'total' => $totalBoletos
                ]);
            }

            $batchSize = 5000; // Generar de 5000 en 5000 para evitar problemas de memoria
            $boletosPorGenerar = $totalBoletos - $yaGenerados;
            $cantidadGenerar = min($batchSize, $boletosPorGenerar);

            $lote = [];

            // Ajuste: Calculamos los dígitos basados en el total exacto (ya no restamos 1)
            $digitos = strlen((string) $totalBoletos);
            if ($digitos < 5) {
                $digitos = 5; // Mínimo 5 dígitos para formato 00000
            }

            // Si yaGenerados es 0, empieza en 0. Si yaGenerados es 5000, empieza en 5000.
            $start = $yaGenerados;
            $end = $yaGenerados + $cantidadGenerar;

            $now = date('Y-m-d H:i:s');

            for ($i = $start; $i < $end; $i++) {
                // --- NUEVO: Sumamos 1 a $i para que empiece desde 1 en adelante ---
                $numeroBoleto = $i + 1;

                $lote[] = [
                    'numero' => str_pad($numeroBoleto, $digitos, '0', STR_PAD_LEFT),
                    'status' => 'disponible',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            if (!empty($lote)) {
                $this->ticketModel->insertBatch($lote);
            }

            $nuevosGenerados = $this->ticketModel->countAll();
            $completado = $nuevosGenerados >= $totalBoletos;

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Se generaron $cantidadGenerar boletos correctamente.",
                'completado' => $completado,
                'generados' => $nuevosGenerados,
                'total' => $totalBoletos,
                'progreso' => round(($nuevosGenerados / $totalBoletos) * 100, 2)
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Error al generar boletos: ' . $e->getMessage()
            ]);
        }
    }
}
