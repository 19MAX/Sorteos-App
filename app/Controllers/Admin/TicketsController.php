<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\DataTablesHelper;
use App\Models\SettingsModel;
use App\Models\TicketModel;
use App\Models\ParticipantModel;
use App\Models\TransactionModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        $settings = $this->settingsModel->getSettings();
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
        $builder = $db->table('tickets t')
            ->select('t.id, t.numero, t.status, t.created_at, t.updated_at, t.reserved_at, t.confirmed_at,
                      p.id as participant_db_id, p.nombres, p.apellidos, p.telefono, p.email, p.cedula,
                      tr.id as transaccion_db_id, tr.transaccion_id as transaccion_code, tr.metodo_pago, tr.status as transaccion_status, tr.total, tr.cantidad_boletos')
            ->join('participants p', 'p.id = t.participant_id', 'left')
            ->join('transactions tr', 'tr.transaccion_id = t.transaccion_id', 'left');

        // Filtros personalizados
        $status = $this->request->getGet('status');
        $reservedFrom = $this->request->getGet('reserved_from');
        $reservedTo = $this->request->getGet('reserved_to');
        $confirmedFrom = $this->request->getGet('confirmed_from');
        $confirmedTo = $this->request->getGet('confirmed_to');
        $participant = $this->request->getGet('participant');
        $transaccion = $this->request->getGet('transaccion');

        if (!empty($status)) {
            $builder->where('t.status', $status);
        }
        if (!empty($reservedFrom)) {
            $builder->where('t.reserved_at >=', $reservedFrom . ' 00:00:00');
        }
        if (!empty($reservedTo)) {
            $builder->where('t.reserved_at <=', $reservedTo . ' 23:59:59');
        }
        if (!empty($confirmedFrom)) {
            $builder->where('t.confirmed_at >=', $confirmedFrom . ' 00:00:00');
        }
        if (!empty($confirmedTo)) {
            $builder->where('t.confirmed_at <=', $confirmedTo . ' 23:59:59');
        }
        if (!empty($participant)) {
            $builder->groupStart()
                ->like('p.nombres', $participant)
                ->orLike('p.apellidos', $participant)
                ->orLike('p.email', $participant)
                ->orLike('p.cedula', $participant)
            ->groupEnd();
        }
        if (!empty($transaccion)) {
            $builder->like('tr.transaccion_id', $transaccion);
        }

        $columns = ['numero', 'status', 'created_at', 'updated_at', 'reserved_at', 'confirmed_at',
                    'nombres', 'apellidos', 'telefono', 'transaccion_code', 'metodo_pago'];
        $searchable = ['t.numero', 't.status', 'p.nombres', 'p.apellidos', 'p.email', 'tr.transaccion_id'];

        return $this->response->setJSON(
            DataTablesHelper::response($this->request, $builder, $columns, $searchable)
        );
    }

    /**
     * Exportación de boletos a Excel
     * GET admin/tickets/export
     */
    public function export()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tickets t')
            ->select('t.id, t.numero, t.status, t.created_at, t.updated_at, t.reserved_at, t.confirmed_at,
                      p.id as participant_db_id, p.nombres, p.apellidos, p.telefono, p.email, p.cedula,
                      tr.id as transaccion_db_id, tr.transaccion_id as transaccion_code, tr.metodo_pago, tr.status as transaccion_status, tr.total, tr.cantidad_boletos')
            ->join('participants p', 'p.id = t.participant_id', 'left')
            ->join('transactions tr', 'tr.transaccion_id = t.transaccion_id', 'left');

        // Aplicar mismos filtros que en data()
        $status = $this->request->getGet('status');
        $reservedFrom = $this->request->getGet('reserved_from');
        $reservedTo = $this->request->getGet('reserved_to');
        $confirmedFrom = $this->request->getGet('confirmed_from');
        $confirmedTo = $this->request->getGet('confirmed_to');
        $participant = $this->request->getGet('participant');
        $transaccion = $this->request->getGet('transaccion');

        if (!empty($status)) {
            $builder->where('t.status', $status);
        }
        if (!empty($reservedFrom)) {
            $builder->where('t.reserved_at >=', $reservedFrom . ' 00:00:00');
        }
        if (!empty($reservedTo)) {
            $builder->where('t.reserved_at <=', $reservedTo . ' 23:59:59');
        }
        if (!empty($confirmedFrom)) {
            $builder->where('t.confirmed_at >=', $confirmedFrom . ' 00:00:00');
        }
        if (!empty($confirmedTo)) {
            $builder->where('t.confirmed_at <=', $confirmedTo . ' 23:59:59');
        }
        if (!empty($participant)) {
            $builder->groupStart()
                ->like('p.nombres', $participant)
                ->orLike('p.apellidos', $participant)
                ->orLike('p.email', $participant)
                ->orLike('p.cedula', $participant)
            ->groupEnd();
        }
        if (!empty($transaccion)) {
            $builder->like('tr.transaccion_id', $transaccion);
        }

        $builder->orderBy('t.id', 'asc');

        $tickets = $builder->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = [
            'ID', 'Número', 'Estado', 'Participante', 'Teléfono', 'Email', 'Cédula',
            'Transacción', 'Método de Pago', 'Status Transacción', 'Total Transacción', 'Cantidad Boletos',
            'Fecha Reservación', 'Fecha Confirmación', 'Creado', 'Actualizado'
        ];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:P1')->getFont()->setBold(true);

        // Datos
        $row = 2;
        foreach ($tickets as $ticket) {
            $sheet->setCellValue('A' . $row, $ticket['id']);
            $sheet->setCellValue('B' . $row, $ticket['numero']);
            $sheet->setCellValue('C' . $row, $ticket['status']);
            $sheet->setCellValue('D' . $row, trim(($ticket['nombres'] ?? '') . ' ' . ($ticket['apellidos'] ?? '')));
            $sheet->setCellValue('E' . $row, $ticket['telefono'] ?? '');
            $sheet->setCellValue('F' . $row, $ticket['email'] ?? '');
            $sheet->setCellValue('G' . $row, $ticket['cedula'] ?? '');
            $sheet->setCellValue('H' . $row, $ticket['transaccion_code'] ?? '');
            $sheet->setCellValue('I' . $row, $ticket['metodo_pago'] ?? '');
            $sheet->setCellValue('J' . $row, $ticket['transaccion_status'] ?? '');
            $sheet->setCellValue('K' . $row, $ticket['total'] ?? '');
            $sheet->setCellValue('L' . $row, $ticket['cantidad_boletos'] ?? '');
            $sheet->setCellValue('M' . $row, $ticket['reserved_at'] ?? '');
            $sheet->setCellValue('N' . $row, $ticket['confirmed_at'] ?? '');
            $sheet->setCellValue('O' . $row, $ticket['created_at']);
            $sheet->setCellValue('P' . $row, $ticket['updated_at']);
            $row++;
        }

        // Auto width
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'boletos_export_' . date('Y-m-d_His') . '.xlsx';
        $filepath = WRITEPATH . 'exports/' . $filename;

        if (!is_dir(WRITEPATH . 'exports')) {
            mkdir(WRITEPATH . 'exports', 0777, true);
        }

        $writer->save($filepath);

        return $this->response->download($filepath, null)
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }


    public function generate()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Acceso denegado']);
            }

            $settings = $this->settingsModel->getSettings();
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

    /**
     * Get single ticket details for modal
     * GET admin/tickets/:id
     */
    public function show($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado']);
        }

        $db = \Config\Database::connect();
        $ticket = $db->table('tickets t')
            ->select('t.id, t.numero, t.status, t.created_at, t.updated_at, t.reserved_at, t.confirmed_at,
                      p.id as participant_db_id, p.nombres, p.apellidos, p.telefono, p.email, p.cedula,
                      tr.id as transaccion_db_id, tr.transaccion_id as transaccion_code, tr.metodo_pago, tr.status as transaccion_status, tr.total, tr.cantidad_boletos')
            ->join('participants p', 'p.id = t.participant_id', 'left')
            ->join('transactions tr', 'tr.transaccion_id = t.transaccion_id', 'left')
            ->where('t.id', $id)
            ->get()
            ->getRowArray();

        if (!$ticket) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Boleto no encontrado']);
        }

        return $this->response->setJSON(['data' => [$ticket]]);
    }
}
