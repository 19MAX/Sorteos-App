<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">

<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p>Administra las transacciones del sistema.</p>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex gap-3">
                <div>
                    <label for="filter-metodo" class="form-label small fw-medium">Método de pago</label>
                    <select id="filter-metodo" class="form-select form-select-sm" style="width: 160px;">
                        <option value="">Todos</option>
                        <?php foreach (transaction_method_list() as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($filterMetodo === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filter-status" class="form-label small fw-medium">Estado</label>
                    <select id="filter-status" class="form-select form-select-sm" style="width: 140px;">
                        <option value="">Todos</option>
                        <?php foreach (transaction_status_list() as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= ($filterStatus === $key) ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button id="btn-expire-expired" class="btn btn-warning">
                <i class="ti ti-clock-hour"></i> Procesar expiradas
            </button>
            <button id="btn-delete-old" class="btn btn-danger">
                <i class="ti ti-trash"></i> Eliminar rechazadas/expiradas
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card table-responsive py-2">
            <table id="transactionsTable" class="table text-nowrap table-hover table-sm">
                <thead class="table-light border-light">
                    <tr>
                        <th>ID Transacción</th>
                        <th>Cliente</th>
                        <th>Cédula</th>
                        <th>Email</th>
                        <th>Boletos</th>
                        <th>Total</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr class="align-middle">
                            <td><span class="fw-bold"><?= esc($tx['transaccion_id']) ?></span></td>
                            <td><?= esc(($tx['nombres'] ?? '') . ' ' . ($tx['apellidos'] ?? '')) ?></td>
                            <td><?= esc($tx['cedula'] ?? '') ?></td>
                            <td><?= esc($tx['email'] ?? '') ?></td>
                            <td><?= (int) $tx['cantidad_boletos'] ?></td>
                            <td>$<?= number_format((float) $tx['total'], 2) ?></td>
                            <td><?= transaction_method_badge($tx['metodo_pago']) ?></td>
                            <td><?= transaction_status_badge($tx['status']) ?></td>
                            <td>
                                <?php
                                $date = date_create($tx['created_at']);
                                echo date_format($date, 'd M Y H:i');
                                ?>
                            </td>
                            <td>
                                <?php if ($tx['status'] === 'completado'): ?>
                                    <a href="<?= url_to('admin.transactions.tickets', $tx['id']) ?>" class="btn btn-sm btn-outline-info" title="Ver boletos">
                                        <i class="ti ti-ticket"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (is_transaction_pending($tx['status'])): ?>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-success btn-pay" data-id="<?= $tx['id'] ?>" title="Marcar como pagada">
                                            <i class="ti ti-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-reject" data-id="<?= $tx['id'] ?>" title="Rechazar">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
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
        const filterMetodo = document.getElementById('filter-metodo');
        const filterStatus = document.getElementById('filter-status');

        function applyFilters() {
            const params = new URLSearchParams(window.location.search);
            const metodo = filterMetodo.value;
            const status = filterStatus.value;

            if (metodo) {
                params.set('metodo', metodo);
            } else {
                params.delete('metodo');
            }

            if (status) {
                params.set('status', status);
            } else {
                params.delete('status');
            }

            const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        }

        filterMetodo.addEventListener('change', applyFilters);
        filterStatus.addEventListener('change', applyFilters);

        new DataTable('#transactionsTable', {
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.7/i18n/es-ES.json' },
            scrollX: true,
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {
                            extend: 'colvis',
                            text: '<i class="ti ti-columns"></i>',
                            className: 'btn btn-secondary'
                        }
                    ]
                }
            }
        });

        $(document).on('click', '.btn-pay', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Marcar como pagada?',
                text: 'Esta acción marcará la transacción como pagada y los boletos asociados cambiarán a estado pagado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, marcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= url_to('admin.transactions.markAsPaid') ?>',
                        type: 'POST',
                        data: { id },
                        success: (res) => {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: res.message,
                                    confirmButtonColor: '#198754'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: res.message,
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: () => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error de conexión',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.btn-reject', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Rechazar transacción?',
                text: 'Esta acción rechazará la transacción y liberará los boletos asociados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= url_to('admin.transactions.reject') ?>',
                        type: 'POST',
                        data: { id },
                        success: (res) => {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: res.message,
                                    confirmButtonColor: '#198754'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: res.message,
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: () => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error de conexión',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '#btn-expire-expired', function () {
            Swal.fire({
                title: '¿Procesar transacciones expiradas?',
                text: 'Esta acción marcará las transacciones expiradas como tales y liberará sus boletos asociados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, procesar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= url_to('admin.transactions.expireExpired') ?>',
                        type: 'POST',
                        success: (res) => {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: res.message,
                                    confirmButtonColor: '#198754'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: res.message,
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: () => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error de conexión',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '#btn-delete-old', function () {
            Swal.fire({
                title: '¿Eliminar transacciones?',
                text: 'Se eliminarán todas las transacciones con estado "expirado" y "rechazada". Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= url_to('admin.transactions.deleteOld') ?>',
                        type: 'POST',
                        success: (res) => {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: res.message,
                                    confirmButtonColor: '#198754'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: res.message,
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: () => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error de conexión',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>