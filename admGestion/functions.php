<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/webcms/includes/token.php";

const OPERATION_SUCCESS = true;
const OPERATION_FAILURE = false;

// =============================================
// SECTION 1: GESTION DES MESSAGES ET NOTIFICATIONS
// =============================================

/**
 * Fonction centrale pour gérer tous les messages
 */
function getMessage($type, $entity, $operation)
{
    $messages = [
        'success' => [
            'category' => [
                'add' => "La catégorie a bien été ajoutée !",
                'update' => "La catégorie a bien été modifiée !",
                'delete' => "La catégorie a bien été supprimée !"
            ],
            'article' => [
                'add' => "L'article a bien été ajouté !",
                'update' => "L'article a bien été modifié !",
                'delete' => "L'article a bien été supprimé !"
            ],
            'user' => [
                'add' => "L'utilisateur a bien été ajouté !",
                'update' => "L'utilisateur a bien été modifié !",
                'delete' => "L'utilisateur a bien été supprimé !",
                'login' => "Connexion réussie !",
                'register' => "Inscription réussie ! Vous pouvez maintenant vous connecter."
            ],
            'profile' => [
                'update' => "Votre profil a bien été mis à jour !",
                'password' => "Votre mot de passe a bien été modifié !"
            ]
        ],
        'error' => [
            'category' => [
                'add' => "Erreur lors de l'ajout de la catégorie",
                'update' => "Erreur lors de la modification de la catégorie",
                'delete' => "Erreur lors de la suppression de la catégorie",
                'exists' => "Cette catégorie existe déjà"
            ],
            'article' => [
                'add' => "Erreur lors de l'ajout de l'article",
                'update' => "Erreur lors de la modification de l'article",
                'delete' => "Erreur lors de la suppression de l'article",
                'image' => "Erreur lors du traitement de l'image"
            ],
            'user' => [
                'add' => "Erreur lors de l'ajout de l'utilisateur",
                'update' => "Erreur lors de la modification de l'utilisateur",
                'delete' => "Erreur lors de la suppression de l'utilisateur",
                'login' => "Email ou mot de passe incorrect",
                'register' => "Erreur lors de l'inscription",
                'exists' => "Ce nom d'utilisateur ou cette adresse email est déjà utilisé",
                'password' => "Les mots de passe ne correspondent pas"
            ],
            'profile' => [
                'update' => "Erreur lors de la mise à jour du profil",
                'password' => "Erreur lors de la modification du mot de passe",
                'image' => "Erreur lors du traitement de la photo de profil"
            ],
            'validation' => [
                'required' => "Veuillez remplir tous les champs obligatoires",
                'email' => "Veuillez entrer une adresse email valide",
                'password' => "Le mot de passe doit contenir au moins 8 caractères",
                'name' => "Veuillez entrer un nom valide (lettres, espaces et tirets uniquement)",
                'firstname' => "Veuillez entrer un prénom valide (lettres, espaces et tirets uniquement)",
                'username' => "Veuillez entrer un nom d'utilisateur valide (lettres et chiffres uniquement)"
            ]
        ]
    ];

    return $messages[$type][$entity][$operation] ?? '';
}

/**
 * Fonctions spécifiques pour rétrocompatibilité des messages
 */
function getCategoryMessage($type, $operation = '')
{
    return getMessage($type, 'category', $operation);
}

function getArticleMessage($type, $operation = '')
{
    return getMessage($type, 'article', $operation);
}

function getUserMessage($type, $operation = '')
{
    return getMessage($type, 'user', $operation);
}

function getProfileMessage($type, $operation = '')
{
    return getMessage($type, 'profile', $operation);
}

function getValidationMessage($type)
{
    return getMessage('error', 'validation', $type);
}

/**
 * Fonction pour gérer les redirections avec messages
 */
function redirectWithMessage($operation, $success = true)
{
    $type = $success ? 'success' : 'error';
    header("Location: categories.php?$type=$operation");
    exit();
}

// =============================================
// SECTION 2: GESTION DES CATÉGORIES
// =============================================

