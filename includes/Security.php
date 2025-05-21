<?php

class Security
{
    private static $instance = null;
    private $bdd;

    private function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public static function getInstance($bdd = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($bdd);
        }
        return self::$instance;
    }

    public function validateInput($data, $type = 'string')
    {
        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_VALIDATE_EMAIL);
            case 'int':
                return filter_var($data, FILTER_VALIDATE_INT);
            case 'url':
                return filter_var($data, FILTER_VALIDATE_URL);
            case 'string':
            default:
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }

    public function generateCSRFToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }

    public function isEmailValidated($userId)
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $req = $this->bdd->prepare("SELECT user_email_validation FROM users WHERE user_id = :user_id");
        $req->bindValue(":user_id", $userId);
        $req->execute();
        $user = $req->fetch(PDO::FETCH_ASSOC);

        return $user && $user['user_email_validation'] == 1;
    }

    public function validatePassword($password)
    {
        // Au moins 8 caractères
        if (strlen($password) < 8) {
            return false;
        }

        // Au moins une lettre majuscule
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Au moins une lettre minuscule
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Au moins un chiffre
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Au moins un caractère spécial
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
            return false;
        }

        return true;
    }

    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function sanitizeFileName($fileName)
    {
        // Supprimer les caractères spéciaux et les espaces
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);

        // Limiter la longueur du nom de fichier
        $fileName = substr($fileName, 0, 255);

        return $fileName;
    }

    public function validateImage($file)
    {
        $errors = [];

        // Vérifier si un fichier a été uploadé
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $errors[] = "Aucun fichier n'a été uploadé";
            return $errors;
        }

        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Erreur lors de l'upload du fichier";
            return $errors;
        }

        // Vérifier le type MIME
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = "Type de fichier non autorisé. Types acceptés : JPG, PNG, GIF";
        }

        // Vérifier la taille du fichier (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = "Le fichier est trop volumineux. Taille maximale : 5MB";
        }

        return $errors;
    }
}