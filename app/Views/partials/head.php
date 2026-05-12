<head>
  <meta charset="UTF-8" />
  <title><?= $this->renderSection('title') ?? 'Quick Luck' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url("") ?>/assets/images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url("assets/images/favicon.ico") ?>">
  <link rel="manifest" href="<?= base_url("/assets/site.webmanifest") ?>">

  <script type="module" crossorigin src="<?= base_url("/assets/js/main.js") ?>"></script>
  <link rel="stylesheet" crossorigin href="<?= base_url("/assets/css/main.css") ?>">
  <link rel="stylesheet" href="<?= base_url("/assets/css/sweetalert2.css") ?>">
  <style>
    .bg-purple {
      background-color: #8e63d2 !important;
      color: #fff !important;
    }
  </style>
</head>