function createNewCategory($bdd)
{
    if (empty($_POST["categoryName"]) || !preg_match("/^[\p{L}\s0-9-]+$/u", $_POST["categoryName"])) {
        $_SESSION['error_message'] = "Veuillez entrer une catégorie valide (lettres, chiffres, espaces et tirets uniquement)";
        header("Location: categories.php");
        exit();
    }

    // Vérification si la catégorie existe déjà (insensible à la casse)
    $checkReq = $bdd->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(category_name) = LOWER(:categoryName)");
    $checkReq->bindValue(":categoryName", $_POST["categoryName"]);
    $checkReq->execute();
    $exists = $checkReq->fetchColumn();

    if ($exists > 0) {
        $_SESSION['error_message'] = "Cette catégorie existe déjà !";
        header("Location: categories.php");
        exit();
    }

    $req = $bdd->prepare("INSERT INTO categories(category_name) VALUES(:categoryName)");
    $req->bindValue(":categoryName", $_POST["categoryName"]);
    $res = $req->execute();

    if (!$res) {
        $_SESSION['error_message'] = "Un problème est survenu, veuillez rééssayer";
        header("Location: categories.php");
        exit();
    }

    $_SESSION['success_message'] = "La catégorie a bien été ajoutée !";
    header("Location: categories.php");
    exit();
}

function updateCategory($bdd)
{
    if (empty($_POST["category_name_modif"]) || !preg_match("/^[\p{L}\s0-9-]+$/u", $_POST["category_name_modif"])) {
        $_SESSION['error_message'] = "Veuillez entrer une catégorie valide (lettres, chiffres, espaces et tirets uniquement)";
        header("Location: categories.php");
        exit();
    }

    // Vérification si la catégorie existe déjà (insensible à la casse) en excluant la catégorie en cours de modification
    $checkReq = $bdd->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(category_name) = LOWER(:categoryName) AND category_id != :category_id");
    $checkReq->bindValue(":categoryName", $_POST["category_name_modif"]);
    $checkReq->bindValue(":category_id", $_POST["category_id_modif"]);
    $checkReq->execute();
    $exists = $checkReq->fetchColumn();

    if ($exists > 0) {
        $_SESSION['error_message'] = "Cette catégorie existe déjà !";
        header("Location: categories.php");
        exit();
    }

    $updateReq = $bdd->prepare("UPDATE categories SET category_name = :categoryName WHERE category_id = :category_id");
    $updateReq->bindValue(":categoryName", $_POST["category_name_modif"]);
    $updateReq->bindValue(":category_id", $_POST["category_id_modif"]);
    $updateRes = $updateReq->execute();

    if (!$updateRes) {
        $_SESSION['error_message'] = "Un problème est survenu, veuillez rééssayer";
        header("Location: categories.php");
        exit();
    }

    $_SESSION['success_message'] = "La catégorie a bien été modifiée !";
    header("Location: categories.php");
    exit();
}

function deleteCategory($bdd)
{
    // Vérifier si l'utilisateur est connecté et est admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        $_SESSION['error_message'] = "Vous n'avez pas les droits nécessaires pour supprimer une catégorie";
        header("Location: categories.php");
        exit();
    }

    $category_id = $_GET["delete"];

    try {
        // Démarrer une transaction
        $bdd->beginTransaction();

        // Supprimer d'abord tous les articles de la catégorie
        $deleteArticlesReq = "DELETE FROM articles WHERE category_id = :category_id";
        $deleteArticlesStmt = $bdd->prepare($deleteArticlesReq);
        $deleteArticlesStmt->execute([':category_id' => $category_id]);

        // Ensuite supprimer la catégorie
        $deleteCategoryReq = "DELETE FROM categories WHERE category_id = :category_id";
        $deleteCategoryStmt = $bdd->prepare($deleteCategoryReq);
        $deleteCategoryStmt->execute([':category_id' => $category_id]);

        // Valider la transaction
        $bdd->commit();

        $_SESSION['success_message'] = "La catégorie et tous ses articles ont été supprimés avec succès !";
    } catch (PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $bdd->rollBack();
        $_SESSION['error_message'] = "Erreur lors de la suppression de la catégorie et de ses articles";
    }

    header("Location: categories.php");
    exit();
}

