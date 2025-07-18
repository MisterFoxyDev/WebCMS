<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . "/PHPMailer.php";
require __DIR__ . "/Exception.php";
require __DIR__ . "/SMTP.php";
require_once __DIR__ . "/../loadEnv.php";

// Charger les variables d'environnement
loadEnv(__DIR__ . '/../../.env');

// Déterminer l'environnement
$env = getenv('APP_ENV') ?: 'development';

$mail = new PHPMailer(true);
$mail->isSMTP(); // Préciser que PHPMailer utilise le protocole SMTP (Simple Mail Transfer Protocol)
$mail->Host = getenv('SMTP_HOST'); // SPécifier le serveur (gmail)
$mail->SMTPAuth = true; // Activer l'authentification
$mail->Username = getenv('SMTP_USERNAME');
$mail->Password = getenv('SMTP_PASSWORD');
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->SMTPDebug = 0; // Désactive le mode debug en production

// Configuration SSL nécessaire pour le développement local
if ($env === 'development') {
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
}

$mail->setLanguage('fr');
$mail->CharSet = 'UTF-8';
$mail->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
$mail->addAddress($_POST['email']);
$mail->isHTML(true); // Activer l'envoi de mail sous forme HTML

$mail->Subject = "Réinitialisation du mot de passe";
require_once "includes/token.php";
$mail->Body = 'Bonjour ! Afin de réinitialiser votre mot de passe, merci de cliquer sur le lien suivant : <a href="' . getenv('APP_URL') . '/new_password.php?token=' . $token . '&email=' . $_POST['email'] . '">Réinitialiser</a>';

if (!$mail->send()) {
    $GLOBALS['mail_confirmation_message'] = "Un problème est survenu, veuillez rééssayer";
    echo "Erreur : " . $mail->ErrorInfo;
} else {
    $GLOBALS['mail_confirmation_message'] = "Un mail vient d'être envoyé à votre adresse mail pour réinitialiser votre mot de passe !";
}