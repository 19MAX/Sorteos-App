<!DOCTYPE html>
<html lang="es">

<?= $this->include('partials/head') ?>
<?= $this->renderSection('style') ?>

<body>
    <div id="overlay" class="overlay"></div>
    <?= $this->include('partials/topbar') ?>
    <?= $this->include('partials/sidebar') ?>
    <main id="content" class="content py-10">
        <div class="container-fluid">
            <?= $this->renderSection('content') ?>
            <?= $this->include('partials/footer') ?>

        </div>
    </main>
    <?= $this->include('partials/scripts') ?>
    <?= $this->renderSection('scripts') ?>

</body>

</html>