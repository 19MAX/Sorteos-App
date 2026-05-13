<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css">

<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p>Administra los participantes registrados en el sistema.</p>
        </div>
        <div class="d-flex justify-content-end mb-4">
            <a href="<?= url_to('admin.participants.create') ?>" class="btn btn-primary">
                <i class="ti ti-plus"></i> Nuevo Participante
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card table-responsive py-2">
            <table id="participantsTable" class="table text-nowrap table-hover table-sm">
                <thead class="table-light border-light">
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Transacciones</th>
                        <th>Boletos</th>
                        <th>Verificado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
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
    const table = new DataTable('#participantsTable', {
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= url_to('admin.participants.data') ?>',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', thrown);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar los datos'
                });
            }
        },
        columns: [
            { data: 'id', name: 'p.id' },
            { data: 'codigo', name: 'p.codigo' },
            { data: 'full_name', name: 'p.full_name', render: function(data, type, row) {
                return `<div class="fw-semibold">${data || ''}</div>`;
            }},
            { data: 'cedula', name: 'p.cedula' },
            { data: 'telefono', name: 'p.telefono' },
            { data: 'email', name: 'p.email' },
            { data: 'transaction_count', name: 'transaction_count', searchable: false, orderable: false },
            { data: 'ticket_count', name: 'ticket_count', searchable: false, orderable: false },
            { data: 'verificado', name: 'p.verificado', render: function(data) {
                return data == 1
                    ? '<span class="badge bg-success">Verificado</span>'
                    : '<span class="badge bg-secondary">No verificado</span>';
            }},
            { data: 'created_at', name: 'p.created_at', render: function(data) {
                if (!data) return '-';
                const date = new Date(data);
                return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
            }},
            { data: 'id', searchable: false, orderable: false, render: function(data, type, row) {
                return `
                    <div class="d-flex gap-1">
                        <a href="<?= site_url('admin/participants/edit/') ?>${row.id}" class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="ti ti-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${row.id}" title="Eliminar">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                `;
            }}
        ],
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
                        text: '<i class="ti ti-columns"></i>',
                        className: 'btn btn-secondary'
                    }
                ]
            }
        }
    });

    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Eliminar participante?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `<?= site_url('admin/participants/delete/') ?>${id}`,
                    type: 'POST',
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: res.message
                            }).then(() => {
                                table.draw();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexión'
                        });
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>