<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row ">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1">Inicio</h1>
            <p>Resumen del sistema de sorteos</p>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-lg-3 col-12">
        <div class="card p-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-2">
            <div class="d-flex gap-3">
                <div class="icon-shape icon-md bg-primary text-white rounded-2">
                    <i class="ti ti-ticket fs-4"></i>
                </div>
                <div>
                    <h2 class="mb-3 fs-6">Boletos Vendidos</h2>
                    <h3 class="fw-bold mb-0"><?= number_format($total_tickets_sold) ?></h3>
                    <p class="text-primary mb-0 small"><?= number_format($tickets_available) ?> disponibles</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-12">
        <div class="card p-4 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-2">
            <div class="d-flex gap-3">
                <div class="icon-shape icon-md bg-success text-white rounded-2">
                    <i class="ti ti-users fs-4"></i>
                </div>
                <div>
                    <h2 class="mb-3 fs-6">Participantes</h2>
                    <h3 class="fw-bold mb-0"><?= number_format($total_participants) ?></h3>
                    <p class="text-success mb-0 small">Registrados en el sistema</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-12">
        <div class="card p-4 bg-info bg-opacity-10 border border-info border-opacity-25 rounded-2">
            <div class="d-flex gap-3">
                <div class="icon-shape icon-md bg-info text-white rounded-2">
                    <i class="ti ti-checkup-list fs-4"></i>
                </div>
                <div>
                    <h2 class="mb-3 fs-6">Transacciones</h2>
                    <h3 class="fw-bold mb-0"><?= number_format($transactions_completed) ?></h3>
                    <p class="text-info mb-0 small">Completadas</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-12">
        <div class="card p-4 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-2">
            <div class="d-flex gap-3">
                <div class="icon-shape icon-md bg-warning text-white rounded-2">
                    <i class="ti ti-clock fs-4"></i>
                </div>
                <div>
                    <h2 class="mb-3 fs-6">Pendientes</h2>
                    <h3 class="fw-bold mb-0"><?= number_format($transactions_pending) ?></h3>
                    <p class="text-warning mb-0 small">Awaiting confirmation</p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-lg-4 col-12">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
                    <div>
                        <h3 class="fw-bold h4"><?= number_format($transactions_completed) ?></h3>
                        <span>Transacciones Completadas</span>
                    </div>
                    <div>
                        <i class="ti ti-circle-check fs-1 text-success"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center small">
                    <div class="text-muted">Total de sorteos realizados</div>
                    <div><a href="<?= site_url('admin/transactions') ?>" class="link-primary text-decoration-underline">Ver todas</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
                    <div>
                        <h3 class="fw-bold h4"><?= number_format($transactions_expired) ?></h3>
                        <span>Transacciones Expiradas</span>
                    </div>
                    <div>
                        <i class="ti ti-clock-off fs-1 text-danger"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center small">
                    <div class="text-muted">Reservas no confirmadas</div>
                    <div><a href="<?= site_url('admin/transactions?status=expirado') ?>" class="link-primary text-decoration-underline">Ver</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
                    <div>
                        <h3 class="fw-bold h4"><?= number_format($transactions_rejected + $transactions_cancelled) ?></h3>
                        <span>Transacciones Rechazadas</span>
                    </div>
                    <div>
                        <i class="ti ti-x fs-1 text-warning"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center small">
                    <div class="text-muted">Canceladas o rechazadas</div>
                    <div><a href="<?= site_url('admin/transactions?status=rechazada') ?>" class="link-primary text-decoration-underline">Ver</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent px-4 py-3">
                <h3 class="h5 mb-0">Resumen del Sistema</h3>
            </div>
            <div class="card-body p-4">
                <div class="row text-center border-top mt-4 pt-4">
                    <div class="col-4 border-end">
                        <h3 class="fw-bold mb-2"><?= number_format($total_tickets) ?></h3>
                        <small class="text-secondary">Total Boletos</small>
                    </div>
                    <div class="col-4 border-end">
                        <h3 class="fw-bold mb-2"><?= number_format($tickets_available) ?></h3>
                        <small class="text-secondary">Disponibles</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold mb-2"><?= number_format($total_tickets_sold) ?></h3>
                        <small class="text-secondary">Vendidos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent px-4 py-3">
                <h3 class="h5 mb-0">Estado de Transacciones</h3>
            </div>
            <div class="card-body p-4">
                <div class="row text-center border-top mt-4 pt-4">
                    <div class="col-3 border-end">
                        <h3 class="fw-bold mb-1 text-success"><?= number_format($transactions_completed) ?></h3>
                        <small class="text-secondary">Completadas</small>
                    </div>
                    <div class="col-3 border-end">
                        <h3 class="fw-bold mb-1 text-warning"><?= number_format($transactions_pending) ?></h3>
                        <small class="text-secondary">Pendientes</small>
                    </div>
                    <div class="col-3 border-end">
                        <h3 class="fw-bold mb-1 text-danger"><?= number_format($transactions_expired) ?></h3>
                        <small class="text-secondary">Expiradas</small>
                    </div>
                    <div class="col-3">
                        <h3 class="fw-bold mb-1 text-muted"><?= number_format($transactions_rejected + $transactions_cancelled) ?></h3>
                        <small class="text-secondary">Rechazadas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent px-4 py-3">
                <h3 class="h5 mb-0">Estadísticas Generales</h3>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex flex-column align-items-center p-3 bg-light rounded">
                            <i class="ti ti-ticket text-primary mb-2 fs-3"></i>
                            <h4 class="fw-bold"><?= number_format($total_tickets) ?></h4>
                            <small class="text-muted">Boletos Totales</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex flex-column align-items-center p-3 bg-light rounded">
                            <i class="ti ti-ticket-check text-success mb-2 fs-3"></i>
                            <h4 class="fw-bold"><?= number_format($total_tickets_sold) ?></h4>
                            <small class="text-muted">Boletos Vendidos</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex flex-column align-items-center p-3 bg-light rounded">
                            <i class="ti ti-users text-info mb-2 fs-3"></i>
                            <h4 class="fw-bold"><?= number_format($total_participants) ?></h4>
                            <small class="text-muted">Participantes</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex flex-column align-items-center p-3 bg-light rounded">
                            <i class="ti ti-report-analytics text-warning mb-2 fs-3"></i>
                            <h4 class="fw-bold"><?= $total_tickets > 0 ? round(($total_tickets_sold / $total_tickets) * 100, 1) : 0 ?>%</h4>
                            <small class="text-muted">Tasa de Venta</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>