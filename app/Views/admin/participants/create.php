<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p>Complete los datos del nuevo participante.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <form id="participantForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="cedula" name="cedula" required maxlength="20">
                                <button type="button" class="btn btn-outline-secondary" id="btn-buscar">
                                    <i class="ti ti-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required maxlength="20">
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="verificado" name="verificado">
                                <label class="form-check-label" for="verificado">Verificado</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Guardar
                            </button>
                            <a href="<?= url_to('admin.participants.index') ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('participantForm');
    const cedulaInput = document.getElementById('cedula');
    const btnBuscar = document.getElementById('btn-buscar');

    function splitName(fullName) {
        const parts = fullName.trim().split(/\s+/).filter(p => p.length > 0);
        const count = parts.length;

        if (count === 0) return { nombres: '', apellidos: '' };
        if (count === 1) return { nombres: parts[0], apellidos: '' };
        if (count === 2) return { nombres: parts[1], apellidos: parts[0] };
        if (count === 3) return { nombres: parts[2], apellidos: parts[0] + ' ' + parts[1] };

        return {
            apellidos: parts[0] + ' ' + parts[1],
            nombres: parts.slice(2).join(' ')
        };
    }

    function buscarCedula() {
        const cedula = cedulaInput.value.trim();

        if (!cedula) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Ingrese una cédula para buscar'
            });
            return;
        }

        btnBuscar.disabled = true;
        btnBuscar.innerHTML = '<i class="ti ti-loader"></i> Buscando...';

        fetch(`<?= site_url('admin/participants/buscar') ?>?cedula=${encodeURIComponent(cedula)}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'exists') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Participante existente',
                        html: `Ya existe un participante registrado con esta cédula:<br><strong>${data.data.full_name}</strong><br>Email: ${data.data.email}`
                    });
                    document.getElementById('nombres').value = data.data.nombres || '';
                    document.getElementById('apellidos').value = data.data.apellidos || '';
                    document.getElementById('email').value = data.data.email || '';
                    document.getElementById('telefono').value = data.data.telefono || '';
                } else if (data.status === 'success') {
                    const fullName = data.data.nombre || '';
                    const split = splitName(fullName);

                    document.getElementById('nombres').value = split.nombres;
                    document.getElementById('apellidos').value = split.apellidos;

                    if (data.data.email) {
                        document.getElementById('email').value = data.data.email;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Datos encontrados',
                        text: 'Los datos fueron cargados correctamente'
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin resultados',
                        text: data.message || 'No se encontraron datos para esta cédula'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al buscar la cédula'
                });
            })
            .finally(() => {
                btnBuscar.disabled = false;
                btnBuscar.innerHTML = '<i class="ti ti-search"></i> Buscar';
            });
    }

    btnBuscar.addEventListener('click', buscarCedula);

    cedulaInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarCedula();
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch('<?= site_url('admin/participants/store') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message
                }).then(() => {
                    window.location.href = '<?= site_url('admin/participants') ?>';
                });
            } else {
                let errorMsg = data.message || 'Error al guardar';
                if (data.errors) {
                    const errorList = Object.values(data.errors).flat().join('<br>');
                    errorMsg = errorList;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMsg
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error de conexión'
            });
        });
    });
});
</script>
<?= $this->endSection() ?>