<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?= $this->section('style') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">
<style>
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1050;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active {
    display: flex;
}
.modal-overlay .modal-dialog {
    background: #fff;
    border-radius: 8px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.modal-overlay .modal-dialog.modal-lg {
    max-width: 800px;
}
.modal-overlay .modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #dee2e6;
}
.modal-overlay .modal-body {
    padding: 1.25rem;
}
.modal-overlay .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid #dee2e6;
}
</style>
<?= $this->endSection() ?>

<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p>Genera y administra los boletos disponibles para el sorteo.</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="filterStatus" class="form-label small fw-semibold">Estado</label>
                        <select id="filterStatus" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="reservado">Reservado</option>
                            <option value="procesando">Procesando</option>
                            <option value="vendido">Vendido</option>
                            <option value="pagado">Pagado</option>
                            <option value="asignado">Asignado</option>
                            <option value="expirado">Expirado</option>
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
                <div class="row mt-2">
                    <div class="col-md-3">
                        <label for="filterTransaccion" class="form-label small fw-semibold">Transacción</label>
                        <input type="text" id="filterTransaccion" class="form-control form-control-sm"
                            placeholder="ID transacción...">
                    </div>
                    <div class="col-md-9 d-flex align-items-end">
                        <button id="btnApplyFilters" class="btn btn-primary btn-sm me-2">
                            <i class="ti ti-search me-1"></i> Aplicar Filtros
                        </button>
                        <button id="btnClearFilters" class="btn btn-outline-secondary btn-sm me-2">
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

<div class="row">
    <div class="col-12">
        <div class="card table-responsive py-2">
            <table id="ticketsTable" class="table text-nowrap table-hover table-sm">
                <thead class="table-light border-light">
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

<!-- ===== MODAL GENERAR BOLETOS ===== -->
<div id="generateModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title">Generar Boletos</h5>
            <button type="button" class="btn-close" onclick="closeModal('generateModal')"></button>
        </div>
        <div class="modal-body p-4">
            <h4 class="mb-4">Estado de los Boletos</h4>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Boletos configurados:</span>
                <span class="fw-bold fs-5" id="totalConfigurados"><?= number_format($totalConfigurados) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-4">
                <span class="text-muted">Boletos generados:</span>
                <span class="fw-bold fs-5 text-primary" id="totalGenerados"><?= number_format($totalGenerados) ?></span>
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
                        role="progressbar"
                        style="width: <?= $progreso ?>%;"
                        aria-valuenow="<?= $progreso ?>"
                        aria-valuemin="0"
                        aria-valuemax="100"></div>
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
            <button type="button" class="btn btn-light" onclick="closeModal('generateModal')">Cerrar</button>
            <?php if ($totalConfigurados > 0 && $totalGenerados < $totalConfigurados): ?>
                <button id="btnGenerar" class="btn btn-primary py-2">
                    <i class="ti ti-ticket me-2"></i> Generar Boletos
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== MODAL VER DETALLES ===== -->
<div id="detailsModal" class="modal-overlay">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h5 class="modal-title">Detalles del Boleto</h5>
            <button type="button" class="btn-close" onclick="closeModal('detailsModal')"></button>
        </div>
        <div class="modal-body" id="detailsModalContent"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" onclick="closeModal('detailsModal')">Cerrar</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.bootstrap5.js"></script>
