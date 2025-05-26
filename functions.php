<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// SECTION 1: GESTION DES COMMENTAIRES
// =============================================

function addComment($bdd, $articleId, $userId, $commentContent)
{
    // Vérifier si l'utilisateur a validé son email
    $checkEmailReq = $bdd->prepare("SELECT user_email_validation FROM users WHERE user_id = :user_id");
    $checkEmailReq->bindValue(":user_id", $userId);
    $checkEmailReq->execute();
    $user = $checkEmailReq->fetch(PDO::FETCH_ASSOC);

    if ($user['user_email_validation'] == 0) {
        return [
            'success' => false,
            'message' => "Vous devez valider votre adresse email pour pouvoir commenter. Veuillez vérifier votre boîte mail."
        ];
    }

    // Nettoyage et validation du commentaire
    $commentContent = trim($commentContent);

    if (empty($commentContent)) {
        return [
            'success' => false,
            'message' => "Le commentaire ne peut pas être vide"
        ];
    }

    if (strlen($commentContent) < 3) {
        return [
            'success' => false,
            'message' => "Le commentaire doit contenir au moins 3 caractères"
        ];
    }

    // Échapper les caractères HTML
    $commentContent = htmlspecialchars($commentContent, ENT_QUOTES, 'UTF-8');
    $commentDate = date("Y-m-d");

    try {
        $createCommentReq = $bdd->prepare("INSERT INTO comments(comment_content, comment_date, article_id, user_id) VALUES(:comment_content, :comment_date, :article_id, :user_id)");
        $createCommentReq->bindValue(":comment_content", $commentContent);
        $createCommentReq->bindValue(":comment_date", $commentDate);
        $createCommentReq->bindValue(":article_id", $articleId);
        $createCommentReq->bindValue(":user_id", $userId);
        $createCommentRes = $createCommentReq->execute();

        if (!$createCommentRes) {
            return [
                'success' => false,
                'message' => "Un problème est survenu, merci de rééssayer ultérieurement"
            ];
        }

        return [
            'success' => true,
            'message' => "Commentaire envoyé !"
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => "Une erreur est survenue lors de l'envoi du commentaire"
        ];
    }
}

// =============================================
// SECTION 2: GESTION DES ARTICLES
// =============================================

function createNewArticle($bdd, $imgToken)
{
    if (isset($_POST["addArticle"])) {
        // Vérification de la validation de l'email
        $checkEmailReq = $bdd->prepare("SELECT user_email_validation FROM users WHERE user_id = :user_id");
        $checkEmailReq->bindValue(":user_id", $_SESSION["user_id"]);
        $checkEmailReq->execute();
        $user = $checkEmailReq->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['user_email_validation'] != 1) {
            $_SESSION['error_message'] = "Vous devez valider votre adresse email pour pouvoir créer un article.";
            header("Location: index.php");
            exit();
        }

        // Formater les tags en JSON
        $tagsArray = explode(',', $_POST["tags"]);
        $tagsArray = array_map(function ($tag) {
            // Nettoyer le tag
            $tag = str_replace('value:', '', $tag);
            return trim($tag);
        }, $tagsArray);
        $tagsArray = array_filter($tagsArray);
        $tagsArray = array_map(function ($tag) {
            return ['value' => $tag];
        }, $tagsArray);
        $tagsJson = json_encode($tagsArray);

        if (empty($_FILES["articleImage"]["name"])) {
            $image_article = "default_avatar.png";
            $filename = "default_avatar.png";
        } else {
            $errors = validateImage($_FILES["articleImage"]);

            if (empty($errors)) {
                $path = "img/images_articles/";
                $filename = saveImage($_FILES["articleImage"], $path, $imgToken);

                if ($filename === false) {
                    return "Erreur lors de l'enregistrement de l'image";
                } else {
                    $image_article = $filename;
                }
            } else {
                return implode("<br>", $errors);
            }
        }

        if (!isset($message)) {
            $getCatReq = $bdd->prepare("SELECT category_id FROM categories WHERE category_name = :category_name");
            $getCatReq->bindValue(":category_name", $_POST["category"]);
            $getCatReq->execute();
            $catData = $getCatReq->fetch(PDO::FETCH_ASSOC);

            if ($catData) {
                $category_id = $catData["category_id"];
                $date = date("Y-m-d");
                $author_id = $_SESSION["user_id"];

                $req = $bdd->prepare("INSERT INTO articles(article_title, article_date, article_content, article_tags, article_status, article_img, category_id, author_id) VALUES(:article_title, :article_date, :article_content, :article_tags, :article_status, :article_img, :category_id, :author_id)");
                $req->bindValue(":article_title", $_POST["title"]);
                $req->bindValue(":article_date", $date);
                $req->bindValue(":article_content", $_POST["content"]);
                $req->bindValue(":article_tags", $tagsJson);
                $req->bindValue(":article_status", "en_attente");
                $req->bindValue(":article_img", $image_article);
                $req->bindValue(":category_id", $category_id);
                $req->bindValue(":author_id", $author_id);

                if ($req->execute()) {
                    $_SESSION['success_message'] = "Votre article a été soumis avec succès et est en attente de validation par un administrateur.";
                    $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/webcms/index.php";
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    $_SESSION['error_message'] = "Erreur lors de la création de l'article";
                    $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/webcms/index.php";
                    header("Location: " . $redirect_url);
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "Catégorie non trouvée";
                $redirect_url = "http://" . $_SERVER['HTTP_HOST'] . "/webcms/index.php";
                header("Location: " . $redirect_url);
                exit();
            }
        }
    }
}

