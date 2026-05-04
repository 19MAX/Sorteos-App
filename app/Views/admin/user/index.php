<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
    $title = $title ?? 'Participantes';
    $subtitle = $subtitle ?? 'Listado de participantes registrados en el sistema.';
    $participants = $participants ?? $users ?? [];

    $value = static function ($row, array $keys, $default = '-') {
        foreach ($keys as $key) {
            if (is_array($row) && array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }

            if (is_object($row) && isset($row->{$key}) && $row->{$key} !== null && $row->{$key} !== '') {
                return $row->{$key};
            }
        }

        return $default;
    };

    $statusClass = static function ($status) {
        $status = mb_strtolower(trim((string) $status));

        return match ($status) {
            'activo', 'active', 'confirmado', 'confirmed' => 'bg-success',
            'pendiente', 'pending', 'reservado', 'reserved' => 'bg-warning text-dark',
            'ganador', 'winner' => 'bg-primary',
            'inactivo', 'inactive', 'bloqueado', 'blocked' => 'bg-secondary',
            default => 'bg-secondary',
        };
    };

    $statusLabel = static function ($status) {
        $status = trim((string) $status);

        return $status !== '' ? ucfirst($status) : 'Sin estado';
    };
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">

<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p class="mb-0"><?= esc($subtitle) ?></p>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="mb-2">Total participantes</h6>
                <h3 class="mb-0 fw-bold"><?= number_format(is_countable($participants) ? count($participants) : 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="mb-2">Registrados hoy</h6>
                <h3 class="mb-0 fw-bold"><?= esc($registeredToday ?? '-') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="mb-2">Activos</h6>
                <h3 class="mb-0 fw-bold"><?= esc($activeParticipants ?? '-') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="mb-2">Boletos asignados</h6>
                <h3 class="mb-0 fw-bold"><?= esc($assignedTickets ?? '-') ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card table-responsive py-2">
            <table id="participantsTable" class="table mb-0 text-nowrap table-hover table-sm">
                <thead class="table-light border-light">
                    <tr>
                        <th>ID</th>
                        <th>Participante</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Boletos</th>
                        <th>Estado</th>
                        <th>Registrado el</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($participants as $participant): ?>
                            <?php
                                $nombre = $value($participant, ['nombre', 'name', 'full_name', 'nombres']);
                                $cedula = $value($participant, ['cedula', 'identificacion', 'dni', 'documento']);
                                $telefono = $value($participant, ['telefono', 'phone', 'celular', 'movil']);
                                $email = $value($participant, ['email', 'correo', 'correo_electronico']);
                                $boletos = $value($participant, ['boletos', 'tickets', 'ticket_count', 'cantidad_boletos'], 0);
                                $estado = $value($participant, ['estado', 'status'], 'activo');
                                $registrado = $value($participant, ['created_at', 'fecha_registro', 'registered_at', 'created']);
                            ?>
                            <tr class="align-middle">
                                <td><?= esc($value($participant, ['id', 'participant_id', 'user_id'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md rounded bg-light d-inline-flex align-items-center justify-content-center">
                                            <i class="ti ti-user fs-5 text-secondary"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-truncate"><?= esc($nombre) ?></div>
                                            <div class="small text-secondary text-truncate">
                                                <?= esc($value($participant, ['username', 'usuario', 'nickname'], $email !== '-' ? $email : 'Participante')) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($cedula) ?></td>
                                <td><?= esc($telefono) ?></td>
                                <td><?= esc($email) ?></td>
                                <td>
                                    <span class="fw-semibold"><?= esc(number_format((float) $boletos)) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= esc($statusClass($estado)) ?>">
                                        <?= esc($statusLabel($estado)) ?>
                                    </span>
                                </td>
                                <td><?= esc($registrado) ?></td>
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

<!-- DataTables core -->
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>

<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/3.2.6/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.bootstrap5.js"></script>

<!-- Export dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- Buttons export -->
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.colVis.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new DataTable('#participantsTable', {
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.7/i18n/es-ES.json' },
            scrollX: true,
            order: [[0, 'desc']],
            layout: {
                topStart: {
                    buttons: [
                        'pageLength',
                        {
                            extend: 'excelHtml5',
                            text: '<i class="ti ti-file-spreadsheet"></i> Excel',
                            className: 'btn btn-success text-white'
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="ti ti-file-type-pdf"></i> PDF',
                            className: 'btn btn-danger text-white'
                        },
                        {
                            extend: 'print',
                            text: '<i class="ti ti-printer"></i> Imprimir',
                            className: 'btn btn-secondary text-white'
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="ti ti-columns"></i> Columnas',
                            className: 'btn btn-primary text-white'
                        }
                    ]
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>
