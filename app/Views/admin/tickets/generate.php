<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?= $this->section('style') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">
<?= $this->endSection() ?>

<!-- ── Encabezado ─────────────────────────────────────────────── -->
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p class="text-muted mb-0">Genera y administra los boletos disponibles para el sorteo.</p>
        </div>
    </div>
</div>

<!-- ── Filtros ────────────────────────────────────────────────── -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="filterStatus" class="form-label small fw-semibold">Estado</label>
                        <select id="filterStatus" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="reservado">Reservado</option>
                            <option value="procesando">Procesando</option>
                            <!-- <option value="vendido">Vendido</option> -->
                            <option value="pagado">Pagado</option>
                            <!-- <option value="asignado">Asignado</option> -->
                            <!-- <option value="expirado">Expirado</option> -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterReservedFrom" class="form-label small fw-semibold">Reservación desde</label>
                        <input type="date" id="filterReservedFrom" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label for="filterReservedTo" class="form-label small fw-semibold">Reservación hasta</label>
                        <input type="date" id="filterReservedTo" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label for="filterConfirmedFrom" class="form-label small fw-semibold">Confirmación desde</label>
                        <input type="date" id="filterConfirmedFrom" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label for="filterConfirmedTo" class="form-label small fw-semibold">Confirmación hasta</label>
                        <input type="date" id="filterConfirmedTo" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label for="filterParticipant" class="form-label small fw-semibold">Participante</label>
                        <input type="text" id="filterParticipant" class="form-control form-control-sm"
                            placeholder="Nombre, email, cédula...">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="filterTransaccion" class="form-label small fw-semibold">Transacción</label>
                        <input type="text" id="filterTransaccion" class="form-control form-control-sm"
                            placeholder="ID transacción...">
                    </div>
                    <div class="col-md-9 d-flex align-items-end gap-2">
                        <button id="btnApplyFilters" class="btn btn-primary btn-sm">
                            <i class="ti ti-search me-1"></i> Aplicar Filtros
                        </button>
                        <button id="btnClearFilters" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-x me-1"></i> Limpiar
                        </button>
                        <button id="btnExportExcel" class="btn btn-success btn-sm">
                            <i class="ti ti-file-export me-1"></i> Exportar Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Tabla ──────────────────────────────────────────────────── -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body table-responsive py-2">
                <table id="ticketsTable" class="table text-nowrap table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Número</th>
                            <th>Estado</th>
                            <th>Participante</th>
                            <th>Teléfono</th>
                            <th>Transacción</th>
                            <th>Método Pago</th>
                            <th>Reserved At</th>
                            <th>Confirmed At</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL — GENERAR BOLETOS
     ══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="generateModalLabel">
                    <i class="ti ti-ticket me-2 text-primary"></i> Generar Boletos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <h6 class="text-muted fw-semibold mb-3">Estado de los Boletos</h6>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Boletos configurados:</span>
                    <span class="fw-bold fs-5" id="totalConfigurados">
                        <?= number_format($totalConfigurados) ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Boletos generados:</span>
                    <span class="fw-bold fs-5 text-primary" id="totalGenerados">
                        <?= number_format($totalGenerados) ?>
                    </span>
                </div>

                <?php
                $progreso = $totalConfigurados > 0
                    ? round(($totalGenerados / $totalConfigurados) * 100, 2)
                    : 0;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Progreso de generación</small>
                        <small id="progresoTexto" class="fw-semibold"><?= $progreso ?>%</small>
                    </div>
                    <div class="progress" style="height: 18px; border-radius: 6px;">
                        <div id="progresoBarra" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: <?= $progreso ?>%;" aria-valuenow="<?= $progreso ?>"
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>

                <div class="alert d-none mb-0" id="statusMessage" role="alert"></div>

                <?php if ($totalConfigurados === 0): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="ti ti-alert-triangle me-2"></i>
                        No se ha configurado el total de boletos.
                    </div>
                <?php elseif ($totalGenerados >= $totalConfigurados): ?>
                    <div class="alert alert-success mb-0">
                        <i class="ti ti-circle-check me-2"></i>
                        Todos los boletos han sido generados correctamente.
                    </div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                <?php if ($totalConfigurados > 0 && $totalGenerados < $totalConfigurados): ?>
                    <button id="btnGenerar" class="btn btn-primary">
                        <i class="ti ti-ticket me-2"></i> Generar Boletos
                    </button>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     MODAL — VER DETALLES DEL BOLETO
     ══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">
                    <i class="ti ti-eye me-2 text-primary"></i> Detalles del Boleto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- El contenido se inyecta dinámicamente -->
            <div class="modal-body" id="detailsModalContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.bootstrap5.js"></script>
