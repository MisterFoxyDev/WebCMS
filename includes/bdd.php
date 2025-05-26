<?php
require_once __DIR__ . '/loadEnv.php';

// Charger les variables d'environnement
loadEnv(__DIR__ . '/../.env');

// Déterminer l'environnement
$env = getenv('APP_ENV') ?: 'development';

// Configuration de la base de données selon l'environnement
if ($env === 'development') {
    // Configuration développement
    $dbHost = getenv('DB_HOST_DEV');
    $dbUser = getenv('DB_USER_DEV');
    $dbPassword = getenv('DB_PASSWORD_DEV');
    $dbName = getenv('DB_NAME_DEV');
} else {
    // Configuration production
    $dbHost = getenv('DB_HOST_PROD');
    $dbUser = getenv('DB_USER_PROD');
    $dbPassword = getenv('DB_PASSWORD_PROD');
    $dbName = getenv('DB_NAME_PROD');
}

$dsn = "mysql:dbname=" . $dbName . ";host=" . $dbHost;

try {
    $bdd = new PDO($dsn, $dbUser, $dbPassword);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}