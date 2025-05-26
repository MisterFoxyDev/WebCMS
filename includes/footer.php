</main>
<!-- Footer -->
<footer class="py-4 bg-dark text-white mt-auto">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Copyright &copy; WebCMS 2024</div>
            <div>
                <a href="#">Politique de confidentialité</a>
                &middot;
                <a href="#">Conditions d'utilisation</a>
            </div>
        </div>
    </div>
</footer>
</div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?php echo htmlspecialchars($js); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Logout Modal -->
<?php require_once "includes/logout_modal.php"; ?>

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