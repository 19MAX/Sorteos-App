<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">

<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p>Administra las transacciones realizadas mediante Payphone.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card table-responsive py-2">
            <table id="payphoneTable" class="table text-nowrap table-hover table-sm">
                <thead class="table-light border-light">
                    <tr>
                        <th>Transacción Interna</th>
                        <th>Código Autorización</th>
                        <th>Email</th>
                        <th>Monto</th>
                        <th>Teléfono</th>
                        <th>Tarjeta</th>
                        <th>Fecha Transacción</th>
                        <th>Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payphoneTransactions as $pt): ?>
                        <tr class="align-middle">
                            <td><?= esc($pt['internal_transaction_id'] ?? '-') ?></td>
                            <td><?= esc($pt['authorization_code'] ?? '-') ?></td>
                            <td><?= esc($pt['email'] ?? '-') ?></td>
                            <td><span class="text-success fw-bold"><?= esc($pt['amount'] / 100 ?? '-') ?></span></td>
                            <td>+<?= esc($pt['phone_number'] ?? '-') ?></td>

                            <td>
                                <?php if (!empty($pt['card_brand']) || !empty($pt['card_type'])): ?>
                                    <?= esc(($pt['card_brand'] ?? '') . ' ' . ($pt['card_type'] ?? '')) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($pt['transaction_date'])) {
                                    $date = date_create($pt['transaction_date']);
                                    echo date_format($date, 'd M Y H:i');
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($pt['created_at'])) {
                                    $date = date_create($pt['created_at']);
                                    echo date_format($date, 'd M Y H:i');
                                } else {
                                    echo '-';
                                }
                                ?>
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
        new DataTable('#payphoneTable', {
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
    });
</script>
<?= $this->endSection() ?>