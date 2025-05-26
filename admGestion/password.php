<?php require_once "includes/login_header.php"; ?>

<?php
if (isset($_POST["reset"])) {
    if (empty($_POST["email"])) {
        $message = "Veuillez entrer votre adresse mail";
    } else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $message = "Veuillez entrer une adresse valide";
    } else {
        require_once "../includes/bdd.php";
        $req = $bdd->prepare("SELECT * FROM users WHERE user_email=:email AND user_role=:user_role");
        $req->bindValue(":email", $_POST["email"]);
        $req->bindValue(":user_role", "admin");
        $req->execute();
        $res = $req->fetch();
        $num = $req->rowCount();

        if ($num == 0) {
            $message = "L'adresse saisie ne correspond à aucune adresse existante";
        } else {
            if ($res["user_email_validation"] != 1) {
                require_once "../includes/token.php";
                $update = $bdd->prepare("UPDATE users SET user_token=:token WHERE user_email=:email AND user_role=:user_role");
                $update->bindValue(":token", $token);
                $update->bindValue(":email", $_POST["email"]);
                $update->bindValue(":user_role", "admin");
                $update->execute();
                require_once "../includes/PHPMailer/sendmail.php";
            } else {
                require_once "../includes/token.php";
                $update = $bdd->prepare("UPDATE users SET user_token=:token WHERE user_email=:email AND user_role=:user_role");
                $update->bindValue(":token", $token);
                $update->bindValue(":email", $_POST["email"]);
                $update->bindValue(":user_role", "admin");
                $update->execute();

                require_once "../includes/PHPMailer/sendmail_resetPassword.php";
            }
        }
    }
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
                                    <!-- Page Heading -->
                                    <h1 class="h3 mb-4 text-gray-800">Mot de passe oublié</h1>

                                    <?php if (isset($message)) {
                                        if (
                                            $message === "Un mail de réinitialisation vient d'être envoyé à votre adresse mail !"
                                        ) {
                                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #d4edda; padding: 10px; border-radius: 5px;\">$message</b></div>";
                                        } else {
                                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">$message</b></div>";
                                        }
                                    } ?>
                                </div>
                                <div class="card-body">
                                    <div class="small mb-3 text-muted">Entrez votre adresse mail pour recevoir un lien
                                        permettant de réinitialiser votre mot de passe</div>
                                    <form action="password.php" method="post">
                                        <div class="form-floating mb-3">
                                            <input required class="form-control" id="inputEmail" type="email"
                                                name="email" />
                                            <label for="inputEmail">Votre adresse mail</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class="small" href="login.php">Retour à la page de connexion</a>
                                            <input type="submit" name="reset" class="btn btn-primary"
                                                value="Réinitialiser">
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