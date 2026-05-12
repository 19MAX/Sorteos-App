<?= $this->extend('layout/mainAuth') ?>
<?= $this->section('title') ?>
Inicio de sesión
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="text-center mb-3">
    <a href="index.html" class="mb-4 d-inline-block"><img
            src="data:image/svg+xml,%3csvg%20width='62'%20height='67'%20viewBox='0%200%2062%2067'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M30.604%2066.378L0.00805664%2048.1582V35.7825L30.604%2054.0023V66.378Z'%20fill='%23302C4D'/%3e%3cpath%20d='M61.1996%2048.1582L30.604%2066.378V54.0023L61.1996%2035.7825V48.1582Z'%20fill='%23E66239'/%3e%3cpath%20d='M30.5955%200L0%2018.2198V30.5955L30.5955%2012.3757V0Z'%20fill='%23657E92'/%3e%3cpath%20d='M61.191%2018.2198L30.5955%200V12.3757L61.191%2030.5955V18.2198Z'%20fill='%23A3B2BE'/%3e%3cpath%20d='M30.604%2048.8457L0.00805664%2030.6259V18.2498L30.604%2036.47V48.8457Z'%20fill='%23302C4D'/%3e%3cpath%20d='M61.1996%2030.6259L30.604%2048.8457V36.47L61.1996%2018.2498V30.6259Z'%20fill='%23E66239'/%3e%3c/svg%3e"
            alt="" width="36">
        <span class=" ms-2"> <img src="./assets/images/logo.svg" alt=""></span>
    </a>
    <h1 class="card-title mb-5 h5">Inicia sesión</h1>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<form class="needs-validation mt-3" method="POST" action="<?= site_url('/login') ?>" novalidate>
    <div class="mb-3">
        <label for="username" class="form-label">Nombre de usuario</label>
        <input id="username" name="username" type="text" class="form-control" placeholder="Ingrese su nombre de usuario" required autofocus>
        <div class="invalid-feedback">Ingrese su nombre de usuario.</div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label d-flex justify-content-between">
            <span>Contraseña</span>
            <!-- <a href="#" class="small link-primary">Forgot Password?</a> -->
        </label>
        <input id="password" name="password" type="password" class="form-control" placeholder="Contraseña" required minlength="6">
        <div class="invalid-feedback">Ingrese una contraseña (mínimo 6 caracteres).</div>
    </div>

    <!-- <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input id="remember" class="form-check-input" type="checkbox">
            <label class="form-check-label small" for="remember">Remember me</label>
        </div>
    </div> -->

    <button class="btn btn-primary w-100" type="submit">Iniciar sesión</button>
</form>
<?= $this->endSection() ?>