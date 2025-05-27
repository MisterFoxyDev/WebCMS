<?php
session_start();
require_once "includes/bdd.php";
require_once "includes/text_utils.php";
require_once "includes/ArticleManager.php";
require_once "includes/Pagination.php";
require_once "includes/Security.php";

// Initialisation des classes
$security = Security::getInstance($bdd);
$articleManager = new ArticleManager($bdd);

// Traitement du renvoi du mail de validation
if (isset($_GET['resend_validation']) && $security->isAuthenticated()) {
    // Envoyer le mail
    $result = resendValidationEmail($bdd, $_SESSION['user_id']);

    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }

    // Redirection immédiate
    header('Location: index.php');
    exit();
}

// Vérifier le statut de validation de l'email
if ($security->isAuthenticated()) {
    $_SESSION['email_validated'] = $security->isEmailValidated($_SESSION['user_id']);
}

// Récupération des catégories
$categoriesReq = $bdd->query("SELECT * FROM categories ORDER BY category_name ASC");
$categories = $categoriesReq->fetchAll(PDO::FETCH_ASSOC);

// Configuration de la pagination
$articlesPerPage = 3;
$page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;

if (isset($_GET["category_id"])) {
    $categoryId = (int) $_GET["category_id"];

    // Vérification de l'existence de la catégorie
    $categoryReq = $bdd->query("SELECT category_name FROM categories WHERE category_id = $categoryId");
    $category = $categoryReq->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        $categoryName = $category['category_name'];
        $pageTitle = "Articles de la catégorie $categoryName";

        // Compte total des articles de la catégorie
        $totalArticlesReq = $bdd->query("SELECT COUNT(*) as total FROM articles WHERE article_status = 'publie' AND category_id = $categoryId");
        $totalPublies = $totalArticlesReq->fetch(PDO::FETCH_ASSOC)['total'];

        // Initialisation de la pagination (même pour les catégories vides)
        $pagination = new Pagination($totalPublies, $articlesPerPage, $page, "index.php?category_id=$categoryId");

        // Redirection si la page n'est pas valide et qu'il y a des articles
        if ($totalPublies > 0 && ($page < 1 || $page > $pagination->getTotalPages())) {
            header("Location: index.php?category_id=$categoryId&page=1");
            exit();
        }

        // Récupération des articles
        $articles = $articleManager->getArticlesByCategory($categoryId, $page, $articlesPerPage);
    } else {
        // Redirection si la catégorie n'existe pas
        header("Location: index.php");
        exit();
    }
} else {
    // Récupération des articles récents
    $totalArticlesReq = $bdd->query("SELECT COUNT(*) as total FROM articles WHERE article_status = 'publie'");
    $totalPublies = $totalArticlesReq->fetch(PDO::FETCH_ASSOC)['total'];

    // Initialisation de la pagination
    $pagination = new Pagination($totalPublies, $articlesPerPage, $page, "index.php");

    // Redirection si la page n'est pas valide
    if ($page < 1 || $page > $pagination->getTotalPages()) {
        header("Location: index.php?page=1");
        exit();
    }

    // Récupération des articles
    $articles = $articleManager->getArticlesByCategory(null, $page, $articlesPerPage);
    $pageTitle = "Articles récents";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <?php require_once "includes/header.php"; ?>
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        #layoutSidenav_content {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1 0 auto;
        }

        footer {
            flex-shrink: 0;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php require_once "includes/navigation.php"; ?>

    <div id="layoutSidenav_content">
        <!-- Page Content -->
        <main>
            <div class="container-fluid mt-1 pt-4">
                <div class="row">
                    <!-- Blog Entries Column -->
                    <div class="col-lg-8">
                        <h1 class="mb-4 px-2"><?php echo $pageTitle; ?></h1>
                        <?php if ($security->isAuthenticated() && (!isset($_SESSION['email_validated']) || $_SESSION['email_validated'] != 1)): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Votre adresse email n'a pas encore été validée. Certaines fonctionnalités seront limitées
                                jusqu'à la validation
                                <a href="index.php?resend_validation=1" class="alert-link">Cliquez ici pour renvoyer le mail
                                    de
                                    validation</a>.
                            </div>
                        <?php endif; ?>
                        <?php if (empty($articles)): ?>
                            <div class="alert alert-info">
                                Aucun article trouvé.
                            </div>
                        <?php else: ?>
                            <?php foreach ($articles as $article): ?>
                                <div class="card mb-4">
                                    <?php if (!empty($article["article_img"])): ?>
                                        <img class="card-img-top"
                                            src="img/images_articles/<?php echo $security->validateInput($article["article_img"]); ?>"
                                            alt="Image de l'article" style="max-height: 300px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h2 class="card-title">
                                            <a href="article.php?id=<?php echo $article['article_id']; ?>">
                                                <?php echo $security->validateInput($article["article_title"]); ?>
                                            </a>
                                        </h2>
                                        <p class="card-text">
                                            <?php echo cleanAndTruncateHtml($article["article_content"]); ?>
                                        </p>
                                        <?php
                                        // Afficher les tags si présents
                                        $tags = json_decode($article["article_tags"], true);
                                        if ($tags && !empty($tags)) {
                                            echo '<div class="mt-2">';
                                            foreach ($tags as $tag) {
                                                echo '<span class="badge bg-secondary me-1">' . $security->validateInput($tag['value']) . '</span>';
                                            }
                                            echo '</div>';
                                        }
                                        ?>
                                        <a href="article.php?id=<?php echo $article["article_id"]; ?>"
                                            class="btn btn-primary mt-2">Lire la suite &rarr;</a>
                                    </div>
                                    <div class="card-footer text-muted">
                                        <i class="fas fa-clock"></i>
                                        Publié le <?php echo date('d/m/Y', strtotime($article["article_date"])); ?> par
                                        <b><?php echo $security->validateInput($article["username"]); ?></b>
                                        dans <a
                                            href="index.php?category_id=<?php echo $article["category_id"]; ?>"><?php echo $security->validateInput($article["category_name"]); ?></a>
                                    </div>
                                </div>
                                <hr class="my-4">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- Sidebar Column -->
                    <div class="col-lg-4">
                        <?php require_once "includes/sidebar.php"; ?>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <?php echo $pagination->render(); ?>
        </main>

        <!-- Footer -->
        <?php require_once "includes/footer.php"; ?>
    </div>

    <!-- Scripts -->
    <?php require_once "includes/scripts.php"; ?>

    <!-- Logout Modal -->
    <?php require_once "includes/logout_modal.php"; ?>

    <!-- Sweet Alert Messages -->
    <?php require_once "includes/sweet_alert.php"; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (isset($_SESSION['success_message'])): ?>
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: <?php echo json_encode($_SESSION['success_message']); ?>,
                    showConfirmButton: false,
                    timer: 3000
                });
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: <?php echo json_encode($_SESSION['error_message']); ?>,
                    showConfirmButton: false,
                    timer: 3000
                });
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>