<script type="module" src="<?= base_url('assets/js/ticketStatus.js') ?>"></script>
<script>
    // ── Funciones de modal nativo (sin bootstrap.Modal) ──────────────
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Cerrar al hacer click en el fondo oscuro
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Cerrar con tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function (el) {
                el.classList.remove('active');
            });
            document.body.style.overflow = '';
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const table = new DataTable('#ticketsTable', {
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.7/i18n/es-ES.json' },
            serverSide: true,
            processing: true,
            scrollX: true,
            ajax: {
                url: '<?= url_to('admin.tickets.data') ?>',
                type: 'GET',
                data: function (d) {
                    d.status         = $('#filterStatus').val();
                    d.reserved_from  = $('#filterReservedFrom').val();
                    d.reserved_to    = $('#filterReservedTo').val();
                    d.confirmed_from = $('#filterConfirmedFrom').val();
                    d.confirmed_to   = $('#filterConfirmedTo').val();
                    d.participant    = $('#filterParticipant').val();
                    d.transaccion    = $('#filterTransaccion').val();
                },
            },
            columns: [
                {
                    data: 'numero',
                    render: (d) => `<span class="fw-bold">${d}</span>`
                },
                {
                    data: 'status',
                    render: (d) => TicketStatus.renderBadge(d)
                },
                {
                    data: 'nombres',
                    render: (d, type, row) => {
                        if (!d) return '<span class="text-muted">-</span>';
                        return `<span class="text-truncate d-inline-block" style="max-width:120px;">${d} ${row.apellidos || ''}</span>`;
                    }
                },
                {
                    data: 'telefono',
                    render: (d) => d || '<span class="text-muted">-</span>'
                },
                {
                    data: 'transaccion_code',
                    render: (d) => d ? `<span class="text-primary small">${d}</span>` : '<span class="text-muted">-</span>'
                },
                {
                    data: 'metodo_pago',
                    render: (d) => {
                        if (!d) return '<span class="text-muted">-</span>';
                        const badges = {
                            'tarjeta'      : '<span class="badge bg-primary">Tarjeta</span>',
                            'transferencia': '<span class="badge bg-warning text-dark">Transferencia</span>',
                            'fisico'       : '<span class="badge bg-secondary">Físico</span>',
                        };
                        return badges[d] || `<span class="badge bg-light text-dark">${d}</span>`;
                    }
                },
                {
                    data: 'reserved_at',
                    render: (d) => d ? `<span class="small text-muted">${d.split(' ')[0]}</span>` : '<span class="text-muted">-</span>'
                },
                {
                    data: 'confirmed_at',
                    render: (d) => d ? `<span class="small text-muted">${d.split(' ')[0]}</span>` : '<span class="text-muted">-</span>'
                },
                {
                    data: 'created_at',
                    render: (d) => `<span class="small text-muted">${d.split(' ')[0]}</span>`
                },
                {
                    data: 'updated_at',
                    render: (d) => `<span class="small text-muted">${d.split(' ')[0]}</span>`
                },
                {
                    data: 'id',
                    render: (d) => `
                        <button class="btn btn-sm btn-outline-primary" onclick="viewTicketDetails(${d})" title="Ver detalles">
                            <i class="ti ti-eye"></i>
                        </button>
                    `,
                    orderable: false,
                    searchable: false,
                },
            ],
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {
                            text: '<i class="ti ti-ticket"></i> Generar Boletos',
                            className: 'btn btn-primary text-white',
                            action: () => openModal('generateModal'),
                        },
                    ],
                },
            },
        });

        // ── Filtros ──────────────────────────────────────────────────
        $('#btnApplyFilters').on('click', () => table.ajax.reload());

        $('#btnClearFilters').on('click', function () {
            $('#filterStatus').val('');
            $('#filterReservedFrom').val('');
            $('#filterReservedTo').val('');
            $('#filterConfirmedFrom').val('');
            $('#filterConfirmedTo').val('');
            $('#filterParticipant').val('');
            $('#filterTransaccion').val('');
            table.ajax.reload();
        });

        // ── Exportar Excel ───────────────────────────────────────────
        $('#btnExportExcel').on('click', function () {
            const params = new URLSearchParams({
                status        : $('#filterStatus').val(),
                reserved_from : $('#filterReservedFrom').val(),
                reserved_to   : $('#filterReservedTo').val(),
                confirmed_from: $('#filterConfirmedFrom').val(),
                confirmed_to  : $('#filterConfirmedTo').val(),
                participant   : $('#filterParticipant').val(),
                transaccion   : $('#filterTransaccion').val(),
            });
            window.location.href = '<?= url_to('admin.tickets.export') ?>?' + params.toString();
        });

        // ── Ver detalles del boleto ──────────────────────────────────
        window.viewTicketDetails = function (id) {
            fetch(`<?= url_to('admin.tickets.show', 0) ?>`.replace('/0', '/' + id), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(resp => {
                if (!resp.data || !resp.data.length) {
                    alert('Boleto no encontrado.');
                    return;
                }
                const row = resp.data[0];
                document.getElementById('detailsModalContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Información del Boleto</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted fw-semibold">ID</td><td>${row.id}</td></tr>
                                <tr><td class="text-muted fw-semibold">Número</td><td class="fw-bold">${row.numero}</td></tr>
                                <tr><td class="text-muted fw-semibold">Estado</td><td>${TicketStatus.renderBadge(row.status)}</td></tr>
                                <tr><td class="text-muted fw-semibold">Creado</td><td>${row.created_at}</td></tr>
                                <tr><td class="text-muted fw-semibold">Actualizado</td><td>${row.updated_at || '-'}</td></tr>
                                <tr><td class="text-muted fw-semibold">Reserved At</td><td>${row.reserved_at || '-'}</td></tr>
                                <tr><td class="text-muted fw-semibold">Confirmed At</td><td>${row.confirmed_at || '-'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Información del Participante</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted fw-semibold">Nombre</td><td>${row.nombres || '-'} ${row.apellidos || ''}</td></tr>
                                <tr><td class="text-muted fw-semibold">Teléfono</td><td>${row.telefono || '-'}</td></tr>
                                <tr><td class="text-muted fw-semibold">Email</td><td>${row.email || '-'}</td></tr>
                                <tr><td class="text-muted fw-semibold">Cédula</td><td>${row.cedula || '-'}</td></tr>
                            </table>
                            <h6 class="fw-bold mb-3 mt-4">Información de Transacción</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted fw-semibold">Transacción</td><td>${row.transaccion_code || '-'}</td></tr>
                                <tr><td class="text-muted fw-semibold">Método Pago</td><td>${row.metodo_pago || '-'}</td></tr>
                                <tr><td class="text-muted fw-semibold">Status Transacción</td><td>${row.transaccion_status || '-'}</td></tr>
                                <tr><td class="text-muted fw-semibold">Total Transacción</td><td>${row.total || '-'}</td></tr>
                                <tr><td class="text-muted fw-semibold">Cantidad Boletos</td><td>${row.cantidad_boletos || '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                openModal('detailsModal');
            });
        };

        // ── Generación por lotes ─────────────────────────────────────
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
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('totalGenerados').textContent = data.generados.toLocaleString();
                    const progreso = data.progreso ?? ((data.generados / data.total) * 100).toFixed(2);
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