function showCategories($bdd)
{
    $req = $bdd->query("
        SELECT 
            c.category_id,
            c.category_name,
            COUNT(a.article_id) as article_count,
            COUNT(CASE WHEN a.article_status = 'publie' THEN 1 END) as published_count
        FROM categories c
        LEFT JOIN articles a ON c.category_id = a.category_id
        GROUP BY c.category_id, c.category_name
        ORDER BY c.category_name ASC
    ");

    while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($data['category_name']) . "</td>";
        echo "<td>" . $data['article_count'] . "</td>";
        echo "<td>";
        echo "<a href='?modify=" . $data['category_id'] . "' class='btn btn-primary btn-sm'><i class='fas fa-edit'></i></a> ";
        echo "<a href='?delete=" . $data['category_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Attention ! La suppression de cette catégorie entraînera également la suppression de tous les articles associés. Êtes-vous sûr de vouloir continuer ?\");'><i class='fas fa-trash'></i></a>";
        echo "</td>";
        echo "</tr>";
    }
}

// =============================================
// SECTION 3: GESTION DES ARTICLES
// =============================================

function createNewArticle($bdd, $imgToken)
{
    if (isset($_POST["addArticle"])) {

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
                $path = "../img/images_articles/";
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
                $req->bindValue(":article_status", "publie");
                $req->bindValue(":article_img", $image_article);
                $req->bindValue(":category_id", $category_id);
                $req->bindValue(":author_id", $author_id);

                if ($req->execute()) {
                    $_SESSION['success_message'] = "L'article a été publié avec succès !";
                    header("Location: all_articles.php");
                    exit();
                } else {
                    return "Erreur lors de la création de l'article";
                }
            } else {
                return "Catégorie non trouvée";
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

            // Si la valeur est un JSON, essayer de le décoder
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
                    // Si le décodage échoue, essayer de nettoyer la chaîne
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
    try {
        $getArticlesReq = "SELECT * FROM articles ORDER BY article_id ASC";
        $getArticlesRes = $bdd->query($getArticlesReq);
        while ($line = $getArticlesRes->fetch(PDO::FETCH_ASSOC)) {
            $article_id = $line["article_id"];
            $authorId = $line["author_id"];
            $categoryId = $line["category_id"];

            $getAuhorReq = "SELECT * FROM users WHERE user_id=$authorId";
            $getAuhorRes = $bdd->query($getAuhorReq);
            $author = $getAuhorRes->fetch(PDO::FETCH_ASSOC);
            $articleAuthor = $author ? $author["username"] : "Auteur supprimé";

            $getCategoryReq = "SELECT * FROM categories WHERE category_id=$categoryId";
            $getCategoryRes = $bdd->query($getCategoryReq);
            $category = $getCategoryRes->fetch(PDO::FETCH_ASSOC);
            $articleCategory = $category["category_name"];

            $articleTitle = $line["article_title"];
            $articleDate = $line["article_date"];
            $articleTags = $line["article_tags"];
            $articleStatus = formatArticleStatus($line["article_status"], $line["is_modified"]);
            $articleImg = $line["article_img"];
            $isModified = $line["is_modified"] ? '<span class="badge badge-warning">Modifié</span>' : '';

            $dateFR = date('d-m-Y', strtotime($articleDate));

            // Nettoyer et afficher les tags
            $cleanTagsJson = cleanTags($articleTags);
            $tagsArray = json_decode($cleanTagsJson, true);
            $tagsDisplay = '';

            if (is_array($tagsArray)) {
                $tagsDisplay = implode(', ', array_map(function ($tag) {
                    return $tag['value'];
                }, $tagsArray));
            }

            echo "<tr>
                <td>$articleTitle</td>
                <td>$articleAuthor</td>
                <td>$dateFR</td>
                <td>$articleCategory</td>
                <td>$tagsDisplay</td>
                <td>$articleStatus</td>
                <td>$isModified</td>
                <td><img src='../img/images_articles/$articleImg' width=40 alt='Image article' /></td>
                <td>
                    <a href='update_article.php?modify=$article_id' class='btn btn-primary btn-sm mr-1'><i class='fas fa-edit'></i></a>
                    <a href='all_articles.php?delete=$article_id' class='btn btn-danger btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cet article ?\")'><i class='fas fa-trash'></i></a>
                </td>
            </tr>";

            // Mettre à jour les tags dans la base de données
            $updateTagsReq = $bdd->prepare("UPDATE articles SET article_tags = :tags WHERE article_id = :id");
            $updateTagsReq->execute([
                ':tags' => $cleanTagsJson,
                ':id' => $article_id
            ]);
        }
    } catch (PDOException $e) {
        echo "<tr><td colspan='9' class='text-center'><b style='color: red'>La récupération des données a échoué, veuillez rééssayer ultérieurement</b></td></tr>";
    }
}

function deleteArticle($bdd)
{
    $article_id = $_GET["delete"];
    $deleteReq = "DELETE FROM articles WHERE article_id=$article_id";
    $deleteRes = $bdd->exec($deleteReq);

    if (!$deleteRes) {
        $_SESSION['error_message'] = "Erreur lors de la suppression de l'article";
        header("Location: all_articles.php");
        exit();
    }

    $_SESSION['success_message'] = "L'article a bien été supprimé !";
    header("Location: all_articles.php");
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
                article_status = :status,
                category_id = :category_id";

        $params = [
            ':title' => $_POST["title"],
            ':content' => $_POST["content"],
            ':tags' => $tagsJson,
            ':status' => $_POST["status"],
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
                return implode("<br>", $errors);
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
                return "L'article a bien été modifié !";
            } else {
                return "Erreur lors de la modification de l'article";
            }
        } else {
            return "Catégorie non trouvée";
        }
    }
    return "";
}

