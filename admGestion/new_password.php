<?php require_once "includes/login_header.php"; ?>

<?php
// Récupérer les paramètres depuis GET ou POST
$email = isset($_GET["email"]) ? $_GET["email"] : (isset($_POST["email"]) ? $_POST["email"] : "");
$token = isset($_GET["token"]) ? $_GET["token"] : (isset($_POST["token"]) ? $_POST["token"] : "");

if (!empty($email) && !empty($token)) {
    require_once "../includes/bdd.php";
    $req = $bdd->prepare("SELECT * FROM users WHERE user_email=:email AND user_token=:token AND user_role=:user_role");
    $req->bindValue(":email", $email);
    $req->bindValue(":token", $token);
    $req->bindValue(":user_role", "admin");
    $req->execute();
    $num = $req->rowCount();

    if ($num != 1) {
        header("location:login.php");
        exit;
    } else {
        if (isset($_POST["validate"])) {
            if (empty($_POST["password"]) || empty($_POST["password_confirm"])) {
                $message = "Veuillez renseigner les champs obligatoires";
            } else if ($_POST["password"] !== $_POST["password_confirm"]) {
                $message = "Les mots de passe ne correspondent pas";
            } else {
                $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                $update = $bdd->prepare("UPDATE users SET user_password=:password, user_token='Email validé' WHERE user_email=:email AND user_token=:token AND user_role=:user_role");
                $update->bindValue(":email", $email);
                $update->bindValue(":token", $token);
                $update->bindValue(":password", $password);
                $update->bindValue(":user_role", "admin");

                if ($update->execute()) {
                    echo "<script type=\"text/javascript\">
                        alert('Votre mot de passe a bien été modifié !');
                        window.location.href = 'admin/login.php';
                    </script>";
                    exit;
                } else {
                    $message = "Une erreur est survenue lors de la mise à jour du mot de passe.";
                }
            }
        }
    }
} else {
    header("location:password.php");
    exit;
}

?>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Réinitialisation du mot de passe</h3>
                                    <?php if (isset($message))
                                        echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">$message</b></div>";
                                    if (isset($GLOBALS['mail_confirmation_message']))
                                        echo $GLOBALS['mail_confirmation_message'];
                                    ?>
                                </div>
                                <div class="card-body">
                                    <div class="small mb-3 text-muted">Veuillez choisir un nouveau mot de passe</div>
                                    <form
                                        action="new_password.php?token=<?php echo htmlspecialchars($token); ?>&email=<?php echo htmlspecialchars($email); ?>"
                                        method="post">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input required class="form-control" id="inputPassword"
                                                        type="password" name="password" />
                                                    <label for="inputPassword">Mot de passe</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input required class="form-control" id="inputPasswordConfirm"
                                                        type="password" name="password_confirm" />
                                                    <label for="inputPasswordConfirm">Confirmez le mot de passe</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class="small" href="login.php">Retour à la page de connexion</a>
                                            <input type="submit" name="validate" class="btn btn-primary"
                                                value="Valider">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        </main>
    </div>

    <?php require_once "includes/footer.php"; ?>