<?php

require_once "includes/bdd.php";
require_once "functions.php";

// Suppression article
if (isset($_GET["delete"])) {
    if (deleteArticle($bdd)) {
        $message = getMessage('success', 'article', 'delete');
        $success = true;
    } else {
        $message = getMessage('error', 'article', 'delete');
        $success = false;
    }
}

// Afficher le message de succès de la session s'il existe
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $success = true;
    unset($_SESSION['success_message']);
}

// Afficher le message d'erreur de la session s'il existe
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $success = false;
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once "includes/header.php"; ?>
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body class="sb-nav-fixed d-flex flex-column min-vh-100">
    <?php require_once "includes/navigation.php"; ?>

    <!-- Page Wrapper -->
    <div id="wrapper" class="d-flex flex-column flex-grow-1">
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column flex-grow-1">
            <!-- Main Content -->
            <div id="content" class="flex-grow-1">

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 pt-4 mb-4 text-gray-800">Gestion des articles</h1>

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
                        if ($success) {
                            echo "<div class='text-center mb-4'><b class='alert-success-message'>$message</b></div>";
                        } else {
                            echo "<div class='text-center mb-4'><b class='alert-error-message'>$message</b></div>";
                        }
                    } ?>
                    <br>

                    <!-- Table -->
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Articles</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Vérifier si l'utilisateur est connecté
                                    if (!isset($_SESSION['user_id'])) {
                                        echo '<div class="alert alert-warning">Vous devez être connecté pour voir vos articles</div>';
                                    } else {
                                        // Vérifier si l'utilisateur a des articles
                                        $checkArticlesReq = $bdd->prepare("SELECT COUNT(*) FROM articles WHERE author_id = :user_id");
                                        $checkArticlesReq->execute([':user_id' => $_SESSION['user_id']]);
                                        $articleCount = $checkArticlesReq->fetchColumn();

                                        if ($articleCount === 0) {
                                            echo '<div class="alert alert-info">Vous n\'avez pas encore d\'articles</div>';
                                        }
                                    }
                                    ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Titre</th>
                                                    <th>Auteur</th>
                                                    <th>Date</th>
                                                    <th>Catégorie</th>
                                                    <th>Mots-clés</th>
                                                    <th>Statut</th>
                                                    <th>Image</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <!-- <tfoot>
                                                <tr>
                                                    <th>Nom de la catégorie</th>
                                                </tr>
                                            </tfoot> -->
                                            <tbody>
                                                <?php
                                                showArticles($bdd)
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

        </div>
        <!-- End of Content Wrapper -->

        <!-- Footer -->
        <footer class="bg-dark">
            <?php require_once "includes/footer.php"; ?>
        </footer>

    </div>
    <!-- End of Page Wrapper -->

    <?php require_once "includes/logout_modal.php"; ?>
    <?php require_once "includes/scripts.php"; ?>

    <script>
        function confirmDelete(articleId) {
            return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');
        }
    </script>

</body>

</html>