// =============================================
// SECTION 4: GESTION DES UTILISATEURS
// =============================================

function deleteUser($bdd)
{
    $user_id = $_GET["delete"];
    $deleteReq = "DELETE FROM users WHERE user_id=$user_id";
    $deleteRes = $bdd->exec($deleteReq);

    if (!$deleteRes) {
        $_SESSION['error_message'] = "Erreur lors de la suppression de l'utilisateur";
        header("Location: users.php");
        exit();
    }

    $_SESSION['success_message'] = "L'utilisateur a bien été supprimé !";
    header("Location: users.php");
    exit();
}

// =============================================
// SECTION 5: GESTION DES PROFILS
// =============================================

function updateProfile($bdd, $imgToken)
{
    if (empty($_POST["firstname"]) || !preg_match("/^[\p{L}\s-]+$/u", $_POST["firstname"])) {
        return "Veuillez entrer un prénom valide (lettres, espaces et tirets uniquement)";
    } else if (empty($_POST["name"]) || !preg_match("/^[\p{L}\s-]+$/u", $_POST["name"])) {
        return "Veuillez entrer un nom valide (lettres, espaces et tirets uniquement)";
    } else if (empty($_POST["username"]) || !ctype_alnum($_POST["username"])) {
        return "Veuillez entrer un nom d'utilisateur composé de lettres et/ou de chiffres";
    } else if (empty($_POST["email"]) || !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        return "Veuillez entrer une adresse mail valide";
    }

    $usernameReq = $bdd->prepare("SELECT * FROM users WHERE username=:username");
    $usernameReq->bindValue(":username", $_POST["username"]);
    $usernameReq->execute();
    $usernameRes = $usernameReq->fetch(PDO::FETCH_ASSOC);

    if ($usernameRes !== false && $usernameRes['user_id'] != $_SESSION["user_id"]) {
        return "Utilisateur déjà existant, veuillez changer de nom d'utilisateur";
    }

    $updateReq = $bdd->prepare("UPDATE users SET user_name=:user_name, user_firstname=:user_firstname, user_email=:user_email, username=:username, user_img=:user_img WHERE user_id=:user_id AND (user_role=:user_role OR user_role='modo')");

    if (empty($_FILES["photo_profil"]["name"])) {
        $updateReq->bindValue(":user_img", $_SESSION["user_img"]);
    } else {
        if ($_FILES["photo_profil"]["size"] > 5000000) {
            return "La photo de profil ne doit pas dépasser 5 Mo";
        } else if ($_FILES["photo_profil"]["error"] !== UPLOAD_ERR_OK) {
            return "Erreur lors de l'upload de la photo (code d'erreur : " . $_FILES["photo_profil"]["error"] . ")";
        } else {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            if (in_array($_FILES["photo_profil"]["type"], $allowed_types)) {
                $path = "img/photo_profil/";
                $original_name = pathinfo($_FILES["photo_profil"]["name"], PATHINFO_FILENAME);
                $extension = pathinfo($_FILES["photo_profil"]["name"], PATHINFO_EXTENSION);
                $filename = $imgToken . "_" . $original_name . "." . $extension;

                // Suppression de l'ancienne image si elle existe et n'est pas l'image par défaut
                if (!empty($_SESSION["user_img"]) && $_SESSION["user_img"] !== "default_avatar.png") {
                    $old_image_path = $path . $_SESSION["user_img"];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }

                move_uploaded_file($_FILES["photo_profil"]["tmp_name"], $path . $filename);
                $updateReq->bindValue(":user_img", $filename);
            } else {
                return "Le format de la photo de profil doit être .jpg, .jpeg ou .png";
            }
        }
    }

    $updateReq->bindValue(":username", $_POST["username"]);
    $updateReq->bindValue(":user_name", $_POST["name"]);
    $updateReq->bindValue(":user_firstname", $_POST["firstname"]);
    $updateReq->bindValue(":user_email", $_POST["email"]);
    $updateReq->bindValue(":user_id", $_SESSION["user_id"]);
    $updateReq->bindValue(":user_role", $_SESSION["user_role"]);
    $updateRes = $updateReq->execute();

    if ($updateRes) {
        // Mettre à jour les variables de session
        $_SESSION["username"] = $_POST["username"];
        $_SESSION["user_name"] = $_POST["name"];
        $_SESSION["user_firstname"] = $_POST["firstname"];
        $_SESSION["user_email"] = $_POST["email"];
        if (isset($filename)) {
            $_SESSION["user_img"] = $filename;
        }

        // Stocker le message de succès dans la session
        $_SESSION['success_message'] = "Votre profil a bien été modifié !";
        // Rediriger vers la page de profil
        header("Location: profile.php");
        exit();
    } else {
        return "Un problème est survenu, veuillez rééssayer";
    }
}

