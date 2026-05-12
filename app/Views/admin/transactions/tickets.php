<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">

<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <a href="<?= url_to('admin.transactions.index') ?>" class="btn btn-light btn-sm mb-3">
                <i class="ti ti-arrow-left me-1"></i> Volver a Transacciones
            </a>
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p class="text-muted">Transacción: <span class="fw-bold"><?= esc($transaction['transaccion_id']) ?></span></p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted fw-semibold mb-3">Información del Cliente</h6>
                <p class="mb-1"><span class="text-muted">Nombre:</span> <?= esc(($participant['nombres'] ?? '') . ' ' . ($participant['apellidos'] ?? '')) ?></p>
                <p class="mb-1"><span class="text-muted">Cédula:</span> <?= esc($participant['cedula'] ?? '-') ?></p>
                <p class="mb-1"><span class="text-muted">Email:</span> <?= esc($participant['email'] ?? '-') ?></p>
                <p class="mb-0"><span class="text-muted">Teléfono:</span> <?= esc($participant['telefono'] ?? '-') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted fw-semibold mb-3">Detalles de la Transacción</h6>
                <p class="mb-1"><span class="text-muted">Cantidad de boletos:</span> <span class="fw-bold"><?= (int) $transaction['cantidad_boletos'] ?></span></p>
                <p class="mb-1"><span class="text-muted">Total:</span> <span class="fw-bold">$<?= number_format((float) $transaction['total'], 2) ?></span></p>
                <p class="mb-1"><span class="text-muted">Método de pago:</span> <?= transaction_method_badge($transaction['metodo_pago']) ?></p>
                <p class="mb-0"><span class="text-muted">Completada:</span> <?= date('d M Y H:i', strtotime($transaction['completed_at'])) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted fw-semibold mb-3">Resumen de Boletos</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total de boletos</span>
                    <span class="fw-bold"><?= count($tickets) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Vendidos/Pagados</span>
                    <span class="fw-bold text-success"><?= count(array_filter($tickets, fn($t) => in_array($t['status'], ['vendido', 'pagado', 'asignado']))) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card table-responsive py-2">
            <table id="ticketsTable" class="table text-nowrap table-hover table-sm">
                <thead class="table-light border-light">
                    <tr>
                        <th>Número</th>
                        <th>Estado</th>
                        <th>Fecha Pago</th>
                        <th>Creado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr class="align-middle">
                            <td><span class="fw-bold"><?= esc($ticket['numero']) ?></span></td>
                            <td><?= ticket_status_badge($ticket['status']) ?></td>
                            <td><?= $ticket['fecha_pago'] ? date('d M Y H:i', strtotime($ticket['fecha_pago'])) : '-' ?></td>
                            <td><?= date('d M Y H:i', strtotime($ticket['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.colVis.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new DataTable('#ticketsTable', {
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.7/i18n/es-ES.json' },
            scrollX: true,
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {
                            extend: 'collection',
                            text: '<i class="ti ti-download"></i> Exportar',
                            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="ti ti-columns"></i>',
                            className: 'btn btn-secondary'
                        }
                    ]
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>