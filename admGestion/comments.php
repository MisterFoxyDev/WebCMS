<?php

require_once "includes/admin_header.php";
require_once "includes/bdd.php";
require_once "functions.php";

// Logique de suppression des commentaires
if (isset($_GET['delete'])) {
    $comment_id = (int) $_GET['delete'];

    try {
        // Vérifier si le commentaire existe
        $check_query = "SELECT comment_id FROM comments WHERE comment_id = ?";
        $check_stmt = $bdd->prepare($check_query);
        $check_stmt->execute([$comment_id]);

        if ($check_stmt->rowCount() > 0) {
            // Supprimer le commentaire
            $delete_query = "DELETE FROM comments WHERE comment_id = ?";
            $delete_stmt = $bdd->prepare($delete_query);
            $delete_stmt->execute([$comment_id]);

            $_SESSION['success_message'] = "Le commentaire a été supprimé avec succès.";
        } else {
            $_SESSION['success_message'] = "Le commentaire n'existe pas.";
        }
    } catch (PDOException $e) {
        $_SESSION['success_message'] = "Une erreur est survenue lors de la suppression du commentaire.";
    }

    // Rediriger pour éviter la resoumission du formulaire
    header("Location: comments.php");
    exit();
}

// Afficher le message de succès de la session s'il existe
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
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
                    <h1 class="h3 mb-4 text-gray-800">Commentaires</h1>

                    <?php if (isset($message)) {
                        if (strpos($message, "succès") !== false) {
                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #d4edda; padding: 10px; border-radius: 5px;\">$message</b></div>";
                        } else {
                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">$message</b></div>";
                        }
                    } ?>

                    <!-- Table -->
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Commentaires</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Article</th>
                                                    <th>Auteur</th>
                                                    <th>Email</th>
                                                    <th>Contenu</th>
                                                    <th>Date</th>
                                                    <th>Suppression</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                showComments($bdd);
                                                ?>
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