<?php

function validateImage($file, $maxSize = 5000000)
{
    $errors = [];

    // Vérification de la taille
    if ($file["size"] > $maxSize) {
        $errors[] = "L'image ne doit pas dépasser " . ($maxSize / 1000000) . " Mo";
    }

    // Vérification des erreurs d'upload
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Erreur lors de l'upload de l'image (code d'erreur : " . $file["error"] . ")";
        return $errors;
    }

    // Types MIME autorisés
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    // Extensions autorisées
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

    // Vérification du type MIME
    if (!in_array($file["type"], $allowed_types)) {
        $errors[] = "Le type de fichier n'est pas autorisé";
    }

    // Vérification de l'extension
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        $errors[] = "L'extension du fichier n'est pas autorisée";
    }

    return $errors;
}

function saveImage($file, $path, $token)
{
    $filename = $token . "_" . basename($file["name"]);
    $target_path = $path . $filename;

    // Vérification si le répertoire existe
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        return $filename;
    }

    return false;
}