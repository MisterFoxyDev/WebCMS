<?php
require_once __DIR__ . '/loadEnv.php';

// Charger les variables d'environnement
loadEnv(__DIR__ . '/../.env');

$dsn = "mysql:dbname=" . getenv('DB_NAME') . ";host=" . getenv('DB_HOST');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

try {
    $bdd = new PDO($dsn, $user, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}