<script type="module" src="<?= base_url('assets/js/ticketStatus.js') ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ──────────────────────────────────────────────────────────────
        // INSTANCIAS DE MODALES (Bootstrap 5 nativo)
        // ──────────────────────────────────────────────────────────────
        const generateModal = new bootstrap.Modal(document.getElementById('generateModal'));
        const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));

        // ──────────────────────────────────────────────────────────────
        // DATATABLE
        // ──────────────────────────────────────────────────────────────
        const table = new DataTable('#ticketsTable', {
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.7/i18n/es-ES.json' },
            serverSide: true,
            processing: true,
            scrollX: true,
            ajax: {
                url: '<?= url_to('admin.tickets.data') ?>',
                type: 'GET',
                data: function (d) {
                    d.status = $('#filterStatus').val();
                    d.reserved_from = $('#filterReservedFrom').val();
                    d.reserved_to = $('#filterReservedTo').val();
                    d.confirmed_from = $('#filterConfirmedFrom').val();
                    d.confirmed_to = $('#filterConfirmedTo').val();
                    d.participant = $('#filterParticipant').val();
                    d.transaccion = $('#filterTransaccion').val();
                },
            },
            columns: [
                {
                    data: 'numero',
                    render: (d) => `<span class="fw-bold">${d}</span>`,
                },
                {
                    data: 'status',
                    render: (d) => TicketStatus.renderBadge(d),
                },
                {
                    data: 'nombres',
                    render: (d, type, row) => {
                        if (!d) return '<span class="text-muted">-</span>';
                        const nombre = `${d} ${row.apellidos || ''}`.trim();
                        return `<span class="text-truncate d-inline-block" style="max-width:140px;" title="${nombre}">${nombre}</span>`;
                    },
                },
                {
                    data: 'telefono',
                    render: (d) => d || '<span class="text-muted">-</span>',
                },
                {
                    data: 'transaccion_code',
                    render: (d) => d
                        ? `<span class="text-primary small font-monospace">${d}</span>`
                        : '<span class="text-muted">-</span>',
                },
                {
                    data: 'metodo_pago',
                    render: (d) => {
                        const map = {
                            tarjeta: '<span class="badge bg-primary">Tarjeta</span>',
                            transferencia: '<span class="badge bg-warning text-dark">Transferencia</span>',
                            fisico: '<span class="badge bg-secondary">Físico</span>',
                        };
                        return d ? (map[d] ?? `<span class="badge bg-light text-dark">${d}</span>`) : '<span class="text-muted">-</span>';
                    },
                },
                {
                    data: 'reserved_at',
                    render: (d) => d
                        ? `<span class="small text-muted">${d.split(' ')[0]}</span>`
                        : '<span class="text-muted">-</span>',
                },
                {
                    data: 'confirmed_at',
                    render: (d) => d
                        ? `<span class="small text-muted">${d.split(' ')[0]}</span>`
                        : '<span class="text-muted">-</span>',
                },
                {
                    data: 'created_at',
                    render: (d) => `<span class="small text-muted">${d.split(' ')[0]}</span>`,
                },
                {
                    data: 'updated_at',
                    render: (d) => `<span class="small text-muted">${d.split(' ')[0]}</span>`,
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: (d) => `
                    <button class="btn btn-sm btn-outline-primary"
                            onclick="viewTicketDetails(${d})"
                            title="Ver detalles">
                        <i class="ti ti-eye"></i>
                    </button>
                `,
                },
            ],
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {
                            text: '<i class="ti ti-ticket me-1"></i> Generar Boletos',
                            className: 'btn btn-primary text-white',
                            action: () => generateModal.show(),
                        },
                    ],
                },
            },
        });

        // ──────────────────────────────────────────────────────────────
        // FILTROS
        // ──────────────────────────────────────────────────────────────
        $('#btnApplyFilters').on('click', () => table.ajax.reload());

        $('#btnClearFilters').on('click', function () {
            $('#filterStatus, #filterReservedFrom, #filterReservedTo, #filterConfirmedFrom, #filterConfirmedTo, #filterParticipant, #filterTransaccion').val('');
            table.ajax.reload();
        });

        // ──────────────────────────────────────────────────────────────
        // EXPORTAR EXCEL
        // ──────────────────────────────────────────────────────────────
        $('#btnExportExcel').on('click', function () {
            const params = new URLSearchParams({
                status: $('#filterStatus').val(),
                reserved_from: $('#filterReservedFrom').val(),
                reserved_to: $('#filterReservedTo').val(),
                confirmed_from: $('#filterConfirmedFrom').val(),
                confirmed_to: $('#filterConfirmedTo').val(),
                participant: $('#filterParticipant').val(),
                transaccion: $('#filterTransaccion').val(),
            });
            window.location.href = '<?= url_to('admin.tickets.export') ?>?' + params.toString();
        });

        // ──────────────────────────────────────────────────────────────
        // VER DETALLES DEL BOLETO
        // ──────────────────────────────────────────────────────────────
        window.viewTicketDetails = function (id) {
            // Mostrar spinner mientras carga
            document.getElementById('detailsModalContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `;
            detailsModal.show();

            const url = '<?= url_to('admin.tickets.show', 0) ?>'.replace('/0', '/' + id);

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(resp => {
                    if (!resp.data?.length) {
                        document.getElementById('detailsModalContent').innerHTML =
                            '<div class="alert alert-warning m-3">Boleto no encontrado.</div>';
                        return;
                    }
                    const row = resp.data[0];
                    document.getElementById('detailsModalContent').innerHTML = buildDetailsHTML(row);
                })
                .catch(() => {
                    document.getElementById('detailsModalContent').innerHTML =
                        '<div class="alert alert-danger m-3">Error al cargar los detalles.</div>';
                });
        };

        function buildDetailsHTML(row) {
            const dash = (val) => val || '<span class="text-muted">-</span>';
            return `
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted text-uppercase small mb-3 border-bottom pb-2">
                        <i class="ti ti-ticket me-1"></i> Información del Boleto
                    </h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted small fw-semibold" style="width:40%">ID</td>
                                <td>${dash(row.id)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Número</td>
                                <td class="fw-bold">${dash(row.numero)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Estado</td>
                                <td>${TicketStatus.renderBadge(row.status)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Creado</td>
                                <td><span class="small">${dash(row.created_at)}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Actualizado</td>
                                <td><span class="small">${dash(row.updated_at)}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Reserved At</td>
                                <td><span class="small">${dash(row.reserved_at)}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Confirmed At</td>
                                <td><span class="small">${dash(row.confirmed_at)}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted text-uppercase small mb-3 border-bottom pb-2">
                        <i class="ti ti-user me-1"></i> Participante
                    </h6>
                    <table class="table table-sm table-borderless mb-4">
                        <tbody>
                            <tr>
                                <td class="text-muted small fw-semibold" style="width:40%">Nombre</td>
                                <td>${dash(row.nombres)} ${row.apellidos ?? ''}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Teléfono</td>
                                <td>${dash(row.telefono)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Email</td>
                                <td><span class="small">${dash(row.email)}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Cédula</td>
                                <td>${dash(row.cedula)}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h6 class="fw-semibold text-muted text-uppercase small mb-3 border-bottom pb-2">
                        <i class="ti ti-credit-card me-1"></i> Transacción
                    </h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted small fw-semibold" style="width:55%">Código</td>
                                <td><span class="font-monospace small text-primary">${dash(row.transaccion_code)}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Método de Pago</td>
                                <td>${dash(row.metodo_pago)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Status Transacción</td>
                                <td>${dash(row.transaccion_status)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Total</td>
                                <td>${dash(row.total)}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Cantidad Boletos</td>
                                <td>${dash(row.cantidad_boletos)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        }

        // ──────────────────────────────────────────────────────────────
        // GENERACIÓN POR LOTES
        // ──────────────────────────────────────────────────────────────
        const btnGenerar = document.getElementById('btnGenerar');
        const statusMessage = document.getElementById('statusMessage');

        // Resetear el modal al cerrarse para que sea reutilizable
        document.getElementById('generateModal').addEventListener('hidden.bs.modal', function () {
            if (btnGenerar && !btnGenerar.dataset.completado) {
                btnGenerar.disabled = false;
                btnGenerar.innerHTML = '<i class="ti ti-ticket me-2"></i> Generar Boletos';
            }
            if (statusMessage) statusMessage.classList.add('d-none');
        });

        if (btnGenerar) {
            btnGenerar.addEventListener('click', function () {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generando...';
                setStatus('info', 'Iniciando generación de lotes...');
                generarLote();
            });
        }

        function generarLote() {
            fetch('<?= url_to('admin.tickets.generate.process') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status !== 'success') {
                        mostrarError(data.message || 'Error desconocido.');
                        return;
                    }

                    // Actualizar contadores y barra
                    const progreso = data.progreso ?? ((data.generados / data.total) * 100).toFixed(2);
                    document.getElementById('totalGenerados').textContent = data.generados.toLocaleString();
                    document.getElementById('progresoTexto').textContent = progreso + '%';

                    const barra = document.getElementById('progresoBarra');
                    barra.style.width = progreso + '%';
                    barra.setAttribute('aria-valuenow', progreso);

                    if (data.completado) {
                        setStatus('success', '<i class="ti ti-circle-check me-2"></i>' + data.message);
                        if (btnGenerar) {
                            btnGenerar.dataset.completado = '1';
                            btnGenerar.style.display = 'none';
                        }
                        table.ajax.reload();
                    } else {
                        setStatus('info', data.message);
                        setTimeout(generarLote, 500);
                    }
                })
                .catch(() => mostrarError('Error de conexión al generar boletos.'));
        }

        function setStatus(type, html) {
            statusMessage.className = `alert alert-${type}`;
            statusMessage.innerHTML = html;
            statusMessage.classList.remove('d-none');
        }

        function mostrarError(mensaje) {
            setStatus('danger', '<i class="ti ti-alert-triangle me-2"></i>' + mensaje);
            if (btnGenerar) {
                btnGenerar.disabled = false;
                btnGenerar.innerHTML = '<i class="ti ti-ticket me-2"></i> Continuar Generación';
            }
        }

    });
</script>
<?= $this->endSection() ?>