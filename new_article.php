<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("location:login.php");
    exit();
}

// Vérification de la validation de l'email
if (!isset($_SESSION["email_validated"]) || $_SESSION["email_validated"] != 1) {
    $_SESSION['error_message'] = "Vous devez valider votre adresse email pour pouvoir créer un article.";
    header("location:index.php");
    exit();
}

require_once "includes/header.php";
require_once "includes/bdd.php";
require_once "includes/token.php";
require_once "functions.php";
require_once "includes/image_utils.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = createNewArticle($bdd, $imgToken);
}

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

    <title>Nouvel article</title>

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
                                        <h3 class="text-center font-weight-light my-4">Nouvel article</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"
                                            enctype="multipart/form-data">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3 mb-md-0">
                                                        <label for="inputTitle">Titre</label>
                                                        <input required class="form-control" id="inputTitle" type="text"
                                                            name="title" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="inputTags">Mots-clés</label>
                                                        <input class="form-control" id="inputTags" type="text"
                                                            name="tags" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="summernote">Contenu de l'article</label>
                                                <textarea required class="form-control" id="summernote" name="content"
                                                    rows="5"></textarea>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3 mb-md-0">
                                                        <label for="inputCategories">Catégorie</label>
                                                        <select required class="form-control" id="inputCategories"
                                                            name="category">
                                                            <option value="" disabled selected>Choisir une catégorie
                                                            </option>
                                                            <?php
                                                            $getCategoriesReq = "SELECT * FROM categories ORDER BY category_id ASC";
                                                            $getCategoriesRes = $bdd->query($getCategoriesReq);
                                                            if (!$getCategoriesRes) {
                                                                echo "Erreur";
                                                            } else {
                                                                while ($line = $getCategoriesRes->fetch(PDO::FETCH_ASSOC)) {
                                                                    $categoryName = $line["category_name"];
                                                                    echo "<option value='$categoryName'>$categoryName</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="articleImage">Image (optionnel)</label>
                                                        <input required class="form-control" type="hidden"
                                                            name="MAX_FILE_SIZE" value="5000000" />
                                                        <input type="file" class="form-control" id="image"
                                                            name="articleImage" value="" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4 mb-0">
                                                <div class="d-grid"><input name="addArticle" type="submit"
                                                        value="Soumettre l'article" class="btn btn-primary btn-block">
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