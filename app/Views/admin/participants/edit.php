<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="mb-6">
            <h1 class="fs-3 mb-1"><?= esc($title) ?></h1>
            <p>Actualice los datos del participante.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <form id="participantForm">
                    <input type="hidden" name="id" value="<?= esc($participant['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required maxlength="100" value="<?= esc($participant['nombres']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required maxlength="100" value="<?= esc($participant['apellidos']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cedula" name="cedula" required maxlength="20" value="<?= esc($participant['cedula']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required maxlength="20" value="<?= esc($participant['telefono']) ?>">
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required maxlength="100" value="<?= esc($participant['email']) ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="verificado" name="verificado" <?= $participant['verificado'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="verificado">Verificado</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3 bg-light">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Código</label>
                                        <div class="fw-bold"><?= esc($participant['codigo']) ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Fecha registro</label>
                                        <div class="fw-bold"><?= date('d M Y', strtotime($participant['created_at'])) ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Última actualización</label>
                                        <div class="fw-bold"><?= date('d M Y H:i', strtotime($participant['updated_at'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Actualizar
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

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const id = formData.get('id');

        fetch(`<?= site_url('admin/participants/update/') ?>${id}`, {
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
                    window.location.href = '<?= url_to('admin.participants.index') ?>';
                });
            } else {
                let errorMsg = data.message || 'Error al actualizar';
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