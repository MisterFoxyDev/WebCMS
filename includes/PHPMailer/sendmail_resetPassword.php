<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . "/PHPMailer.php";
require __DIR__ . "/Exception.php";
require __DIR__ . "/SMTP.php";

$mail = new PHPMailer(true);
$mail->isSMTP(); // Préciser que PHPMailer utilise le protocole SMTP (Simple Mail Transfer Protocol)
$mail->Host = "smtp.gmail.com"; // SPécifier le serveur (gmail)
$mail->SMTPAuth = true; // Activer l'authentification
$mail->Username = "misterfoxydev@gmail.com";
$mail->Password = "xnew zewg yhqz nlvx";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->SMTPDebug = 0; // Désactive le mode debug en production

// Configuration SSL nécessaire pour le développement local
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

$mail->setLanguage('fr');
$mail->CharSet = 'UTF-8';
$mail->setFrom("webcms2up@gmail.com", "Webcms");
$mail->addAddress($_POST['email']);
$mail->isHTML(true); // Activer l'envoi de mail sous forme HTML

$mail->Subject = "Réinitialisation du mot de passe";
require_once "includes/token.php";
$mail->Body = 'Bonjour ! Afin de réinitialiser votre mot de passe, merci de cliquer sur le lien suivant : <a href="localhost/webcms/new_password.php?token=' . $token . '&email=' . $_POST['email'] . '">Réinitialiser</a>';

if (!$mail->send()) {
    $GLOBALS['mail_confirmation_message'] = "Un problème est survenu, veuillez rééssayer";
    echo "Erreur : " . $mail->ErrorInfo;
} else {
    $GLOBALS['mail_confirmation_message'] = "Un mail vient d'être envoyé à votre adresse mail pour réinitialiser votre mot de passe !";
}