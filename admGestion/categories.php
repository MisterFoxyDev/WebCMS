<?php
session_start();

require_once "includes/admin_header.php";
require_once "includes/bdd.php";
require_once "functions.php";

// Gestion des actions CRUD
if (isset($_GET["delete"])) {
    if (deleteCategory($bdd)) {
        $message = "La catégorie a bien été supprimée !";
    } else {
        $message = "Impossible de supprimer la catégorie car elle contient des articles.";
    }
}

if (isset($_POST["addCategory"])) {
    if (createNewCategory($bdd)) {
        $message = "La catégorie a bien été ajoutée !";
    } else {
        $message = "Erreur lors de l'ajout de la catégorie.";
    }
}

if (isset($_POST["updateCategory"])) {
    if (updateCategory($bdd)) {
        $message = "La catégorie a bien été modifiée !";
    } else {
        $message = "Erreur lors de la modification de la catégorie.";
    }
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
                    <h1 class="h3 mb-4 text-gray-800">Catégories</h1>

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
                        if (
                            $message === "La catégorie a bien été ajoutée !" ||
                            $message === "La catégorie a bien été modifiée !" ||
                            $message === "La catégorie a bien été supprimée !" ||
                            $message === "La catégorie et tous ses articles ont été supprimés avec succès !"
                        ) {
                            echo "<div class='alert alert-success'>$message</div>";
                        } else {
                            echo "<div class='alert alert-danger'>$message</div>";
                        }
                    } ?>
                    <br>

                    <!-- Formulaire d'ajout -->
                    <?php if (!isset($_GET["modify"])): ?>
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-5">
                                    <div class="card shadow-lg border-0 rounded-lg mt-4">
                                        <div class="card-header">
                                            <h3 class="text-center font-weight-light my-4">Nouvelle catégorie</h3>
                                        </div>
                                        <div class="card-body">
                                            <form action="" method="post">
                                                <div class="form-floating mb-3">
                                                    <input class="form-control" id="inputCategoryName" type="text"
                                                        name="categoryName" required />
                                                    <label for="inputCategoryName">Nom de la catégorie</label>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                    <input type="submit" name="addCategory" class="btn btn-primary"
                                                        value="Ajouter">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Formulaire de modification de catégorie
                    if (isset($_GET["modify"])) {
                        $category_id_modif = $_GET["modify"];
                        $updateReq1 = "SELECT * FROM categories WHERE category_id=$category_id_modif";
                        $updateRes1 = $bdd->query($updateReq1);
                        $data = $updateRes1->fetch(PDO::FETCH_ASSOC);
                        $category_name_modif = $data["category_name"];
                        $category_id_modif = $data["category_id"];
                        ?>
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-5">
                                    <div class="card shadow-lg border-0 rounded-lg">
                                        <div class="card-header">
                                            <h3 class="text-center font-weight-light my-4">Modifier catégorie</h3>
                                        </div>
                                        <div class="card-body">
                                            <form action="" method="post">
                                                <div class="form-floating mb-3">
                                                    <input class="form-control" id="inputCategoryName" type="text"
                                                        name="category_name_modif"
                                                        value="<?php echo htmlspecialchars($category_name_modif); ?>"
                                                        required />
                                                    <label for="inputCategoryName">Nom de la catégorie</label>
                                                    <input type="hidden" name="category_id_modif"
                                                        value="<?php echo htmlspecialchars($category_id_modif); ?>">
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                    <input type="submit" name="updateCategory" class="btn btn-primary"
                                                        value="Modifier">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <?php
                    }
                    ?>

                    <!-- Table des catégories -->
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Catégories</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Nom de la catégorie</th>
                                                    <th>Nombre d'articles</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php showCategories($bdd); ?>
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

            <?php require_once "includes/admin_footer.php"; ?>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php require_once "includes/admin_logout_modal.php"; ?>
    <?php require_once "includes/admin_scripts.php"; ?>

</body>

</html>