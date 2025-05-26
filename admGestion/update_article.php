<?php
session_start();

require_once "includes/admin_header.php";
require_once "includes/bdd.php";
require_once "../includes/token.php";
require_once "../includes/image_utils.php";
require_once "functions.php";

$message = '';

if (empty($_GET["modify"]) || !is_numeric($_GET["modify"])) {
    $_SESSION['error_message'] = "ID d'article invalide";
    header("location:all_articles.php");
    exit();
}

$article_id = (int) $_GET["modify"];

$req = "SELECT a.*, c.category_name, u.username 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.category_id 
        LEFT JOIN users u ON a.author_id = u.user_id 
        WHERE a.article_id = :article_id";
$stmt = $bdd->prepare($req);
$stmt->execute(['article_id' => $article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    $_SESSION['error_message'] = "L'article demandé n'existe pas";
    header("location:all_articles.php");
    exit();
}

if (isset($_POST["updateArticle"])) {
    $result = updateArticle($bdd, $imgToken);
    if (strpos($result, "bien été") !== false) {
        $_SESSION['success_message'] = $result;
        header("Location: all_articles.php");
        exit();
    } else {
        $message = $result;
    }
}

if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
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
                    <h1 class="h3 mb-4 text-gray-800">Modifier l'article</h1>

                    <?php if (isset($message)) {
                        if (strpos($message, "succès") !== false || strpos($message, "bien été") !== false) {
                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #d4edda; padding: 10px; border-radius: 5px;\">$message</b></div>";
                        } else {
                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">$message</b></div>";
                        }
                    } ?>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10">
                                <div class="card shadow-lg border-0 rounded-lg mt-4">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Modifier l'article</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="post"
                                            action="<?php echo $_SERVER['PHP_SELF']; ?>?modify=<?php echo $article_id; ?>"
                                            enctype="multipart/form-data">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0">
                                                        <label for="inputTitle">Titre</label>
                                                        <input required class="form-control" id="inputTitle" type="text"
                                                            name="title"
                                                            value="<?php echo htmlspecialchars($article["article_title"]); ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <label for="inputTags">Mots-clés</label>
                                                        <?php
                                                        $tagsArray = json_decode($article["article_tags"], true);
                                                        $tagsString = '';
                                                        if ($tagsArray) {
                                                            $tagsValues = array_map(function ($tag) {
                                                                return $tag['value'];
                                                            }, $tagsArray);
                                                            $tagsString = implode(', ', $tagsValues);
                                                        }
                                                        ?>
                                                        <input class="form-control" id="inputTags" type="text"
                                                            name="tags"
                                                            value="<?php echo htmlspecialchars($tagsString); ?>" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <label for="summernote">Contenu de l'article</label>
                                                <textarea required class="form-control" id="summernote" name="content"
                                                    rows="5"><?php echo htmlspecialchars($article["article_content"]); ?></textarea>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0">
                                                        <label for="inputCategories">Catégorie</label>
                                                        <select required class="form-control" id="inputCategories"
                                                            name="category">
                                                            <option disabled value="">Choisir une catégorie</option>
                                                            <?php
                                                            $getCategoriesReq = "SELECT * FROM categories ORDER BY category_id ASC";
                                                            $getCategoriesRes = $bdd->query($getCategoriesReq);
                                                            if (!$getCategoriesRes) {
                                                                echo "Erreur";
                                                            } else {
                                                                while ($line = $getCategoriesRes->fetch(PDO::FETCH_ASSOC)) {
                                                                    $categoryName = $line["category_name"];
                                                                    $selected = ($categoryName === $article["category_name"]) ? "selected" : "";
                                                                    echo "<option value='$categoryName' $selected>$categoryName</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0">
                                                        <label for="inputStatus">Statut</label>
                                                        <select required class="form-control" id="inputStatus"
                                                            name="status">
                                                            <option value="publie" <?php echo ($article["article_status"] === "publie") ? "selected" : ""; ?>>Publié</option>
                                                            <option value="en_attente" <?php echo ($article["article_status"] === "en_attente") ? "selected" : ""; ?>>En attente</option>
                                                            <option value="brouillon" <?php echo ($article["article_status"] === "brouillon") ? "selected" : ""; ?>>Brouillon</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div>
                                                        <?php if (!empty($article["article_img"])): ?>
                                                            <div class="mb-3">
                                                                <label>Image actuelle :</label>
                                                                <div>
                                                                    <img src="../img/images_articles/<?php echo htmlspecialchars($article["article_img"]); ?>"
                                                                        alt="Image de l'article"
                                                                        style="max-width: 100px; max-height: 100px;"
                                                                        class="img-thumbnail">
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <label for="articleImage">Nouvelle image (optionnel)</label>
                                                        <input class="form-control" type="hidden" name="MAX_FILE_SIZE"
                                                            value="5000000" />
                                                        <input type="file" id="image" name="articleImage" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4 mb-0">
                                                <div class="d-grid">
                                                    <input type="hidden" name="article_id"
                                                        value="<?php echo $article_id; ?>">
                                                    <input name="updateArticle" type="submit" value="Modifier l'article"
                                                        class="btn btn-primary btn-block">
                                                </div>
                                            </div>
                                        </form>
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