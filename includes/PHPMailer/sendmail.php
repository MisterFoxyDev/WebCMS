<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . "/PHPMailer.php";
require __DIR__ . "/Exception.php";
require __DIR__ . "/SMTP.php";

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = "smtp.gmail.com";
$mail->SMTPAuth = true;
$mail->Username = "misterfoxydev@gmail.com";
$mail->Password = "xnew zewg yhqz nlvx";
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

$mail->setFrom("webcms2up@gmail.com", "Webcms");
$mail->addAddress($_POST['email']);
$mail->isHTML(true);

$mail->Subject = "Confirmation d'adresse mail";

// Récupérer le token de l'utilisateur
require_once __DIR__ . "/../../includes/bdd.php";
$email = $_POST['email'];
$tokenReq = $bdd->prepare("SELECT user_token FROM users WHERE user_email = :email");
$tokenReq->bindValue(":email", $email);
$tokenReq->execute();
$tokenData = $tokenReq->fetch(PDO::FETCH_ASSOC);
$token = $tokenData['user_token'];

$mail->Body = 'Bienvenue ! Afin de valider votre inscription, merci de cliquer sur le lien suivant : <a href="http://' . $_SERVER['HTTP_HOST'] . '/webcms/verification.php?token=' . $token . '&email=' . $_POST['email'] . '">Confirmation</a>';

try {
    $mail->send();
    $GLOBALS['mail_success'] = true;
    $GLOBALS['mail_message'] = "Un mail de confirmation vient d'être envoyé à votre adresse mail pour valider votre inscription !";
} catch (Exception $e) {
    $GLOBALS['mail_success'] = false;
    $GLOBALS['mail_message'] = "Mail non envoyé, veuillez rééssayer";
}