function showUsers($bdd)
{
    try {
        // Récupérer tous les utilisateurs
        $getUsersReq = $bdd->prepare("SELECT * FROM users ORDER BY user_id ASC");
        $getUsersReq->execute();
        $users = $getUsersReq->fetchAll(PDO::FETCH_ASSOC);

        if (empty($users)) {
            echo "<tr><td colspan='8' class='text-center'>Aucun utilisateur trouvé</td></tr>";
            return;
        }

        foreach ($users as $user) {
            $user_id = $user['user_id'];
            $user_name = htmlspecialchars($user['user_name']);
            $user_firstname = htmlspecialchars($user['user_firstname']);
            $user_email = htmlspecialchars($user['user_email']);
            $username = htmlspecialchars($user['username']);
            $user_role = htmlspecialchars($user['user_role']);
            $user_img = $user['user_img'] ?? 'default_avatar.png';
            $email_validated = $user['user_email_validation'] ?? 0;

            echo "<tr>
                <td>$user_name</td>
                <td>$user_firstname</td>
                <td>$user_email</td>
                <td>$username</td>
                <td>
                    <form action='update_user_role.php' method='POST' class='d-inline'>
                        <input type='hidden' name='user_id' value='$user_id'>
                        <select name='user_role' class='form-control form-control-sm' onchange='this.form.submit()'>
                            <option value='user'" . ($user_role === 'user' ? ' selected' : '') . ">Utilisateur</option>
                            <option value='admin'" . ($user_role === 'admin' ? ' selected' : '') . ">Administrateur</option>
                            <option value='modo'" . ($user_role === 'modo' ? ' selected' : '') . ">Modérateur</option>
                        </select>
                    </form>
                </td>
                <td class='text-center'>
                    " . ($email_validated ?
                "<i class='fas fa-check-circle text-success' title='Email validé'></i>" :
                "<i class='fas fa-times-circle text-danger' title='Email non validé'></i>") . "
                </td>
                <td class='text-center'>
                    <img src='../img/photo_profil/$user_img' alt='Photo de profil' class='img-profile rounded-circle' style='width: 50px; height: 50px;'>
                </td>
                <td class='text-center'>
                    <a href='users.php?delete=$user_id' class='btn btn-danger btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cet utilisateur ?\")'>
                        <i class='fas fa-trash'></i>
                    </a>
                </td>
            </tr>";
        }
    } catch (PDOException $e) {
        echo "<tr><td colspan='8' class='text-center'>Erreur lors de la récupération des utilisateurs</td></tr>";
    }
}