function cleanTags($tagsJson)
{
    $tagsArray = json_decode($tagsJson, true);
    if (!is_array($tagsArray)) {
        return json_encode([]);
    }

    $cleanTags = [];
    foreach ($tagsArray as $tag) {
        if (isset($tag['value'])) {
            $value = $tag['value'];
            if (is_string($value) && (strpos($value, '{') === 0 || strpos($value, '[') === 0)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($decoded['value'])) {
                        $cleanTags[] = ['value' => $decoded['value']];
                    } elseif (is_array($decoded)) {
                        foreach ($decoded as $dTag) {
                            if (isset($dTag['value'])) {
                                $cleanTags[] = ['value' => $dTag['value']];
                            }
                        }
                    }
                } else {
                    $value = str_replace(['[', ']', '{', '}', '"', '\\', 'value:'], '', $value);
                    $value = trim($value);
                    if (!empty($value)) {
                        $cleanTags[] = ['value' => $value];
                    }
                }
            } else {
                if (!empty($value)) {
                    $cleanTags[] = ['value' => $value];
                }
            }
        }
    }
    return json_encode($cleanTags);
}

// Fonction pour formater le statut de l'article
function formatArticleStatus($status, $is_modified = false)
{
    $statusMap = [
        'publie' => 'Publié',
        'en_attente' => $is_modified ? 'En attente (modifié)' : 'En attente',
        'brouillon' => 'Brouillon'
    ];
    return $statusMap[$status] ?? $status;
}

function showArticles($bdd)
{
    if (!isset($_SESSION['user_id'])) {
        echo "<tr><td colspan='9' class='text-center'>Vous devez être connecté pour voir vos articles</td></tr>";
        return;
    }

    $req = "SELECT a.*, c.category_name, u.username 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.category_id 
            LEFT JOIN users u ON a.author_id = u.user_id 
            WHERE a.author_id = :user_id 
            ORDER BY a.article_id DESC";
    $stmt = $bdd->prepare($req);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($articles)) {
        echo "<tr><td colspan='9' class='text-center'>Vous n'avez pas encore d'articles</td></tr>";
        return;
    }

    foreach ($articles as $article) {
        // Nettoyer et afficher les tags
        $cleanTagsJson = cleanTags($article["article_tags"]);
        $tagsArray = json_decode($cleanTagsJson, true);
        $tagsHtml = '';

        if (is_array($tagsArray)) {
            foreach ($tagsArray as $tag) {
                if (isset($tag['value'])) {
                    $tagsHtml .= '<span class="badge bg-secondary me-1">' . htmlspecialchars($tag['value']) . '</span>';
                }
            }
        }

        $statusClass = '';
        $statusText = '';
        switch ($article["article_status"]) {
            case 'publie':
                $statusClass = 'success';
                $statusText = 'Publié';
                break;
            case 'en_attente':
                $statusClass = 'warning';
                $statusText = $article["is_modified"] ? 'En attente (modifié)' : 'En attente';
                break;
            case 'brouillon':
                $statusClass = 'secondary';
                $statusText = 'Brouillon';
                break;
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars($article["article_title"]) . "</td>";
        echo "<td>" . htmlspecialchars($article["username"]) . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($article["article_date"])) . "</td>";
        echo "<td>" . htmlspecialchars($article["category_name"]) . "</td>";
        echo "<td>" . $tagsHtml . "</td>";
        echo "<td><span class='badge bg-" . $statusClass . "'>" . $statusText . "</span></td>";
        echo "<td class='text-center'>";
        if (!empty($article["article_img"])) {
            echo "<img src='img/images_articles/" . htmlspecialchars($article["article_img"]) . "' 
                      alt='Image de l\'article' style='max-width: 50px; max-height: 50px;' class='img-thumbnail'>";
        }
        echo "</td>";
        echo "<td class='text-center' style='vertical-align: middle;'>";
        echo "<div style='white-space: nowrap;'>";
        echo "<a href='my_articles.php?delete=" . $article["article_id"] . "' 
              onclick='return confirmDelete(" . $article["article_id"] . ")' 
              class='btn btn-sm btn-danger' style='display: inline-block; height: 31px; line-height: 31px; padding: 0 0.5rem; margin: 0 2px;'>Supprimer</a>";
        echo "<a href='update_article.php?modify=" . $article["article_id"] . "' 
              class='btn btn-sm btn-primary' style='display: inline-block; height: 31px; line-height: 31px; padding: 0 0.5rem; margin: 0 2px;'>Modifier</a>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";

        // Mettre à jour les tags dans la base de données
        $updateTagsReq = $bdd->prepare("UPDATE articles SET article_tags = :tags WHERE article_id = :id");
        $updateTagsReq->execute([
            ':tags' => $cleanTagsJson,
            ':id' => $article["article_id"]
        ]);
    }
}

