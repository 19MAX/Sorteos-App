<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">

<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p>Genera y administra los boletos disponibles para el sorteo.</p>
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
                        <th>Creado el</th>
                        <th>Actualizado el</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ← Vacío: DataTables lo llena vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== MODAL GENERAR BOLETOS ===== -->
<!-- (sin cambios, igual que antes) -->
<div id="generateModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generar Boletos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h4 class="mb-4">Estado de los Boletos</h4>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Boletos configurados:</span>
                    <span class="fw-bold fs-5" id="totalConfigurados"><?= number_format($totalConfigurados) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-muted">Boletos generados:</span>
                    <span class="fw-bold fs-5 text-primary"
                        id="totalGenerados"><?= number_format($totalGenerados) ?></span>
                </div>
                <?php
                $progreso = $totalConfigurados > 0
                    ? round(($totalGenerados / $totalConfigurados) * 100, 2)
                    : 0;
                ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Progreso de generación</small>
                        <small id="progresoTexto"><?= $progreso ?>%</small>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div id="progresoBarra"
                            class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                            role="progressbar" style="width: <?= $progreso ?>%;" aria-valuenow="<?= $progreso ?>"
                            aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="alert alert-info d-none" id="statusMessage">Iniciando generación...</div>
                <?php if ($totalConfigurados == 0): ?>
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        No se ha configurado el total de boletos.
                    </div>
                <?php elseif ($totalGenerados >= $totalConfigurados): ?>
                    <div class="alert alert-success">
                        <i class="ti ti-check me-2"></i>
                        Todos los boletos han sido generados correctamente.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                <?php if ($totalConfigurados > 0 && $totalGenerados < $totalConfigurados): ?>
                    <button id="btnGenerar" class="btn btn-primary py-2">
                        <i class="ti ti-ticket me-2"></i> Generar Boletos
                    </button>
                <?php endif; ?>
            </div>
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
<script type="module" src="<?= base_url('assets/js/ticketStatus.js') ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const table = new DataTable('#ticketsTable', {
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.7/i18n/es-ES.json' },
            serverSide: true,
            processing: true,
            scrollX: true,
            ajax: {
                url: '<?= url_to('admin.tickets.data') ?>',
                type: 'GET',
            },
            columns: [
                { data: 'numero', render: (d) => `<span class="fw-bold">${d}</span>` },
                { data: 'status', render: (d) => TicketStatus.renderBadge(d) },
                {
                    // Creado el
                    data: 'created_at',
                },
                {
                    // Actualizado el
                    data: 'updated_at',
                },
            ],
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {
                            text: '<i class="ti ti-ticket"></i> Generar Boletos',
                            className: 'btn btn-primary text-white',
                            action: () =>
                                new bootstrap.Modal(document.getElementById('generateModal')).show(),
                        },
                    ],
                },
            },
        });

        // ── Lógica de generación por lotes (sin cambios) ──────────────
        const btnGenerar = document.getElementById('btnGenerar');
        if (btnGenerar) {
            btnGenerar.addEventListener('click', function () {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generando...';
                const statusMessage = document.getElementById('statusMessage');
                statusMessage.classList.remove('d-none');
                statusMessage.className = 'alert alert-info';
                statusMessage.textContent = 'Iniciando generación de lotes...';
                generarLote();
            });
        }

        function generarLote() {
            fetch('<?= url_to('admin.tickets.generate.process') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('totalGenerados').textContent =
                            data.generados.toLocaleString();

                        const progreso = data.progreso ??
                            ((data.generados / data.total) * 100).toFixed(2);

                        document.getElementById('progresoTexto').textContent = progreso + '%';

                        const barra = document.getElementById('progresoBarra');
                        barra.style.width = progreso + '%';
                        barra.setAttribute('aria-valuenow', progreso);

                        const statusMessage = document.getElementById('statusMessage');
                        statusMessage.textContent = data.message;

                        if (data.completado) {
                            statusMessage.className = 'alert alert-success';
                            statusMessage.innerHTML = '<i class="ti ti-check me-2"></i> ' + data.message;
                            if (btnGenerar) btnGenerar.style.display = 'none';

                            // ← Recargar solo la tabla, no toda la página
                            table.ajax.reload();

                        } else {
                            setTimeout(generarLote, 500);
                        }
                    } else {
                        mostrarError(data.message || 'Error desconocido.');
                    }
                })
                .catch(() => mostrarError('Error de conexión al generar boletos.'));
        }

        function mostrarError(mensaje) {
            const statusMessage = document.getElementById('statusMessage');
            statusMessage.className = 'alert alert-danger';
            statusMessage.innerHTML = '<i class="ti ti-alert-triangle me-2"></i> ' + mensaje;
            if (btnGenerar) {
                btnGenerar.disabled = false;
                btnGenerar.innerHTML = '<i class="ti ti-ticket me-2"></i> Continuar Generación';
            }
        }
    });
</script>
<?= $this->endSection() ?>