// =============================================
// SECTION 6: GESTION DES INFOS DASHBOARD
// =============================================

function articlesNum($bdd)
{
    $req = "SELECT * FROM articles";
    $res = $bdd->query($req);
    $resNum = $res->rowCount();
    return $resNum;
}

function articlesPublishedNum($bdd)
{
    $req = "SELECT * FROM articles WHERE article_status = 'publie'";
    $res = $bdd->query($req);
    $resNum = $res->rowCount();
    return $resNum;
}

function articlesPendingNum($bdd)
{
    $req = "SELECT * FROM articles WHERE article_status = 'en_attente'";
    $res = $bdd->query($req);
    $resNum = $res->rowCount();
    return $resNum;
}

function usersNum($bdd)
{
    $req = "SELECT * FROM users";
    $res = $bdd->query($req);
    $resNum = $res->rowCount();
    return $resNum;
}

function usersByRoleNum($bdd, $role)
{
    $req = $bdd->prepare("SELECT * FROM users WHERE user_role = :role");
    $req->execute([':role' => $role]);
    return $req->rowCount();
}

function commentsNum($bdd)
{
    $req = "SELECT * FROM comments";
    $res = $bdd->query($req);
    $resNum = $res->rowCount();
    return $resNum;
}

function categoriesNum($bdd)
{
    $req = "SELECT * FROM categories";
    $res = $bdd->query($req);
    $resNum = $res->rowCount();
    return $resNum;
}

/**
 * Affiche la liste des commentaires dans un tableau
 * @param PDO $bdd Instance de la connexion à la base de données
 */
function showComments($bdd)
{
    try {
        $query = "SELECT c.*, a.article_title, u.username, u.user_email 
                FROM comments c 
                LEFT JOIN articles a ON c.article_id = a.article_id 
                LEFT JOIN users u ON c.user_id = u.user_id 
                ORDER BY c.comment_date DESC";
        $stmt = $bdd->prepare($query);
        $stmt->execute();
        $comments = $stmt->fetchAll();

        foreach ($comments as $comment) {
            $username = $comment['username'] ? htmlspecialchars($comment['username']) : "Auteur supprimé";
            $userEmail = $comment['user_email'] ? htmlspecialchars($comment['user_email']) : "-";

            echo "<tr>
                <td>" . htmlspecialchars($comment['article_title']) . "</td>
                <td>" . $username . "</td>
                <td>" . $userEmail . "</td>
                <td>" . htmlspecialchars(html_entity_decode($comment['comment_content'])) . "</td>
                <td>" . date('d/m/Y H:i', strtotime($comment['comment_date'])) . "</td>
                <td><button class='btn btn-danger'><a style='color: white; text-decoration: none;' href='comments.php?delete=" . $comment['comment_id'] . "' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce commentaire ?\")'>Supprimer</a></button></td>
            </tr>";
        }
    } catch (PDOException $e) {
        echo "<tr><td colspan='6' class='text-center'><b style='color: red'>La récupération des données a échoué, veuillez rééssayer ultérieurement</b></td></tr>";
    }
}

?>