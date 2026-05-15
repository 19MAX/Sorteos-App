<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p>Venta de boletos físicos en punto de venta.</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="alert alert-info mb-0">
            <strong>Precio:</strong> $<?= number_format($precio_boleto, 2) ?> c/u
        </div>
    </div>
    <div class="col-md-4">
        <div class="alert alert-warning mb-0">
            <strong>Mínimo:</strong> <?= (int) $boletos_minimos ?> | <strong>Máximo:</strong>
            <?= (int) $boletos_maximos ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="alert alert-secondary mb-0">
            <strong>Disponibles:</strong> <?= (int) $boletos_disponibles ?>
        </div>
    </div>
</div>

<div class="row" id="step-1">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-user-search me-2"></i>Buscar Participante</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label for="input-cedula" class="form-label fw-medium">Cédula de Identidad</label>
                        <input type="text" id="input-cedula" class="form-control" placeholder="" maxlength="10">
                        <!-- <div class="form-text">Ingrese la cédula sin puntos ni comas.</div> -->
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="btn-buscar-cedula">
                            <i class="ti ti-search me-1"></i>Buscar
                        </button>
                    </div>
                    <div class="col-md-4">
                        <div id="participant-status" class="alert d-none mt-2">
                            <strong id="status-text"></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row d-none" id="step-2">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ti ti-user-edit me-2"></i>Datos del Participante</h5>
                <span class="badge bg-success" id="badge-exists" style="display:none">
                    <i class="ti ti-check me-1"></i>Encontrado
                </span>
            </div>
            <div class="card-body">
                <form id="form-participante">
                    <input type="hidden" id="participant-id" value="">
                    <input type="hidden" id="from-api" value="0">
                    <input type="hidden" id="locked" value="0">

                    <div class="">
                        <div class="row mb-3">

                            <div class="col-md-4">
                                <label for="cedula" class="form-label fw-medium">Cédula *</label>
                                <input type="text" id="cedula" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="nombres" class="form-label fw-medium">Nombres *</label>
                                <input type="text" id="nombres" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="apellidos" class="form-label fw-medium">Apellidos *</label>
                                <input type="text" id="apellidos" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">

                            <div class="col-md-4">
                                <label for="email" class="form-label fw-medium">Email</label>
                                <input type="email" id="email" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="telefono" class="form-label fw-medium">Teléfono / WhatsApp</label>
                                <input type="text" id="telefono" class="form-control" >
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success" id="btn-guardar-participante">
                            <i class="ti ti-device-floppy me-2"></i>Guardar y Continuar
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-back-step1">
                            <i class="ti ti-arrow-left me-2"></i>Volver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row d-none" id="step-3">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-ticket me-2"></i>Seleccionar Boletos</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="cantidad-boletos" class="form-label fw-medium">Cantidad de Boletos</label>
                        <input type="number" id="cantidad-boletos" class="form-control"
                            min="<?= (int) $boletos_minimos ?>"
                            max="<?= min((int) $boletos_maximos, (int) $boletos_disponibles) ?>"
                            value="<?= (int) $boletos_minimos ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="w-100">
                            <div class="alert alert-primary mb-0 py-2">
                                <strong>Total a pagar:</strong>
                                <span id="total-a-pagar"
                                    class="fs-5 fw-bold ms-2">$<?= number_format($precio_boleto * $boletos_minimos, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="w-100">
                            <div class="alert alert-warning mb-0 py-2">
                                <strong>Cantidad disponible:</strong>
                                <span id="disponibles-info"><?= (int) $boletos_disponibles ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <label for="monto-recibido" class="form-label fw-medium">Monto Recibido ($)</label>
                        <input type="number" id="monto-recibido" class="form-control" step="0.01" min="0"
                            placeholder="0.00">
                        <div class="form-text">Deje en 0 si es pago exacto.</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="w-100">
                            <div class="alert alert-success mb-0 py-2 d-none" id="vuelto-alert">
                                <strong>Vuelto:</strong>
                                <span id="vuelto-amount" class="fs-5 fw-bold ms-2">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label for="observaciones" class="form-label fw-medium">Observaciones (opcional)</label>
                    <textarea id="observaciones" class="form-control" rows="2"
                        placeholder="Notas sobre la venta..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card" id="resumen-card" style="position: sticky; top: 1rem;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="ti ti-receipt me-2"></i>Resumen de Venta</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Participante:</td>
                        <td class="fw-bold" id="resumen-nombre">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cédula:</td>
                        <td id="resumen-cedula">-</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Boletos:</td>
                        <td class="fw-bold" id="resumen-cantidad">0</td>
                    </tr>
                    <tr class="table-primary">
                        <td class="fw-bold">Total:</td>
                        <td class="fw-bold fs-5" id="resumen-total">$0.00</td>
                    </tr>
                </table>

                <div class="d-grid gap-2 mt-4">
                    <button type="button" class="btn btn-success btn-lg" id="btn-vender">
                        <i class="ti ti-cash me-2"></i>Confirmar Venta
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btn-cancelar-venta">
                        <i class="ti ti-x me-2"></i>Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row d-none" id="step-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="ti ti-circle-check me-2"></i>Venta Completada</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-success fs-5 mb-3">
                            <i class="ti ti-check-circle me-2"></i>Venta realizada exitosamente.
                        </p>

                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted">ID Transacción:</td>
                                <td class="fw-bold" id="success-transaccion">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Participante:</td>
                                <td id="success-nombre">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cantidad:</td>
                                <td class="fw-bold" id="success-cantidad">0</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total cobrado:</td>
                                <td class="fw-bold fs-5" id="success-total">$0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Vuelto:</td>
                                <td id="success-vuelto">$0.00</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-secondary"><strong>Boletos vendidos:</strong></div>
                        <div class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                            <div class="row g-1" id="success-tickets"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" class="btn btn-primary" id="btn-nueva-venta">
                        <i class="ti ti-plus me-2"></i>Nueva Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    (function () {
        const PRECIO_BOLETO = <?= (float) $precio_boleto ?>;
        const BOLETOS_MINIMOS = <?= (int) $boletos_minimos ?>;
        const BOLETOS_MAXIMOS = <?= (int) $boletos_maximos ?>;
        const BOLETOS_DISPONIBLES = <?= (int) $boletos_disponibles ?>;
        const CSRF_NAME = '<?= $csrfName ?>';
        let CSRF_HASH = '<?= $csrfHash ?>';

        function csrfBody() {
            const obj = {};
            obj[CSRF_NAME] = CSRF_HASH;
            return obj;
        }

        function updateCsrf(hash) {
            CSRF_HASH = hash;
        }

        let currentParticipant = null;

        function showStep(step) {
            ['step-1', 'step-2', 'step-3', 'step-4'].forEach(function (id) {
                document.getElementById(id).classList.add('d-none');
            });
            document.getElementById('step-' + step).classList.remove('d-none');
        }

        function updateTotal() {
            const qty = parseInt(document.getElementById('cantidad-boletos').value) || 0;
            const total = qty * PRECIO_BOLETO;
            document.getElementById('total-a-pagar').textContent = '$' + total.toFixed(2);
            document.getElementById('resumen-cantidad').textContent = qty;
            document.getElementById('resumen-total').textContent = '$' + total.toFixed(2);

            const received = parseFloat(document.getElementById('monto-recibido').value) || 0;
            const alertVuelto = document.getElementById('vuelto-alert');
            if (received > 0 && received > total) {
                alertVuelto.classList.remove('d-none');
                document.getElementById('vuelto-amount').textContent = '$' + (received - total).toFixed(2);
            } else {
                alertVuelto.classList.add('d-none');
            }
        }

        function updateResumen() {
            if (currentParticipant) {
                document.getElementById('resumen-nombre').textContent =
                    (currentParticipant.nombres || '') + ' ' + (currentParticipant.apellidos || '');
                document.getElementById('resumen-cedula').textContent = currentParticipant.cedula || '';
            }
        }

        document.getElementById('btn-buscar-cedula').addEventListener('click', function () {
            const cedula = document.getElementById('input-cedula').value.trim();
            if (!cedula) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese una cédula' });
                return;
            }

            const body = csrfBody();
            body.cedula = cedula;

            console.log('Sending request to:', '<?= url_to('admin.physicalSales.buscarCedula') ?>');
            console.log('Body:', JSON.stringify(body));

            fetch('<?= url_to('admin.physicalSales.buscarCedula') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(body)
            })
                .then(function (res) {
                    console.log('Response status:', res.status);
                    return res.json();
                })
                .then(function (data) {
                    console.log('Response data:', JSON.stringify(data));
                    console.log('data.status:', data.status);
                    console.log('data.found:', data.found);
                    console.log('data.participant:', JSON.stringify(data.participant));

                    if (data.csrfHash) updateCsrf(data.csrfHash);

                    if (data.status !== 'success') {
                        console.log('No es success, mostrando error:', data.message);
                        alert('Error: ' + (data.message || 'Error desconocido'));
                        return;
                    }

                    currentParticipant = data.participant;
                    document.getElementById('participant-id').value = data.participant.id || '';
                    document.getElementById('from-api').value = data.from_api ? '1' : '0';
                    document.getElementById('locked').value = data.found ? '1' : '0';

                    document.getElementById('cedula').value = data.participant.cedula || '';
                    document.getElementById('nombres').value = data.participant.nombres || '';
                    document.getElementById('apellidos').value = data.participant.apellidos || '';
                    document.getElementById('email').value = data.participant.email || '';
                    document.getElementById('telefono').value = data.participant.telefono || '';

                    const statusEl = document.getElementById('participant-status');
                    const badge = document.getElementById('badge-exists');

                    if (data.found) {
                        badge.style.display = '';
                        statusEl.classList.remove('d-none', 'alert-info', 'alert-warning');
                        statusEl.classList.add('alert-success');
                        document.getElementById('status-text').textContent = 'Participante encontrado en el sistema';
                        document.getElementById('nombres').readOnly = true;
                        document.getElementById('apellidos').readOnly = true;
                    } else if (data.from_api) {
                        badge.style.display = 'none';
                        statusEl.classList.remove('d-none', 'alert-success', 'alert-warning');
                        statusEl.classList.add('alert-info');
                        document.getElementById('status-text').textContent = 'Datos cargados desde registro oficial';
                        document.getElementById('nombres').readOnly = false;
                        document.getElementById('apellidos').readOnly = false;
                    } else {
                        badge.style.display = 'none';
                        statusEl.classList.remove('d-none', 'alert-success', 'alert-info');
                        statusEl.classList.add('alert-warning');
                        document.getElementById('status-text').textContent = 'Nuevo participante. Complete los datos.';
                        document.getElementById('nombres').readOnly = false;
                        document.getElementById('apellidos').readOnly = false;
                    }

                    console.log('Llamando showStep(2)');
                    showStep(2);
                    console.log('showStep(2) completado');
                })
                .catch(function (err) {
                    console.error('Fetch error:', err);
                    console.log('Type of err:', typeof err);
                    console.log('Err message:', err.message);
                    alert('Error de conexión: ' + (err.message || 'Sin detalles'));
                });
        });

        document.getElementById('btn-back-step1').addEventListener('click', function () {
            showStep(1);
        });

        document.getElementById('form-participante').addEventListener('submit', function (e) {
            e.preventDefault();
            guardarParticipante();
        });

        function guardarParticipante() {
            const nombres = document.getElementById('nombres').value.trim();
            const apellidos = document.getElementById('apellidos').value.trim();

            if (!nombres || !apellidos) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Nombres y apellidos son requeridos' });
                return;
            }

            const body = csrfBody();
            body.cedula = document.getElementById('cedula').value.trim();
            body.nombres = nombres;
            body.apellidos = apellidos;
            body.email = document.getElementById('email').value.trim();
            body.telefono = document.getElementById('telefono').value.trim();

            fetch('<?= url_to('admin.physicalSales.guardarParticipante') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(body)
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    console.log('Guardar response:', JSON.stringify(data));
                    if (data.csrfHash) updateCsrf(data.csrfHash);

                    if (data.status === 'success') {
                        currentParticipant = data.participant;
                        document.getElementById('participant-id').value = data.participant_id;
                        updateResumen();
                        updateTotal();
                        showStep(3);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al guardar' });
                    }
                })
                .catch(function (err) {
                    console.error('Fetch error guardar:', err);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión: ' + (err.message || 'Sin detalles') });
                });
        }

        document.getElementById('cantidad-boletos').addEventListener('input', updateTotal);
        document.getElementById('monto-recibido').addEventListener('input', updateTotal);

        document.getElementById('btn-vender').addEventListener('click', function () {
            const qty = parseInt(document.getElementById('cantidad-boletos').value) || 0;

            if (qty < BOLETOS_MINIMOS || qty > BOLETOS_MAXIMOS) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Cantidad debe estar entre ' + BOLETOS_MINIMOS + ' y ' + BOLETOS_MAXIMOS });
                return;
            }

            if (qty > BOLETOS_DISPONIBLES) {
                Swal.fire({ icon: 'error', title: 'Sinstock', text: 'Solo hay ' + BOLETOS_DISPONIBLES + ' boletos disponibles' });
                return;
            }

            const total = qty * PRECIO_BOLETO;
            const received = parseFloat(document.getElementById('monto-recibido').value) || total;

            Swal.fire({
                title: '¿Confirmar venta?',
                text: 'Total: $' + total.toFixed(2) + ' | Recibido: $' + received.toFixed(2),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    realizarVenta();
                }
            });
        });

        function realizarVenta() {
            const btn = document.getElementById('btn-vender');
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader me-2"></i>Procesando...';

            const body = csrfBody();
            body.participant_id = document.getElementById('participant-id').value;
            body.cantidad = document.getElementById('cantidad-boletos').value;
            body.monto_recibido = document.getElementById('monto-recibido').value;
            body.observaciones = document.getElementById('observaciones').value;

            fetch('<?= url_to('admin.physicalSales.venderBoletos') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(body)
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.csrfHash) updateCsrf(data.csrfHash);

                    if (data.status === 'success') {
                        document.getElementById('success-transaccion').textContent = data.data.transaccion_id;
                        document.getElementById('success-nombre').textContent =
                            (currentParticipant.nombres || '') + ' ' + (currentParticipant.apellidos || '');
                        document.getElementById('success-cantidad').textContent = data.data.cantidad;
                        document.getElementById('success-total').textContent = '$' + parseFloat(data.data.total).toFixed(2);
                        document.getElementById('success-vuelto').textContent = '$' + parseFloat(data.data.vuelto).toFixed(2);

                        const ticketsHtml = data.data.tickets.map(function (t) {
                            return '<div class="col-4"><span class="badge bg-dark">' + t + '</span></div>';
                        }).join('');
                        document.getElementById('success-tickets').innerHTML = ticketsHtml;

                        showStep(4);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="ti ti-cash me-2"></i>Confirmar Venta';
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al realizar la venta' });
                    }
                })
                .catch(function (err) {
                    console.error('Fetch error vender:', err);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-cash me-2"></i>Confirmar Venta';
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión: ' + (err.message || 'Sin detalles') });
                });
        }

        document.getElementById('btn-cancelar-venta').addEventListener('click', function () {
            Swal.fire({
                title: '¿Cancelar venta?',
                text: 'Se perderán los datos ingresados',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'Seguir'
            }).then(function (result) {
                if (result.isConfirmed) {
                    resetForm();
                }
            });
        });

        document.getElementById('btn-nueva-venta').addEventListener('click', resetForm);

        function resetForm() {
            currentParticipant = null;
            document.getElementById('input-cedula').value = '';
            document.getElementById('participant-id').value = '';
            document.getElementById('from-api').value = '0';
            document.getElementById('locked').value = '0';
            document.getElementById('form-participante').reset();
            document.getElementById('monto-recibido').value = '';
            document.getElementById('observaciones').value = '';
            document.getElementById('badge-exists').style.display = 'none';
            document.getElementById('participant-status').classList.add('d-none');
            document.getElementById('nombres').readOnly = false;
            document.getElementById('apellidos').readOnly = false;
            updateTotal();
            showStep(1);
        }
    })();
</script>
<?= $this->endSection() ?>