<?php

require_once "../includes/bdd.php";

if (isset($_GET["email"]) && !empty($_GET["email"]) && isset($_GET["token"]) && !empty($_GET["token"])) {
    $email = $_GET["email"];
    $token = $_GET["token"];

    $req = $bdd->prepare("SELECT * FROM users WHERE user_email=:email AND user_token=:token");
    $req->bindValue(":email", $email);
    $req->bindValue(":token", $token);
    $req->execute();
    $num = $req->rowCount();
    if ($num == 1) {
        $update = $bdd->prepare("UPDATE users SET user_email_validation =:validation, user_token=:token WHERE user_email=:email");
        $update->bindValue(":validation", 1);
        $update->bindValue(":token", "Email validé");
        $update->bindValue(":email", $email);
        $updateRes = $update->execute();

        if ($updateRes) {
            echo "<script type=\"text/javascript\">alert('Votre adresse mail est bien confirmée !');
document.location.href='login.php';
            </script>";
        }
    }
}