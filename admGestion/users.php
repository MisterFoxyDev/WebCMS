<?php
session_start();

require_once "includes/admin_header.php";
require_once "../includes/bdd.php";
require_once "functions.php";

// Traitement des actions
if (isset($_GET["delete"])) {
    if (deleteUser($bdd)) {
        $message = "L'utilisateur a bien été supprimé !";
    } else {
        $message = "Erreur lors de la suppression de l'utilisateur.";
    }
}

displayHeader();
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php require_once "includes/admin_left_sidebar.php"; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php require_once "includes/admin_topbar.php"; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Utilisateurs</h1>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($message)) {
                        if ($message === "L'utilisateur a bien été supprimé !") {
                            echo "<div class='alert alert-success'>$message</div>";
                        } else {
                            echo "<div class='alert alert-danger'>$message</div>";
                        }
                    } ?>
                    <br>

                    <!-- Table des utilisateurs -->
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Utilisateurs</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Prénom</th>
                                                    <th>Email</th>
                                                    <th>Nom d'utilisateur</th>
                                                    <th>Rôle</th>
                                                    <th>Email validé</th>
                                                    <th>Photo</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php showUsers($bdd); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <?php require_once "includes/admin_footer.php"; ?>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php require_once "includes/admin_logout_modal.php"; ?>
    <?php require_once "includes/admin_scripts.php"; ?>

</body>

</html>