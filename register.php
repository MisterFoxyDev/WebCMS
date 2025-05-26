<?php
session_start();
require_once "includes/bdd.php";
require_once "includes/image_utils.php";

$message = "";

if (isset($_POST["register"])) {
    // Validation des champs
    if (empty($_POST["firstname"]) || !preg_match("/^[\p{L}\s-]+$/u", $_POST["firstname"])) {
        $message = "Veuillez entrer un prénom valide (lettres, espaces et tirets uniquement)";
    } else if (empty($_POST["name"]) || !preg_match("/^[\p{L}\s-]+$/u", $_POST["name"])) {
        $message = "Veuillez entrer un nom valide (lettres, espaces et tirets uniquement)";
    } else if (empty($_POST["username"]) || !ctype_alnum($_POST["username"])) {
        $message = "Veuillez entrer un nom d'utilisateur composé de lettres et/ou de chiffres";
    } else if (empty($_POST["email"]) || !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $message = "Veuillez entrer une adresse mail valide";
    } else if (empty($_POST["password"]) || strlen($_POST["password"]) < 8) {
        $message = "Le mot de passe doit contenir au moins 8 caractères";
    } else if ($_POST["password"] !== $_POST["confirm_password"]) {
        $message = "Les mots de passe ne correspondent pas";
    } else {
        // Vérification si l'utilisateur existe déjà
        $checkReq = $bdd->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR user_email = :email");
        $checkReq->bindValue(":username", $_POST["username"]);
        $checkReq->bindValue(":email", $_POST["email"]);
        $checkReq->execute();
        $exists = $checkReq->fetchColumn();

        if ($exists > 0) {
            $message = "Ce nom d'utilisateur ou cette adresse email est déjà utilisé";
        } else {
            // Traitement de la photo de profil
            if (empty($_FILES["photo_profil"]["name"])) {
                $photo_profil = "default_avatar.png";
            } else {
                $errors = validateImage($_FILES["photo_profil"]);
                if (empty($errors)) {
                    $path = "img/photo_profil/";
                    $filename = saveImage($_FILES["photo_profil"], $path, uniqid());
                    if ($filename === false) {
                        $message = "Erreur lors de l'enregistrement de la photo de profil";
                    } else {
                        $photo_profil = $filename;
                    }
                } else {
                    $message = implode("<br>", $errors);
                }
            }

            if (empty($message)) {
                // Hashage du mot de passe
                $hashed_password = password_hash($_POST["password"], PASSWORD_DEFAULT);

                // Génération du token
                require_once "includes/token.php";

                // Insertion dans la base de données
                $insertReq = $bdd->prepare("INSERT INTO users (user_name, user_firstname, username, user_email, user_password, user_img, user_role, user_token, user_email_validation) VALUES (:name, :firstname, :username, :email, :password, :photo, 'user', :token, 0)");
                $insertReq->bindValue(":name", $_POST["name"]);
                $insertReq->bindValue(":firstname", $_POST["firstname"]);
                $insertReq->bindValue(":username", $_POST["username"]);
                $insertReq->bindValue(":email", $_POST["email"]);
                $insertReq->bindValue(":password", $hashed_password);
                $insertReq->bindValue(":photo", $photo_profil);
                $insertReq->bindValue(":token", $token);

                if ($insertReq->execute()) {
                    require_once "includes/PHPMailer/sendmail.php";
                    if (isset($GLOBALS['mail_confirmation_message'])) {
                        $_SESSION['success_message'] = $GLOBALS['mail_confirmation_message'];
                    } else {
                        $_SESSION['success_message'] = "Un mail de confirmation vient d'être envoyé à votre adresse mail pour valider votre inscription !";
                    }
                    header("Location: login.php?success=register");
                    exit();
                } else {
                    $message = "Une erreur est survenue lors de l'inscription";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<?php require_once "includes/register_header.php"; ?>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container mb-4">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Créer un compte</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($message)): ?>
                                        <div class="alert alert-danger"><?php echo $message; ?></div>
                                    <?php endif; ?>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" name="firstname" type="text"
                                                        placeholder="Entrez votre prénom" required />
                                                    <label>Prénom</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input class="form-control" name="name" type="text"
                                                        placeholder="Entrez votre nom" required />
                                                    <label>Nom</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="username" type="text"
                                                placeholder="Choisissez un nom d'utilisateur" required />
                                            <label>Nom d'utilisateur</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="email" type="email"
                                                placeholder="name@example.com" required />
                                            <label>Adresse email</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" name="password" type="password"
                                                        placeholder="Créez un mot de passe" required />
                                                    <label>Mot de passe</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" name="confirm_password" type="password"
                                                        placeholder="Confirmez le mot de passe" required />
                                                    <label>Confirmez le mot de passe</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="photo_profil" class="form-label">Photo de profil
                                                (optionnel)</label>
                                            <input class="form-control" type="file" id="photo_profil"
                                                name="photo_profil" accept="image/*">
                                        </div>
                                        <div class="mt-4 mb-0">
                                            <div class="d-grid">
                                                <button class="btn btn-primary btn-block" type="submit"
                                                    name="register">Créer le compte</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="login.php">Vous avez déjà un compte ? Connectez-vous
                                            !</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <?php require_once "includes/register_footer.php"; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>