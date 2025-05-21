<?php
session_start();
require_once "../includes/bdd.php";
require_once "../includes/token.php";
require_once "functions.php";
?>

<?php
if (isset($_GET["modifier_compte"]) && isset($_SESSION["user_id"]) && $_GET["modifier_compte"] == $_SESSION["user_id"]) {
    $user_id = $_SESSION["user_id"];
    $req = "SELECT * FROM users WHERE user_id=$user_id AND user_role='admin' OR user_role='modo' ";
    $res = $bdd->query($req);
    $line = $res->fetch(PDO::FETCH_ASSOC);

    $user_name = $line["user_name"];
    $user_firstname = $line["user_firstname"];
    $username = $line["username"];
    $user_img = $line["user_img"];
    $user_email = $line["user_email"];
} else {
    header("location:login.php");
}

// Afficher le message de succès de la session s'il existe
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Supprimer le message après l'avoir affiché
}

if (isset($_POST["modification"])) {
    $message = updateProfile($bdd, $imgToken);
}
?>


<!DOCTYPE html>
<html lang="fr">

<?php require_once "includes/login_header.php"; ?>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Modifier le profil</h3>
                                    <!-- Page Heading -->
                                    <h1 class="h3 mb-4 text-gray-800">Modifier le profil</h1>

                                    <?php if (isset($message)) {
                                        if (
                                            $message === "Votre profil a bien été modifié !"
                                        ) {
                                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #d4edda; padding: 10px; border-radius: 5px;\">$message</b></div>";
                                        } else {
                                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">$message</b></div>";
                                        }
                                    } ?>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input required class="form-control" id="inputFirstName" type="text"
                                                        name="firstname" value="<?= $user_firstname ?>" />
                                                    <label for="inputFirstName">Prénom</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input required class="form-control" id="inputLastName" type="text"
                                                        name="name" value="<?= $user_name ?>" />
                                                    <label for="inputLastName">Nom</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input required class="form-control" id="inputUsername" type="text"
                                                        name="username" value="<?= $username ?>" />
                                                    <label for="inputUsername">Nom d'utilisateur</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input required class="form-control" id="inputUserEmail" type="text"
                                                        name="email" value="<?= $user_email ?>" />
                                                    <label for="inputUserEmail">Adresse mail</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div>
                                                    <?php echo "<img width=24 class='media-object' alt='photo de profil' src='../img/photo_profil/$user_img' />"; ?>
                                                    <label for="photo">Photo de profil</label>
                                                    <input required class="form-control" type="hidden"
                                                        name="MAX_FILE_SIZE" value="5000000" />
                                                    <input type="file" id="photo" name="photo_profil" value="" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 mb-0">
                                            <div class="d-grid"><input name="modification" type="submit"
                                                    value="Valider les changements" class="btn btn-primary btn-block">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <?php require_once "templates/partials/footer.php"; ?>

</body>

</html>