<?php
session_start();
require_once "includes/bdd.php";

if (isset($_GET["email"]) && !empty($_GET["email"]) && isset($_GET["token"]) && !empty($_GET["token"])) {
    $email = $_GET["email"];
    $token = $_GET["token"];

    $req = $bdd->prepare("SELECT * FROM users WHERE user_email = :email AND user_token = :token");
    $req->bindValue(":email", $email);
    $req->bindValue(":token", $token);
    $req->execute();
    $user = $req->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Mise à jour du statut de validation
        $update = $bdd->prepare("UPDATE users SET user_email_validation = 1 WHERE user_email = :email");
        $update->bindValue(":email", $email);
        $updateRes = $update->execute();

        if ($updateRes) {
            // Mettre à jour la session si l'utilisateur est connecté
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['user_id']) {
                $_SESSION['email_validated'] = 1;
            }

            echo "<script>
                alert('Votre adresse mail est bien confirmée !');
                window.location.href = 'login.php';
            </script>";
        } else {
            echo "<script>
                alert('Une erreur est survenue lors de la validation de votre adresse mail.');
                window.location.href = 'index.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Informations de validation invalides.');
            window.location.href = 'index.php';
        </script>";
    }
} else {
    echo "<script>
        alert('Paramètres de validation manquants.');
        window.location.href = 'index.php';
    </script>";
}