function deleteArticle($bdd)
{
    $article_id = $_GET["delete"];

    // Vérifier si l'article appartient à l'utilisateur connecté
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Vous devez être connecté pour supprimer un article";
        header("Location: my_articles.php");
        exit();
    }

    $userId = $_SESSION['user_id'];
    $checkReq = $bdd->prepare("SELECT author_id FROM articles WHERE article_id = ?");
    $checkReq->execute([$article_id]);
    $article = $checkReq->fetch(PDO::FETCH_ASSOC);

    if (!$article || $article['author_id'] != $userId) {
        $_SESSION['error_message'] = "Vous n'êtes pas autorisé à supprimer cet article";
        header("Location: my_articles.php");
        exit();
    }

    $deleteReq = "DELETE FROM articles WHERE article_id = $article_id";
    $deleteRes = $bdd->exec($deleteReq);

    if (!$deleteRes) {
        $_SESSION['error_message'] = "Erreur lors de la suppression de l'article";
        header("Location: my_articles.php");
        exit();
    }

    $_SESSION['success_message'] = "L'article a bien été supprimé !";
    header("Location: my_articles.php");
    exit();
}

function updateArticle($bdd, $imgToken)
{
    if (isset($_POST["updateArticle"])) {
        // Récupérer l'ID de l'article
        $article_id = $_GET["modify"];

        // Traiter les tags
        $tagsInput = $_POST["tags"];
        $tagsArray = explode(',', $tagsInput);
        $tagsArray = array_map('trim', $tagsArray);
        $tagsArray = array_filter($tagsArray); // Supprimer les éléments vides
        $tagsArray = array_map(function ($tag) {
            // Nettoyer le tag de tout préfixe "value:"
            $tag = str_replace('value:', '', $tag);
            return ['value' => trim($tag)];
        }, $tagsArray);
        $tagsJson = json_encode($tagsArray);

        // Préparer la requête de base
        $sql = "UPDATE articles SET 
                article_title = :title,
                article_content = :content,
                article_tags = :tags,
                category_id = :category_id,
                article_status = 'en_attente',
                is_modified = TRUE";

        $params = [
            ':title' => $_POST["title"],
            ':content' => $_POST["content"],
            ':tags' => $tagsJson,
            ':article_id' => $article_id
        ];

        // Gérer l'image si elle est modifiée
        if (!empty($_FILES["articleImage"]["name"])) {
            $errors = validateImage($_FILES["articleImage"]);

            if (empty($errors)) {
                $path = "img/images_articles/";
                $filename = saveImage($_FILES["articleImage"], $path, $imgToken);

                if ($filename !== false) {
                    $sql .= ", article_img = :image";
                    $params[':image'] = $filename;
                }
            } else {
                $_SESSION['error_message'] = implode("<br>", $errors);
                header("Location: update_article.php?modify=" . $article_id);
                exit();
            }
        }

        // Récupérer l'ID de la catégorie
        $getCatReq = $bdd->prepare("SELECT category_id FROM categories WHERE category_name = :category_name");
        $getCatReq->bindValue(":category_name", $_POST["category"]);
        $getCatReq->execute();
        $catData = $getCatReq->fetch(PDO::FETCH_ASSOC);

        if ($catData) {
            $params[':category_id'] = $catData["category_id"];

            // Ajouter la condition WHERE
            $sql .= " WHERE article_id = :article_id";

            // Exécuter la requête
            $req = $bdd->prepare($sql);

            if ($req->execute($params)) {
                $_SESSION['success_message'] = "L'article a bien été modifié ! Il est maintenant en attente de validation par un modérateur.";
                header("Location: my_articles.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Erreur lors de la modification de l'article";
                header("Location: update_article.php?modify=" . $article_id);
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Catégorie non trouvée";
            header("Location: update_article.php?modify=" . $article_id);
            exit();
        }
    }
}

// =============================================
// SECTION 3: GESTION DES PROFILS
// =============================================

function resendValidationEmail($bdd, $userId)
{
    // Récupérer les informations de l'utilisateur
    $userReq = $bdd->prepare("SELECT user_email, user_token FROM users WHERE user_id = :user_id");
    $userReq->bindValue(":user_id", $userId);
    $userReq->execute();
    $user = $userReq->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Simuler $_POST pour sendmail.php
        $_POST['email'] = $user['user_email'];
        require_once "includes/PHPMailer/sendmail.php";

        // Vérifier si le mail a été envoyé avec succès
        if (isset($GLOBALS['mail_success']) && $GLOBALS['mail_success'] === true) {
            return [
                'success' => true,
                'message' => $GLOBALS['mail_message']
            ];
        }
    }

    return [
        'success' => false,
        'message' => isset($GLOBALS['mail_message']) ? $GLOBALS['mail_message'] : "Une erreur est survenue lors de l'envoi du mail de confirmation."
    ];
}