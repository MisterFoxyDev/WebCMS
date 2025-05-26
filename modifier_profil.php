<?php
session_start();
require_once "includes/bdd.php";
require_once "includes/token.php";
?>

<?php
if (isset($_GET["modifier_compte"]) && isset($_SESSION["user_id"]) && $_GET["modifier_compte"] == $_SESSION["user_id"]) {
    $user_id = $_SESSION["user_id"];
    $req = "SELECT * FROM users WHERE user_id=$user_id";
    $res = $bdd->query($req);
    $line = $res->fetch(PDO::FETCH_ASSOC);

    $user_name = $line["user_name"];
    $user_firstname = $line["user_firstname"];
    $username = $line["username"];
    $user_img = $line["user_img"];
} else {
    header("location:login.php");
}

if (isset($_POST["modification"])) {
    if (empty($_POST["firstname"]) || !preg_match("/^[\p{L}\s-]+$/u", $_POST["firstname"])) {
        $message = "Veuillez entrer un prénom valide (lettres, espaces et tirets uniquement)";
    } else if (empty($_POST["name"]) || !preg_match("/^[\p{L}\s-]+$/u", $_POST["name"])) {
        $message = "Veuillez entrer un nom valide (lettres, espaces et tirets uniquement)";
    } else if (empty($_POST["username"]) || !ctype_alnum($_POST["username"])) {
        $message = "Veuillez entrer un nom d'utilisateur composé de lettres et/ou de chiffres";
    } else {
        $usernameReq = $bdd->prepare("SELECT * FROM users WHERE username=:username");
        $usernameReq->bindValue(":username", $_POST["username"]);
        $usernameReq->execute();
        $usernameRes = $usernameReq->fetch(PDO::FETCH_ASSOC);

        if (!empty($usernameRes) && $usernameRes['user_id'] != $user_id) {
            $message = "Utilisateur déjà existant, veuillez changer de nom d'utilisateur";
        } else {
            $updateReq = $bdd->prepare("UPDATE users SET user_name=:user_name, user_firstname=:user_firstname, username=:username, user_img=:user_img WHERE user_id=:user_id");

            if (empty($_FILES["photo_profil"]["name"])) {
                $updateReq->bindValue(":user_img", $user_img);
            } else {
                if ($_FILES["photo_profil"]["size"] > 5000000) {
                    $message = "La photo de profil ne doit pas dépasser 5 Mo";
                } else if ($_FILES["photo_profil"]["error"] !== UPLOAD_ERR_OK) {
                    $message = "Erreur lors de l'upload de la photo (code d'erreur : " . $_FILES["photo_profil"]["error"] . ")";
                } else {
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (in_array($_FILES["photo_profil"]["type"], $allowed_types)) {
                        $path = "img/photo_profil/";
                        $original_name = pathinfo($_FILES["photo_profil"]["name"], PATHINFO_FILENAME);
                        $extension = pathinfo($_FILES["photo_profil"]["name"], PATHINFO_EXTENSION);
                        $filename = $imgToken . "_" . $original_name . "." . $extension;

                        // Suppression de l'ancienne image si elle existe et n'est pas l'image par défaut
                        if (!empty($user_img) && $user_img !== "default_avatar.png") {
                            $old_image_path = $path . $user_img;
                            if (file_exists($old_image_path)) {
                                unlink($old_image_path);
                            }
                        }

                        move_uploaded_file($_FILES["photo_profil"]["tmp_name"], $path . $filename);
                        $photo_profil = $filename;
                        $updateReq->bindValue(":user_img", $filename);
                    } else {
                        $message = "Le format de la photo de profil doit être .jpg, .jpeg ou .png";
                    }
                }
            }
            if (!isset($message)) {
                $updateReq->bindValue(":username", $_POST["username"]);
                $updateReq->bindValue(":user_name", $_POST["name"]);
                $updateReq->bindValue(":user_firstname", $_POST["firstname"]);
                $updateReq->bindValue(":user_id", $user_id);
                $updateRes = $updateReq->execute();

                if ($updateRes) {
                    // Mettre à jour les variables de session
                    $_SESSION["username"] = $_POST["username"];
                    $_SESSION["user_name"] = $_POST["name"];
                    $_SESSION["user_firstname"] = $_POST["firstname"];
                    if (isset($filename)) {
                        $_SESSION["user_img"] = $filename;
                    }

                    // Stocker le message de succès dans la session
                    $_SESSION['success_message'] = "Votre profil a bien été modifié !";
                    // Rediriger vers la page de profil
                    header("location:profile.php");
                    exit();
                } else {
                    $message = "Un problème est survenu, veuillez rééssayer";
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">

<?php require_once "includes/register_header.php" ?>

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
                                    <?php
                                    if (isset($message)) {
                                        echo "<div class='alert alert-danger text-center mb-4'>$message</div>";
                                    }
                                    ?>
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
                                                <div>
                                                    <?php echo "<img width=24 class='media-object' alt='photo de profil' src='img/photo_profil/$user_img' />"; ?>
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

        <?php require_once "includes/register_footer.php" ?>

        <!-- Scripts -->
        <?php require_once "includes/scripts.php"; ?>
        <?php require_once "includes/sweet_alert.php"; ?>

        <!-- Bouton Haut de page -->

</body>

</html>