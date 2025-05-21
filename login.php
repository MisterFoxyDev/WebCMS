<?php
session_start();
require_once "includes/bdd.php";
require_once "includes/token.php";

$message = "";

// Vérification des cookies de "Se souvenir de moi"
if (!isset($_SESSION["user_id"]) && isset($_COOKIE["remember_token"])) {
    $token = $_COOKIE["remember_token"];
    $req = $bdd->prepare("SELECT * FROM users WHERE remember_token = :token");
    $req->bindValue(":token", $token);
    $req->execute();
    $user = $req->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["user_name"] = $user["user_name"];
        $_SESSION["user_firstname"] = $user["user_firstname"];
        $_SESSION["user_email"] = $user["user_email"];
        $_SESSION["user_img"] = $user["user_img"];
        $_SESSION["user_role"] = $user["user_role"];
        $_SESSION["email_validated"] = $user["user_email_validation"] == 1;

        if ($user["user_role"] === "admin" || $user["user_role"] === "modo") {
            header("location: admGestion/index.php");
        } else {
            header("location: index.php");
        }
        exit();
    }
}

if (isset($_POST["login"])) {
    if (empty($_POST["email"]) || empty($_POST["password"])) {
        $message = "Veuillez remplir tous les champs";
    } else {
        $req = $bdd->prepare("SELECT * FROM users WHERE user_email = :email");
        $req->bindValue(":email", $_POST["email"]);
        $req->execute();
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($_POST["password"], $user["user_password"])) {
            // Vérifier le statut de validation de l'email
            $emailValidated = $user["user_email_validation"] == 1;

            // Stocker les informations de session
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["user_name"] = $user["user_name"];
            $_SESSION["user_firstname"] = $user["user_firstname"];
            $_SESSION["user_email"] = $user["user_email"];
            $_SESSION["user_img"] = $user["user_img"];
            $_SESSION["user_role"] = $user["user_role"];
            $_SESSION["email_validated"] = $emailValidated;

            if (!$emailValidated) {
                $_SESSION['warning_message'] = "Votre adresse email n'a pas encore été validée. Certaines fonctionnalités seront limitées.";
            }

            if ($user["user_role"] === "admin" || $user["user_role"] === "modo") {
                header("location: admGestion/index.php");
            } else {
                header("location: index.php");
            }
            exit();
        } else {
            $message = "Email ou mot de passe incorrect";
        }
    }
}

if (isset($_COOKIE["remember_email"])) {
    $email = $_COOKIE["remember_email"];

    $prefilled_email = $email;
} else {
    $prefilled_email = "";
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
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Connexion</h3>
                                    <?php if (isset($_SESSION['success_message'])): ?>
                                        <div class="alert alert-success text-center mb-4">
                                            <?php
                                            echo $_SESSION['success_message'];
                                            unset($_SESSION['success_message']);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($message)): ?>
                                        <div class="alert alert-danger"><?php echo $message; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="email" type="email" placeholder="Email"
                                                value="<?php echo htmlspecialchars($prefilled_email); ?>" required />
                                            <label>Email</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="password" type="password"
                                                placeholder="Mot de passe" required />
                                            <label>Mot de passe</label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="remember"
                                                id="remember">
                                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                                        </div>
                                        <div class="mt-4 mb-0">
                                            <div class="d-grid">
                                                <button class="btn btn-primary btn-block" type="submit"
                                                    name="login">Connexion</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="register.php">Pas encore de compte ? Inscrivez-vous
                                            !</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <?php require_once "includes/footer.php"; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>