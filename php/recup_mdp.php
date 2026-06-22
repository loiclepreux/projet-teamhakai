<?php
session_start();

require_once __DIR__ . '/../SMTP//PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../SMTP/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../SMTP/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF invalide.');
    }

    if (isset($_POST['demande_code'])) {
        $email = $_POST['email'] ?? '';
        $_SESSION['email']= $email;
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur) {
            $_SESSION['code_mdp'] = bin2hex(random_bytes(6));

            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = 0; // Désactiver les logs visibles
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USER;
                $mail->Password = MAIL_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom(MAIL_USER);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de mot de passe';
                $mail->Body = 'Bonjour,<br>Voici votre code de réinitialisation : <strong>' . $_SESSION['code_mdp'] . '</strong><br>Ne le partagez pas.';
                $mail->AltBody = 'Bonjour, votre code est : ' . $_SESSION['code_mdp'];

                $mail->send();
                $_SESSION['message'] = "📩 Un e-mail a été envoyé à $email.";
            } catch (Exception $e) {
                $_SESSION['message'] = "❌ Erreur lors de l'envoi de l'e-mail : " . $mail->ErrorInfo;
            }
        } else {
            $_SESSION['message'] = "❌ Aucune adresse e-mail trouvée.";
        }

        header('Location: recup_mdp.php');
        exit();
    }

    if (isset($_POST['verif_code'])) {
        $code = $_POST['code_mdp'] ?? '';

        if (!empty($_SESSION['code_mdp']) && $code === $_SESSION['code_mdp']) {
            unset($_SESSION['code_mdp']);
            header('Location: nouveau_mdp.php');
            exit();
        } else {
            $_SESSION['message'] = "❌ Code incorrect.";
            header('Location: recup_mdp.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Team HAKAI - Réinitialisation</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="../css/base.css" />
    <link rel="stylesheet" href="../css/formulaire3.css" />
</head>

<body>

    <a href="../index.php" class="retour">← Retour à la page de connexion</a>

    <h1>Récupération de mot de passe</h1>

    <?php if (!empty($_SESSION['message'])): ?>
        <p class="message-confirmation"><?= htmlspecialchars($_SESSION['message']) ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="container">

        <form method="POST" action="recup_mdp.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <label for="email">Adresse e-mail :</label>
            <input type="email" id="email" name="email" required />
            <button type="submit" name="demande_code">Envoyer le lien de réinitialisation</button>
        </form>

        <?php if (!empty($_SESSION['code_mdp'])): ?>
            <h2>Vérification du code reçu</h2>
            <form method="POST" action="recup_mdp.php">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <label for="code_mdp">Code de réinitialisation :</label>
                <input type="text" id="code_mdp" name="code_mdp" required />
                <button type="submit" name="verif_code">Vérifier le code</button>
            </form>
        <?php endif; ?>

        <p>Déjà un compte ? <a href="../index.php" class="retour">Connectez-vous ici</a></p>

    </div>
</body>

</html>