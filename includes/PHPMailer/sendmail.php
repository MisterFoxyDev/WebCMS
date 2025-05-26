<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . "/PHPMailer.php";
require __DIR__ . "/Exception.php";
require __DIR__ . "/SMTP.php";

// Fonction pour charger les variables d'environnement
function loadEnv($path)
{
    if (!file_exists($path)) {
        throw new Exception('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

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