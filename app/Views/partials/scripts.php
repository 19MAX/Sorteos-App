<script type="module" crossorigin src="<?= base_url("/assets/js/main.js") ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url("/assets/js/sweetalert2.js") ?>"></script>

<script>

  // Verificar si hay mensajes de éxito, advertencia o error
  <?php if (session()->has('flashMessages')): ?>
    <?php foreach (session('flashMessages') as $message): ?>
      <?php
      $type = $message[1];
      $msg = $message[0];
      $position = $message[2] ?? 'top-end';
      ?>
      showAlert(
        <?= json_encode($type ?? "") ?>,
        <?= json_encode($msg ?? "") ?>,
        <?= json_encode($position ?? "") ?>,
      );
    <?php endforeach; ?>
  <?php endif; ?>
  const base_url = '<?= base_url() ?>';
</script>