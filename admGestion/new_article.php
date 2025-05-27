<?php
session_start();

require_once "includes/admin_header.php";
require_once "../includes/bdd.php";
require_once "../includes/token.php";
require_once "../includes/image_utils.php";
require_once "functions.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = createNewArticle($bdd, $imgToken);
}

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
                    <h1 class="h3 mb-4 text-gray-800">Articles</h1>

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
                                                    <div class="form-floating mb-3 mb-md-0">
                                                        <label for="inputTitle">Titre</label>
                                                        <input required class="form-control" id="inputTitle" type="text"
                                                            name="title" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <label for="inputTags">Mots-clés</label>
                                                        <input class="form-control" id="inputTags" type="text"
                                                            name="tags" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <label for="summernote">Contenu de l'article</label>
                                                <textarea required class="form-control" id="summernote" name="content"
                                                    rows="5"></textarea>
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
                                                                    echo "<option value='$categoryName'>$categoryName</option>";
                                                                }
                                                            }

                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div>
                                                        <label for="articleImage">Image (optionnel)</label>
                                                        <input required class="form-control" type="hidden"
                                                            name="MAX_FILE_SIZE" value="5000000" />
                                                        <input type="file" id="image" name="articleImage" value="" />
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

            <?php require_once "includes/admin_footer.php"; ?>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!--       Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php require_once "includes/admin_logout_modal.php"; ?>
    <?php require_once "includes/admin_scripts.php"; ?>

</body>

</html>