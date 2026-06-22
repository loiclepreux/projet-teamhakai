<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../SMTP/PHPMailer/src/Exception.php';
require '../SMTP/PHPMailer/src/PHPMailer.php';
require '../SMTP/PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);
$email = $_POST['email'];
$name = $_POST['name'];
$mailContent = $_POST['mailContent'];

try {
    //Server settings
    $mail->SMTPDebug = 0;                      // Enable verbose debug output
    $mail->isSMTP();                           // Set mailer to use SMTP
    $mail->Host       = 'smtp.gmail.com';    // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                  // Enable SMTP authentication
    $mail->Username   = 'bountynebor@gmail.com'; // SMTP username
    $mail->Password   = 'cixg bjco chbe hfof'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption, `ssl` also accepted
    $mail->Port       = 587;                   // TCP port to connect to

    //Recipients
    $mail->setFrom('bountynebor@gmail.com', 'Enzo');
    $mail->addAddress($email, $name);     // Add a recipient


    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Coucou :v';   
    $mail->Body    = $mailContent;
    $mail->AltBody = $mailContent;
    $mail->send();
    echo 'Message has been sent';
    header('Location: MailSender.php');
    exit();
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>


