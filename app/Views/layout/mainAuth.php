<!DOCTYPE html>
<html lang="es">

<?= $this->include('partials/head') ?>
<?= $this->renderSection('style') ?>

<body>
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card " style="max-width:420px; width:100%;">
            <div class="card-body p-5">
                <?= $this->renderSection('content') ?>

                <div class="text-center mt-3 small text-muted">
                    Don't have an account? <a href="signup.html" class="link-primary">Sign up</a>
                </div>
            </div>
        </div>
    </div>
    <?= $this->include('partials/scripts') ?>
    <?= $this->renderSection('scripts') ?>

</body>

</html>