<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Corriger les chemins selon ton dossier : PHPMailer-master
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp-loic.alwaysdata.net';
    $mail->SMTPAuth = true;
    $mail->Username = 'lepreuxlolo@gmail.com';
    $mail->Password = 'eammjyfgxasqgzwm'; // ← ici, colle bien ton mot de passe app
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->SMTPOptions = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
];

    $mail->setFrom('lepreuxlolo@gmail.com', 'Test Gmail');
    $mail->addAddress('tonadresse@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Test depuis PHPMailer';
    $mail->Body    = 'Bravo, ce message vient de XAMPP avec PHPMailer.';

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';

    $mail->send();
    echo '✅ Email envoyé !';
} catch (Exception $e) {
    echo '❌ Erreur : ' . $mail->ErrorInfo;
}
?>