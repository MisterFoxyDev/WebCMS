<?php
session_start();

// Vérification explicite de la connexion
if (isset($_SESSION['user_id']) || isset($_SESSION['logged_in'])) {
    // Nettoyage de la session
    $_SESSION = array();

    // Destruction du cookie de session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destruction de la session
    session_destroy();

    // Redirection avec message de succès
    header("Location: ../index.php?logout=success");
    exit();
} else {
    // Redirection avec message d'erreur si non connecté
    header("Location: index.php?error=not_logged_in");
    exit();
}