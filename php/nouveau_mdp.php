<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (empty($_SESSION['email'])) {
    header('Location: recup_mdp.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {

    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF invalide.');
    }

    $mdp       = $_POST['mot_de_passe']             ?? '';
    $mdp_verif = $_POST['mot_de_passe_verification'] ?? '';

    if (empty($mdp) && empty($mdp_verif)) {
        $_SESSION['message'] = "❌ Aucun mot de passe n'a été saisi. Veuillez entrer un nouveau mot de passe.";
    } elseif (empty($mdp) || empty($mdp_verif)) {
        $_SESSION['message'] = "❌ Les champs mot de passe et confirmation ne peuvent pas être vides.";
    } elseif ($mdp !== $mdp_verif) {
        $_SESSION['message'] = "❌ Les mots de passe ne correspondent pas. Veuillez réessayer.";
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = :mot_de_passe WHERE email = :email");
        $stmt->bindValue(':mot_de_passe', password_hash($mdp, PASSWORD_DEFAULT));
        $stmt->bindValue(':email', $_SESSION['email']);
        $stmt->execute();
        $_SESSION['message'] = "✅ Mot de passe mis à jour avec succès.";
        unset($_SESSION['email']);
        header('Location: ../index.php');
        exit();
    }

    header('Location: nouveau_mdp.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon Mot de passe</title>
    <link rel="icon" href="../img/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/formulaire3.css">
</head>
<body style="background-image: url('../img/orage.png'); background-size: cover;">

    <a href="../index.php" class="retour-accueil">← Retour à la page Accueil</a>

    <h1>Modifier mon Mot de passe</h1>

    <?php if (!empty($_SESSION['message'])): ?>
        <p class="message-confirmation"><?= htmlspecialchars($_SESSION['message']) ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label>Nouveau mot de passe</label>
        <input type="password" name="mot_de_passe" required>

        <label>Confirmer le mot de passe</label>
        <input type="password" name="mot_de_passe_verification" required>

        <button type="submit" name="confirmer">✅ Mettre à jour</button>
    </form>

</body>
</html>
