<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("location:login.php");
    exit();
}

require_once "includes/header.php";
require_once "includes/bdd.php";
require_once "includes/token.php";
require_once "includes/image_utils.php";
require_once "functions.php";

if (isset($_POST["updateArticle"])) {
    updateArticle($bdd, $imgToken);
}

if (empty($_GET["modify"]) || !is_numeric($_GET["modify"])) {
    $_SESSION['error_message'] = "ID d'article invalide";
    header("location:articles.php");
    exit();
}

$article_id = (int) $_GET["modify"];

$req = "SELECT * FROM articles WHERE article_id = :article_id";
$stmt = $bdd->prepare($req);
$stmt->execute(['article_id' => $article_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    $_SESSION['error_message'] = "L'article demandé n'existe pas";
    header("location:articles.php");
    exit();
}

$categoryId = $data["category_id"];
$authorId = $data["author_id"];

$getAuthorReq = "SELECT * FROM users WHERE user_id = :user_id";
$getAuthorStmt = $bdd->prepare($getAuthorReq);
$getAuthorStmt->execute(['user_id' => $authorId]);
$author = $getAuthorStmt->fetch(PDO::FETCH_ASSOC);
$articleAuthor = $author["username"];

$getCategoryReq = "SELECT * FROM categories WHERE category_id = :category_id";
$getCategoryStmt = $bdd->prepare($getCategoryReq);
$getCategoryStmt->execute(['category_id' => $categoryId]);
$category = $getCategoryStmt->fetch(PDO::FETCH_ASSOC);
$articleCategory = $category["category_name"];

$title = $data["article_title"];
$tags = $data["article_tags"];
$content = $data["article_content"];
$status = $data["article_status"];
$img = $data["article_img"];
$author = $articleAuthor;
$category = $articleCategory;

if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Modifier l'article</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">

    <!-- Tagify CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
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
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10">
                                <div class="card shadow-lg border-0 rounded-lg mt-4">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Modifier l'article</h3>
                                    </div>
                                    <div class="card-body">
                                        <form action="" method="post" enctype="multipart/form-data"
                                            id="updateArticleForm">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3 mb-md-0">
                                                        <label for="inputTitle">Titre</label>
                                                        <input required class="form-control" id="inputTitle" type="text"
                                                            name="title" value="<?= htmlspecialchars($title) ?>"
                                                            minlength="3" maxlength="255" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="inputTags">Mots-clés</label>
                                                        <?php
                                                        $tagsArray = json_decode($tags, true);
                                                        $tagsString = '';
                                                        if ($tagsArray) {
                                                            $tagsValues = array_map(function ($tag) {
                                                                return $tag['value'];
                                                            }, $tagsArray);
                                                            $tagsString = implode(', ', $tagsValues);
                                                        }
                                                        ?>
                                                        <input required class="form-control" id="inputTags" type="text"
                                                            name="tags" value="<?= $tagsString ?>" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="summernote">Contenu de l'article</label>
                                                <textarea required class="form-control" id="summernote" name="content"
                                                    rows="5"><?php echo htmlspecialchars($content) ?></textarea>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3 mb-md-0">
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
                                                                    $selected = ($categoryName == $articleCategory) ? 'selected' : '';
                                                                    echo "<option value='$categoryName' $selected>$categoryName</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <?php if (!empty($img)): ?>
                                                            <div class="mb-3">
                                                                <label>Image actuelle :</label>
                                                                <div>
                                                                    <img src="img/images_articles/<?= htmlspecialchars($img) ?>"
                                                                        alt="Image de l'article"
                                                                        style="max-width: 80px; max-height: 80px;"
                                                                        class="img-thumbnail">
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <label for="articleImage">Nouvelle image</label>
                                                        <input class="form-control" type="file" id="articleImage"
                                                            name="articleImage"
                                                            accept="image/jpeg,image/jpg,image/png,image/webp" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4 mb-0">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <input type="reset" value="Annuler les modifications"
                                                            class="btn btn-danger me-2">
                                                        <input type="button"
                                                            value="Annuler les modifications du contenu"
                                                            class="btn btn-warning" onclick="resetSummernote()">
                                                    </div>
                                                    <input name="updateArticle" type="submit" value="Modifier l'article"
                                                        class="btn btn-primary">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Scripts -->
            <?php require_once "includes/scripts.php"; ?>
            <!-- Summernote JS -->
            <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
            <!-- Tagify JS -->
            <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
            <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
            <script>
                $(document).ready(function () {
                    // Initialisation de Summernote
                    $('#summernote').summernote({
                        height: 300,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'underline', 'clear']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        lang: 'fr-FR'
                    });

                    // Initialisation de Tagify
                    var input = document.querySelector('input[name="tags"]');
                    if (input) {
                        new Tagify(input, {
                            delimiters: ",| ", // séparateurs : virgule ou espace
                            maxTags: 10,
                            placeholder: "Ajoutez des mots-clés...",
                            dropdown: {
                                enabled: 0 // désactive l'autocomplétion
                            },
                            whitelist: [], // liste blanche vide pour accepter tous les tags
                            transformTag: function (tagData) {
                                tagData.value = tagData.value.toLowerCase().trim();
                            }
                        });
                    }
                });

                // Fonction pour réinitialiser Summernote à son état initial
                function resetSummernote() {
                    if (confirm('Êtes-vous sûr de vouloir remettre le contenu initial ?')) {
                        $('#summernote').summernote('reset');
                    }
                }
            </script>

            <!-- Logout Modal -->
            <?php require_once "includes/logout_modal.php"; ?>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <?php require_once "includes/footer.php"; ?>

</body>

</html>