<?php
session_start();

require_once "includes/admin_header.php";
require_once "../includes/bdd.php";
require_once "functions.php";

// Gestion de la suppression d'article
if (isset($_GET["delete"])) {
    if (deleteArticle($bdd)) {
        $_SESSION['success_message'] = "L'article a bien été supprimé !";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la suppression de l'article.";
    }
    header("Location: all_articles.php");
    exit();
}

// Gestion des messages de notification
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

displayHeader();
?>

<body id="page-top">
    <div id="wrapper">
        <?php require_once "includes/admin_left_sidebar.php"; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php require_once "includes/admin_topbar.php"; ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Gestion des articles</h1>

                    <?php if (isset($message)) {
                        if (strpos($message, "succès") !== false || strpos($message, "bien été") !== false) {
                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #d4edda; padding: 10px; border-radius: 5px;\">$message</b></div>";
                        } else {
                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">$message</b></div>";
                        }
                    } ?>

                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Articles</h6>
                                </div>
                                <div class="card-body">
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
                                                    <th>Modifié</th>
                                                    <th>Image</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php showArticles($bdd); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <?php require_once "includes/admin_footer.php"; ?>

        </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php require_once "includes/admin_logout_modal.php"; ?>
    <?php require_once "includes/admin_scripts.php"; ?>

</body>

</html>