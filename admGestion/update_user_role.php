<?php
session_start();
require_once "includes/bdd.php";
require_once "functions.php";

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé";
    header("Location: users.php");
    exit();
}

// Vérifier si les données POST sont présentes
if (!isset($_POST['user_id']) || !isset($_POST['user_role'])) {
    $_SESSION['error_message'] = "Données manquantes";
    header("Location: users.php");
    exit();
}

$user_id = $_POST['user_id'];
$user_role = $_POST['user_role'];

// Vérifier que le rôle est valide
if (!in_array($user_role, ['user', 'admin', 'modo'])) {
    $_SESSION['error_message'] = "Rôle invalide";
    header("Location: users.php");
    exit();
}

try {
    // Mettre à jour le rôle de l'utilisateur
    $updateReq = $bdd->prepare("UPDATE users SET user_role = :user_role WHERE user_id = :user_id");
    $updateReq->execute([
        ':user_role' => $user_role,
        ':user_id' => $user_id
    ]);

    if ($updateReq->rowCount() > 0) {
        $_SESSION['success_message'] = "Le rôle de l'utilisateur a été mis à jour avec succès";
    } else {
        $_SESSION['error_message'] = "Aucune modification n'a été effectuée";
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la mise à jour du rôle";
}

header("Location: users.php");
exit();