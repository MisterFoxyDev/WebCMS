<?php
if (isset($_SESSION['success_message'])): ?>
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: <?php echo json_encode($_SESSION['success_message']); ?>,
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php
    unset($_SESSION['success_message']);
endif;

if (isset($_SESSION['error_message'])): ?>
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: <?php echo json_encode($_SESSION['error_message']); ?>,
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php
    unset($_SESSION['error_message']);
endif;
?>