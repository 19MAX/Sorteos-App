<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css
">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.css
">
<!-- <?php
// Recuperar datos flash
$lastAction = session()->getFlashdata('last_action');
$lastData = session()->getFlashdata('last_data') ?? [];
$flashValidation = session()->getFlashdata('flashValidation') ?? []; // array de errores por campo
$bankActions = ['create', 'edit']; // Agrega aquí futuras acciones de bancos
$isBankTab = in_array($lastAction, $bankActions);
?> -->
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row ">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1">Configuración</h1>
            <p>Maneja la configuración del sistema</p>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">

    <div class="col-12">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link <?= !$isBankTab ? 'active' : '' ?>" id="nav-home-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-home" type="button" role="tab">
                    Producto
                </button>
                <button class="nav-link <?= $isBankTab ? 'active' : '' ?>" id="nav-profile-tab" data-bs-toggle="tab"
                    data-bs-target="#nav-profile" type="button" role="tab">
                    Bancos
                </button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade <?= !$isBankTab ? 'show active' : '' ?>" id="nav-home" role="tabpanel"
                tabindex="0">

                <div class="card">
                    <div class="card-body px-4">

                        <form class="row" action="<?= base_url('admin/tickets/settings') ?>" method="POST"
                            id="settingsForm" enctype="multipart/form-data">
                            <div class="col-lg-8">
                                <!-- Configuración del Producto -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nombre_producto" class="form-label">Nombre del
                                                Producto</label>
                                            <input type="text" class="form-control" id="nombre_producto"
                                                name="nombre_producto"
                                                value="<?= esc($settings['nombre_producto'] ?? '') ?>"
                                                placeholder="Ej: Honda Civic 2024" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="total_boletos" class="form-label">Total de
                                                Boletos</label>
                                            <input type="number" class="form-control" id="total_boletos"
                                                name="total_boletos" value="<?= $settings['total_boletos'] ?? '' ?>"
                                                min="1" max="1000000" placeholder="Ej: 10000" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuración de Boletos -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="precio_boleto" class="form-label">Precio por
                                                Boleto
                                                ($)</label>
                                            <input type="number" class="form-control" id="precio_boleto"
                                                name="precio_boleto" value="<?= $settings['precio_boleto'] ?? '' ?>"
                                                min="0.01" step="0.01" placeholder="Ej: 5.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="boletos_minimos" class="form-label">Numero mínimo de
                                                boletos</label>
                                            <input type="number" class="form-control" id="boletos_minimos"
                                                name="boletos_minimos" value="<?= $settings['boletos_minimos'] ?? '' ?>"
                                                min="1" step="1" placeholder="Ej: 10" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="descripcion_producto" class="form-label">Descripción
                                                del Producto</label>
                                            <textarea class="form-control" id="descripcion_producto"
                                                name="descripcion_producto" rows="3"
                                                placeholder="Descripción detallada del producto..."><?= esc($settings['descripcion_producto'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campo oculto para incluir la imagen en el formulario -->
                                <input type="hidden" id="imagen_producto" name="imagen_producto" value="">

                                <!-- Botones de Acción -->
                                <div class="row">
                                    <div class="col-12">

                                        <button form="settingsForm" type="submit" class="col-12 btn btn-success">
                                            <i class="mdi mdi-content-save me-1"></i>
                                            Guardar Configuración
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-4">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label>Imagen del Producto</label>
                                                <input type="file" name="imagen_producto" id="img" class="form-control"
                                                    data-default-file="<?= isset($settings['imagen_producto']) ? base_url('assets/upload/' . $settings['imagen_producto']) : '' ?>"
                                                    accept="image/*">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body pb-0">
                                        <div class="col-md-12">
                                            <div class="form-check form-switch form-switch-lg mb-3">
                                                <label class="form-check-label" for="sorteo_activo">
                                                    <strong>Sorteo Activo</strong>
                                                </label>

                                                <input class="form-check-input" type="checkbox" id="sorteo_activo"
                                                    name="sorteo_activo" <?= ($settings['sorteo_activo'] ?? false) ? 'checked' : "" ?>>

                                            </div>

                                            <!-- Alert que se actualiza dinámicamente -->
                                            <div id="status_alert"
                                                class="alert <?= ($settings["sorteo_activo"] ?? false) ? "alert-success" : "alert-danger" ?>">
                                                <h5 class="alert-heading text-center">
                                                    <span id="status_badge"
                                                        class="badge <?= ($settings['sorteo_activo'] ?? false) ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= ($settings['sorteo_activo'] ?? false) ? 'ACTIVO' : 'INACTIVO' ?>
                                                    </span>
                                                </h5>
                                            </div>

                                            <!-- Indicador de carga para el checkbox -->
                                            <div id="loading_indicator" class="text-center" style="display: none;">
                                                <div class="spinner-border spinner-border-sm text-primary"
                                                    role="status">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <small class="text-muted d-block">Actualizando estado...</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <!-- Tab Bancos — ✅ activo si hubo error -->
            <div class="tab-pane fade <?= $isBankTab ? 'show active' : '' ?>" id="nav-profile" role="tabpanel"
                tabindex="0">

                <div class="row">
                    <div class="col-12">
                        <div class="card table-responsive py-2">
                            <table id="example" class="table text-nowrap table-hover table-sm">
                                <thead class="table-light border-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo de cuenta</th>
                                        <th>Cuenta</th>
                                        <th>Titular</th>
                                        <th>Activo</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_banks as $bank): ?>
                                        <tr class="align-middle">
                                            <td>
                                                <img src="<?= base_url('uploads/bancos/' . ($bank['logo'] ?? 'assets/images/product-1.png')) ?>"
                                                    alt="" class="avatar avatar-md rounded" />
                                                <span class="ms-3"><?= esc($bank['nombre_banco']) ?></span>
                                            </td>
                                            <td><?= esc($bank['tipo_cuenta']) ?></td>
                                            <td><?= esc($bank['numero_cuenta']) ?></td>
                                            <td><?= esc($bank['titular']) ?></td>
                                            <td>
                                                <!-- ✅ Badge visual para activo/inactivo -->
                                                <span class="badge <?= $bank['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $bank['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <!-- Editar — todos los datos en data-* -->
                                                <a href="#" class="btn-edit" data-id="<?= $bank['id'] ?>"
                                                    data-nombre_banco="<?= esc($bank['nombre_banco']) ?>"
                                                    data-tipo_cuenta="<?= esc($bank['tipo_cuenta']) ?>"
                                                    data-numero_cuenta="<?= esc($bank['numero_cuenta']) ?>"
                                                    data-titular="<?= esc($bank['titular']) ?>"
                                                    data-activo="<?= $bank['activo'] ?>"
                                                    data-logo="<?= esc($bank['logo'] ?? '') ?>">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <!-- Eliminar -->
                                                <a href="#" class="link-danger btn-delete ms-2" data-id="<?= $bank['id'] ?>"
                                                    data-nombre="<?= esc($bank['nombre_banco']) ?>">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>


                    </div>

                </div>
            </div>

        </div>
    </div>


</div>

<!-- ===== MODAL CREAR BANCO ===== -->
<div id="createModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createForm" action="<?= route_to('settings.bank.create') ?>" method="post"
                enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Banco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <!-- Nombre banco -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Banco</label>
                            <input type="text" class="form-control" name="nombre_banco"
                                value="<?= esc(old('nombre_banco', $lastData['nombre_banco'] ?? '')) ?>">
                        </div>


                        <!-- Tipo cuenta -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Cuenta</label>
                            <select class="form-control" name="tipo_cuenta">
                                <option value="">Seleccione</option>
                                <?php foreach (['ahorros' => 'Ahorros', 'corriente' => 'Corriente', 'otro' => 'Otro'] as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= (old('tipo_cuenta', $lastData['tipo_cuenta'] ?? '') === $val) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <!-- Número cuenta -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Cuenta</label>
                            <input type="text" class="form-control" name="numero_cuenta"
                                value="<?= esc(old('numero_cuenta', $lastData['numero_cuenta'] ?? '')) ?>">
                        </div>


                        <!-- Titular -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Titular de la Cuenta</label>
                            <input type="text" class="form-control" name="titular"
                                value="<?= esc(old('titular', $lastData['titular'] ?? '')) ?>">
                        </div>

                        <!-- Logo -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Logo del Banco</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                        </div>

                        <!-- Estado -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-control" name="activo">
                                <option value="1" <?= (old('activo', $lastData['activo'] ?? '1') == '1') ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= (old('activo', $lastData['activo'] ?? '1') == '0') ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL EDITAR BANCO ===== -->
<div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <!-- La action se setea dinámicamente con JS al abrir el modal -->

                <div class="modal-header">
                    <h5 class="modal-title">Editar Banco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Banco</label>
                            <input type="text" class="form-control" name="nombre_banco"
                                value="<?= esc(old('nombre_banco', $lastData['nombre_banco'] ?? '')) ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Cuenta</label>
                            <select class="form-control" name="tipo_cuenta">
                                <option value="">Seleccione</option>
                                <?php foreach (['ahorros' => 'Ahorros', 'corriente' => 'Corriente', 'otro' => 'Otro'] as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= (old('tipo_cuenta', $lastData['tipo_cuenta'] ?? '') === $val) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Cuenta</label>
                            <input type="text" class="form-control" name="numero_cuenta"
                                value="<?= esc(old('numero_cuenta', $lastData['numero_cuenta'] ?? '')) ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Titular de la Cuenta</label>
                            <input type="text" class="form-control" name="titular"
                                value="<?= esc(old('titular', $lastData['titular'] ?? '')) ?>">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Logo del Banco</label>
                            <!-- Preview del logo actual -->
                            <div id="editLogoPreview" class="mb-2"></div>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            <small class="text-muted">Dejar vacío para mantener el logo actual.</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-control" name="activo">
                                <option value="1" <?= (old('activo', $lastData['activo'] ?? '') == '1') ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= (old('activo', $lastData['activo'] ?? '') == '0') ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL ELIMINAR BANCO ===== -->
<div id="deleteModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="deleteForm" method="post">
                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title text-danger">Eliminar Banco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <i class="ti ti-trash fs-1 text-danger"></i>
                    <p class="mt-2">¿Estás seguro de eliminar el banco <strong id="deleteBankName"></strong>?</p>
                    <small class="text-muted">Esta acción no se puede deshacer.</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Bootstrap -->
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

    // ✅ Mapa acción → modal
    const actionModalMap = {
        'create': 'createModal',
        'edit': 'editModal',
        'delete': 'deleteModal',
    };

    new DataTable('#example', {
        language: { url: 'https://cdn.datatables.net/plug-ins/2.3.7/i18n/es-ES.json' },

        scrollX: true,
        layout: {
            topStart: {
                buttons: [
                    'pageLength',
                    // Agregar un boton personalizado para abrir un modal de creación de registros con icono de plus
                    {
                        text: '<i class="ti ti-plus"></i>',
                        action: function (e, dt, node, config) {
                            // Aquí puedes abrir un modal o redirigir a una página de creación
                            const modal = new bootstrap.Modal(document.getElementById('createModal'));
                            function openCreateModal() {
                                modal.show();
                            }
                            return openCreateModal();
                        },
                        className: 'btn btn-success text-white',
                    }
                ]
            }
        }
    });

    // Abrir modal EDITAR — poblar campos con data-*
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-edit');
        if (!btn) return;
        e.preventDefault();

        const d = btn.dataset;
        const form = document.getElementById('editForm');
        const base = '<?= base_url("admin/settings/bank/update/") ?>';

        // Setear action con el ID
        form.action = base + d.id;

        // Poblar campos
        form.querySelector('[name="nombre_banco"]').value = d.nombre_banco;
        form.querySelector('[name="tipo_cuenta"]').value = d.tipo_cuenta;
        form.querySelector('[name="numero_cuenta"]').value = d.numero_cuenta;
        form.querySelector('[name="titular"]').value = d.titular;
        form.querySelector('[name="activo"]').value = d.activo;

        // Preview logo actual
        const preview = document.getElementById('editLogoPreview');
        preview.innerHTML = d.logo
            ? `<img src="<?= base_url('uploads/bancos/') ?>${d.logo}"
                class="avatar avatar-md rounded" alt="Logo actual">`
            : '';

        new bootstrap.Modal(document.getElementById('editModal')).show();
    });

    // Abrir modal ELIMINAR
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;
        e.preventDefault();

        const form = document.getElementById('deleteForm');
        const base = '<?= base_url("admin/settings/bank/delete/") ?>';

        form.action = base + btn.dataset.id;
        document.getElementById('deleteBankName').textContent = btn.dataset.nombre;

        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
</script>
<?= $this->endSection() ?>