<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $this->escape($pageTitle) : 'WebCMS'; ?></title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">

    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link href="<?php echo $this->escape($css); ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="sb-nav-fixed d-flex flex-column min-vh-100">
    <?php $this->partial('navigation'); ?>

    <!-- Page Content -->
    <main class="flex-grow-1">
        <?php echo $content; ?>
    </main>

    <?php $this->partial('footer'); ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?php echo $this->escape($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Logout Modal -->
    <?php $this->partial('logout_modal'); ?>

    <!-- Sweet Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: <?php echo json_encode($_SESSION['success_message']); ?>,
                showConfirmButton: false,
                timer: 3000
            });
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: <?php echo json_encode($_SESSION['error_message']); ?>,
                showConfirmButton: false,
                timer: 3000
            });
        </script>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</body>

</html>