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

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = getenv('SMTP_HOST');
$mail->SMTPAuth = true;
$mail->Username = getenv('SMTP_USERNAME');
$mail->Password = getenv('SMTP_PASSWORD');
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->SMTPDebug = 0;

// Configuration SSL nécessaire pour le développement local
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

$mail->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
$mail->addAddress($_POST['email']);
$mail->isHTML(true);

$mail->Subject = "Confirmation d'adresse mail";
require_once "includes/token.php";
$mail->Body = 'Bienvenue ! Afin de valider votre inscription, merci de cliquer sur le lien suivant : <a href="' . getenv('APP_URL') . '/verification.php?token=' . $token . '&email=' . $_POST['email'] . '">Confirmation</a>';

if (!$mail->send()) {
    $GLOBALS['mail_confirmation_message'] = "Mail non envoyé, veuillez rééssayer";
    echo "Erreur : " . $mail->ErrorInfo;
} else {
    $GLOBALS['mail_confirmation_message'] = "Un mail de confirmation vient d'être envoyé à votre adresse mail pour valider votre inscription !";
}