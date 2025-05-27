<?php
session_start();
require_once "includes/bdd.php";
require_once "includes/text_utils.php";
require_once "functions.php";

// Gestion de la modification des commentaires
if (isset($_POST["editComment"]) && isset($_GET["edit"])) {
    $commentId = $_GET["edit"];
    $articleId = $_GET["id"];

    // Vérifier si le commentaire existe et appartient à l'utilisateur
    $checkReq = "SELECT * FROM comments WHERE comment_id = :comment_id AND user_id = :user_id";
    $checkStmt = $bdd->prepare($checkReq);
    $checkStmt->execute([
        ':comment_id' => $commentId,
        ':user_id' => $_SESSION["user_id"]
    ]);

    if ($checkStmt->rowCount() > 0) {
        $newContent = trim($_POST["commentContent"]);

        if (empty($newContent)) {
            $_SESSION['error_message'] = "Le commentaire ne peut pas être vide.";
            echo "<script>window.location.href = 'article.php?id=" . $articleId . "&edit=" . $commentId . "';</script>";
            exit();
        }

        try {
            $updateReq = "UPDATE comments SET comment_content = :content WHERE comment_id = :id AND user_id = :user_id";
            $updateStmt = $bdd->prepare($updateReq);
            $result = $updateStmt->execute([
                ':content' => $newContent,
                ':id' => $commentId,
                ':user_id' => $_SESSION["user_id"]
            ]);

            if ($result) {
                $_SESSION['success_message'] = "Le commentaire a été modifié avec succès !";
                echo "<script>window.location.href = 'article.php?id=" . $articleId . "';</script>";
                exit();
            } else {
                $_SESSION['error_message'] = "Une erreur est survenue lors de la modification du commentaire.";
                echo "<script>window.location.href = 'article.php?id=" . $articleId . "&edit=" . $commentId . "';</script>";
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Une erreur est survenue lors de la modification du commentaire.";
            echo "<script>window.location.href = 'article.php?id=" . $articleId . "&edit=" . $commentId . "';</script>";
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à modifier ce commentaire.";
        echo "<script>window.location.href = 'article.php?id=" . $articleId . "';</script>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Traitement des données POST
}

if (isset($_GET["id"])) {
    $articleId = $_GET["id"];
    $req = "SELECT a.*, u.username, c.category_name FROM articles a
    JOIN users u ON a.author_id = u.user_id 
    JOIN categories c ON a.category_id = c.category_id
    WHERE a.article_id = :article_id";
    $stmt = $bdd->prepare($req);
    $stmt->execute(['article_id' => $articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        $_SESSION['error_message'] = "L'article demandé n'existe pas";
        header("location:index.php");
        exit();
    }

    $title = $article["article_title"];
    $date = $article["article_date"];
    $content = $article["article_content"];
    $tags = json_decode($article["article_tags"], true);
    $status = $article["article_status"];
    $category = $article["category_name"];
    $username = $article["username"];
    $img = $article["article_img"];

    if (isset($_POST["postComment"])) {
        if (isset($_SESSION["user_id"])) {
            $user_id = $_SESSION["user_id"];
            $commentContent = trim($_POST["commentContent"]);

            if (empty($commentContent)) {
                $_SESSION['error_message'] = "Le commentaire ne peut pas être vide";
                header("Location: article.php?id=" . $articleId);
                exit();
            }

            $result = addComment($bdd, $articleId, $user_id, $commentContent);

            if ($result['success']) {
                $_SESSION['success_message'] = "Le commentaire a été ajouté avec succès !";
                header("Location: article.php?id=" . $articleId);
                exit();
            } else {
                $_SESSION['error_message'] = $result['message'];
                header("Location: article.php?id=" . $articleId);
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Vous devez être connecté pour pouvoir laisser un commentaire !";
            header("Location: article.php?id=" . $articleId);
            exit();
        }
    }
} else if (isset($_GET["edit"]) && $_GET["edit"] != "") {
    $commentId = $_GET["edit"];

    // Vérification des droits de modification
    $checkReq = "SELECT * FROM comments WHERE comment_id = $commentId AND user_id = " . $_SESSION["user_id"];
    $checkRes = $bdd->query($checkReq);

    if ($checkRes->rowCount() > 0) {
        $comment = $checkRes->fetch(PDO::FETCH_ASSOC);
        $articleId = $comment["article_id"];
        $commentContent = $comment["comment_content"];

        // Traitement de la soumission du formulaire de modification
        if (isset($_POST["editComment"])) {
            $newContent = trim($_POST["commentContent"]);

            if (empty($newContent)) {
                $_SESSION['error_message'] = "Le commentaire ne peut pas être vide.";
                header("Location: article.php?id=" . $articleId . "&edit=" . $commentId);
                exit();
            }

            try {
                $updateReq = "UPDATE comments SET comment_content = :content WHERE comment_id = :id AND user_id = :user_id";
                $updateStmt = $bdd->prepare($updateReq);
                $result = $updateStmt->execute([
                    ':content' => $newContent,
                    ':id' => $commentId,
                    ':user_id' => $_SESSION["user_id"]
                ]);

                if ($result) {
                    $_SESSION['success_message'] = "Le commentaire a été modifié avec succès !";
                    header("Location: article.php?id=" . $articleId);
                    exit();
                } else {
                    $_SESSION['error_message'] = "Une erreur est survenue lors de la modification du commentaire.";
                    header("Location: article.php?id=" . $articleId . "&edit=" . $commentId);
                    exit();
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Une erreur est survenue lors de la modification du commentaire.";
                header("Location: article.php?id=" . $articleId . "&edit=" . $commentId);
                exit();
            }
        }

        // Charger l'article pour l'affichage
        $req = "SELECT a.*, u.username, c.category_name FROM articles a
        JOIN users u ON a.author_id=u.user_id 
        JOIN categories c ON a.category_id=c.category_id
        WHERE a.article_id=$articleId";
        $res = $bdd->query($req);
        $article = $res->fetch(PDO::FETCH_ASSOC);

        if ($article) {
            $title = $article["article_title"];
            $date = $article["article_date"];
            $content = $article["article_content"];
            $tags = json_decode($article["article_tags"], true);
            $status = $article["article_status"];
            $category = $article["category_name"];
            $username = $article["username"];
            $img = $article["article_img"];
        }
    } else {
        header("Location: index.php");
        exit();
    }
} else if (isset($_GET["delete"]) && $_GET["delete"] != "") {
    $commentId = $_GET["delete"];

    // Vérification des droits de suppression
    $checkReq = "SELECT * FROM comments WHERE comment_id = $commentId AND user_id = " . $_SESSION["user_id"];
    $checkRes = $bdd->query($checkReq);

    if ($checkRes->rowCount() > 0) {
        $comment = $checkRes->fetch(PDO::FETCH_ASSOC);
        $articleId = $comment["article_id"];

        // Traitement de la suppression
        if (isset($_GET["error"])) {
            $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression du commentaire.";
        } else {
            $deleteReq = "DELETE FROM comments WHERE comment_id = $commentId";
            if ($bdd->exec($deleteReq)) {
                $_SESSION['success_message'] = "Le commentaire a été supprimé avec succès !";
            } else {
                $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression du commentaire.";
            }
        }

        header("Location: article.php?id=" . $articleId);
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
} else {
    header("location:index.php");
    exit();
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

    <!-- Page Content -->
    <div class="container mt-1 pt-4">
        <div class="row">
            <!-- Blog Entries Column -->
            <div class="col-lg-8">
                <!-- Bouton Retour -->
                <a href="index.php" class="btn btn-primary mb-3">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                </a>
                <?php
                if ((isset($_GET["id"]) && $_GET["id"] != "") || (isset($_GET["delete"]) && $_GET["delete"] != "")) {
                    ?>
                    <h1 class="my-4"><?= $title ?></h1>
                    <div><i class="fas fa-clock"></i> Publié le
                        <?php echo date('d/m/Y', strtotime($article["article_date"])); ?> par
                        <b><?php echo htmlspecialchars($article["username"]); ?></b>
                        dans <a
                            href="index.php?category_id=<?php echo $article["category_id"]; ?>"><?php echo htmlspecialchars($article["category_name"]); ?></a>
                    </div>
                    <br>
                    <div class="card mb-4" style="width: 100%;">
                        <?php if (!empty($img)): ?>
                            <img class="card-img-top" src="img/images_articles/<?php echo htmlspecialchars($img); ?>"
                                alt="Image de l'article" style="max-height: 400px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h2 class="card-title"><?php echo htmlspecialchars($title); ?></h2>
                            <div class="card-text" style="width: 100%; word-wrap: break-word; white-space: normal;">
                                <?php echo cleanHtml($content); ?>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <?php
                            // Afficher les tags si présents
                            if ($tags && !empty($tags)) {
                                echo '<div class="mt-2">';
                                foreach ($tags as $tag) {
                                    echo '<span class="badge bg-secondary me-1">' . htmlspecialchars($tag['value']) . '</span>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                } else if (isset($_GET["edit"]) && $_GET["edit"] != "") {
                    ?>
                        <div class="well col-lg-8">
                            <h4>Modifier le commentaire :</h4>
                            <form action="" method="post">
                                <div class="form-group">
                                    <textarea class="form-control" rows="3" name="commentContent"
                                        value="<?= $commentContent ?>"></textarea>
                                </div>
                                <?php
                                if (isset($message)) {
                                    echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">$message</b></div>";
                                }
                                ?>
                                <input class="btn btn-primary" type="submit" name="postComment">
                            </form>
                        </div>

                <?php }
                ?>
            </div>
        </div>
        <!-- Comment Form -->
        <?php if (isset($_SESSION["user_id"])): ?>
            <?php if ($_SESSION["email_validated"] == 1): ?>
                <div class="card my-4">
                    <h5 class="card-header">Laisser un commentaire :</h5>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <textarea class="form-control" name="commentContent" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="postComment" class="btn btn-primary mt-3">Publier</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card my-4">
                    <h5 class="card-header">Commentaires</h5>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Vous devez valider votre adresse email pour pouvoir commenter.
                            Veuillez vérifier votre boîte mail et cliquer sur le lien de validation.
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card my-4">
                <h5 class="card-header">Commentaires</h5>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Vous devez être <a href="login.php">connecté</a> pour pouvoir commenter.
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Posted comments  -->
        <?php
        if (isset($_GET["id"]) && !empty($_GET["id"])) {
            $article_id = $_GET["id"];
            $req = "SELECT * FROM comments WHERE article_id=$article_id";
            $res = $bdd->query($req);

            if (!$res) {
                echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">La récupération des commentaires a rencontré un problème ! Merci de rééssayer ultérieurement</b></div>";
            } else {
                while ($line = $res->fetch(PDO::FETCH_ASSOC)) {

                    $user_id = $line["user_id"];
                    $comment_id = $line["comment_id"];
                    $comment_content = $line["comment_content"];
                    $comment_date = $line["comment_date"];
                    $date_fr = date("d/m/Y", strtotime($comment_date));

                    // Infos auteur
                    $getCommentAuthorReq = "SELECT * FROM users WHERE user_id = $user_id";
                    $getCommentAuthorRes = $bdd->query($getCommentAuthorReq);
                    $userLine = $getCommentAuthorRes->fetch(PDO::FETCH_ASSOC);

                    if ($userLine) {
                        $username = $userLine["username"];
                        $userImg = $userLine["user_img"];

                        // Affichage
                        ?>
                        <div class="media" id="comment-<?= $comment_id ?>">
                            <a href="" class="pull-left">
                                <img class="img-profile rounded-circle" style="width: 40px; height: 40px; object-fit: cover;"
                                    src="img/photo_profil/<?php echo $userImg; ?>">
                            </a>
                            <div class="media-body">
                                <h4 style="display: inline;" class="media-heading"><span class="mx-2"><?= $username ?></span></h4>
                                <small>Publié le <?= $date_fr ?></small>
                                <?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] === $user_id) {
                                    if (isset($_GET["edit"]) && $_GET["edit"] == $comment_id) {
                                        echo '<form action="article.php?id=' . $article_id . '&edit=' . $comment_id . '" method="post" class="mt-2">
                                            <div class="form-group">
                                                <textarea class="form-control" rows="3" name="commentContent" required>' . html_entity_decode($comment_content) . '</textarea>
                                            </div>
                                            <div class="d-flex gap-2" style="align-items: baseline; margin-bottom: 20px;">
                                                <input type="hidden" name="editComment" value="1">
                                                <button type="submit" class="btn btn-primary" style="height: 38px; display: inline-block; vertical-align: baseline;">Modifier</button>
                                                <a href="article.php?id=' . $article_id . '" class="btn btn-secondary" style="height: 38px; display: inline-block; vertical-align: baseline;">Annuler</a>
                                            </div>
                                        </form>';
                                    } else {
                                        echo "|<a href='article.php?id=" . $article_id . "&edit=$comment_id'> Modifier</a> | <a href='javascript:void(0)' onclick='confirmDelete($comment_id)'> Supprimer</a>";
                                    }
                                } ?>
                                <?php if (!isset($_GET["edit"]) || $_GET["edit"] != $comment_id) {
                                    echo "<br><br>" . $comment_content . "<br><br><br>";
                                } ?>
                            </div>
                        </div>
                        <?php
                    } else {
                        // Afficher un message si l'utilisateur n'existe plus
                        ?>
                        <div class="media" id="comment-<?= $comment_id ?>">
                            <div class="media-body">
                                <h4 style="display: inline;" class="media-heading"><span class="mx-2">Utilisateur supprimé</span></h4>
                                <small>Publié le <?= $date_fr ?></small>
                                <?php if (!isset($_GET["edit"]) || $_GET["edit"] != $comment_id) {
                                    echo "<br><br>" . $comment_content . "<br><br><br>";
                                } ?>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
        }

        ?>

    </div>

    <!-- Footer -->
    <?php require_once "includes/footer.php"; ?>

    <!-- Scripts -->
    <?php require_once "includes/scripts.php"; ?>

    <?php require_once "includes/sweet_alert.php"; ?>

    <script>
        function confirmDelete(commentId) {
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'article.php?delete=' + commentId;
                }
            });
        }

        // Gestion des messages de session
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '" . addslashes($_SESSION['success_message']) . "',
                showConfirmButton: false,
                timer: 1500
            });";
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo "Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '" . addslashes($_SESSION['error_message']) . "',
                showConfirmButton: false,
                timer: 1500
            });";
            unset($_SESSION['error_message']);
        }
        ?>

        // Scroll vers le commentaire modifié
        <?php if (isset($_GET['edit'])): ?>
            document.addEventListener('DOMContentLoaded', function () {
                const commentId = <?php echo $_GET['edit']; ?>;
                const commentElement = document.getElementById('comment-' + commentId);
                if (commentElement) {
                    commentElement.scrollIntoView({ behavior: 'instant' });
                }
            });
        <?php endif; ?>
    </script>

    <!-- Bouton Haut de page -->
    <button class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Afficher/masquer le bouton quand on scroll
        window.onscroll = function () {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                document.getElementById("backToTop").style.display = "block";
            } else {
                document.getElementById("backToTop").style.display = "none";
            }
        };

        // Fonction pour remonter en haut
        document.getElementById("backToTop").onclick = function () {
            document.body.scrollTop = 0; // Pour Safari
            document.documentElement.scrollTop = 0; // Pour Chrome, Firefox, IE et Opera
        };
    </script>

    <!-- Logout Modal -->
    <?php require_once "includes/logout_modal.php"; ?>
</body>

</html>