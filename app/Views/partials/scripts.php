<script type="module" crossorigin src="<?= base_url("/assets/js/main.js") ?>"></script>

<script>
function handleFormResponse(action, modalId, fieldErrors) {
    if (!action || !modalId) return;
    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    modalEl.addEventListener('shown.bs.modal', function onShown() {
        applyFieldErrors(modalEl, fieldErrors);
    }, { once: true });
}

function applyFieldErrors(container, fieldErrors) {
    if (!fieldErrors || typeof fieldErrors !== 'object') return;
    container.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    container.querySelectorAll('.invalid-feedback.dynamic-error').forEach(el => el.remove());
    Object.entries(fieldErrors).forEach(([field, message]) => {
        const input = container.querySelector(`[name="${field}"]`);
        if (!input) return;
        input.classList.add('is-invalid');
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback dynamic-error';
        feedback.textContent = message;
        input.insertAdjacentElement('afterend', feedback);
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url("/assets/js/sweetalert2.js") ?>"></script>

<script>
<?php if (session()->has('flashMessages')): ?>
    <?php foreach (session('flashMessages') as $message): ?>
        <?php
            $type     = $message[1];
            $msg      = $message[0];
            $position = $message[2] ?? 'top-end';
        ?>
        showAlert(
            <?= json_encode($type) ?>,
            <?= json_encode($msg) ?>,
            <?= json_encode($position) ?>
        );
    <?php endforeach; ?>
<?php endif; ?>

<?php
    // ✅ Centralizado aquí — funciona para CUALQUIER vista que use este partial
    $lastAction      = session()->getFlashdata('last_action');
    $flashValidation = session()->getFlashdata('flashValidation') ?? [];
?>
<?php if ($lastAction): ?>
    document.addEventListener('DOMContentLoaded', function () {
        // Cada vista define su propio actionModalMap en su sección scripts
        if (typeof actionModalMap !== 'undefined' && actionModalMap[<?= json_encode($lastAction) ?>]) {
            handleFormResponse(
                <?= json_encode($lastAction) ?>,
                actionModalMap[<?= json_encode($lastAction) ?>],
                <?= json_encode($flashValidation) ?>
            );
        }
    });
<?php endif; ?>

const base_url = '<?= base_url() ?>';
</script>