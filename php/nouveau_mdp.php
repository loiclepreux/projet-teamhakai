<?php
// ✅ Démarre la session pour accéder aux variables $_SESSION.
session_start();

// ✅ Connexion à la base de données via le fichier config.php.
require_once 'config.php';

// ✅ Requête de réinitialisation du mot de passe.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Protection CSRF : vérifie que le token est bien présent et valide.
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF invalide.');
    }

    $email = $_SESSION['email'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon Mot de passe</title>

    <!-- ✅ Icône de l’onglet (favicon) -->
    <link rel="icon" href="img/favicon.ico" type="image/x-icon" />

    <!-- 🎨 Fichier de styles personnalisé -->
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/formulaire3.css">
    
</head>
<body style="background-image: url('img/orage.png'); background-size: cover;">

    <!-- 🔙 Lien retour vers l accueil -->
    <a href="../index.php" class="retour-accueil">← Retour à la page Accueil</a>

    <h1>Modifier mon Mot de passe</h1>

    <!-- ✅ Formulaire d'ajout -->
    <form method="POST" enctype="multipart/form-data">
        <!-- 🔐 CSRF token de protection -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- Champ mot de passe -->
        <label>Mot de passe (laisser vide si inchangé)</label>
        <input type="password" name="mot_de_passe">

        <!-- Confirmer votre mot de passe -->
        <label>Confirmer votre mot de passe (laisser vide si inchangé)</label>
        <input type="password" name="mot_de_passe_verification">

        <button type="submit" name="confirmer">✅ Mettre à jour</button>
    </form>

    <?php if (!empty($_SESSION['message'])): ?>
        <p class="message-confirmation"><?= htmlspecialchars($_SESSION['message']) ?></p>
        <?php unset($_SESSION['message']); ?> <!-- 🔄 Supprime le message pour qu'il ne réapparaisse pas -->
    <?php endif; ?>

</body>
</html>

<?php
    if (isset($_POST['confirmer'])) {
            if (empty($_POST['mot_de_passe']) && empty($_POST['mot_de_passe_verification'])) {
    $_SESSION['message'] = "❌ Aucun mot de passe n'a été saisi. Veuillez entrer un nouveau mot de passe.";
    }
    elseif (empty($_POST['mot_de_passe']) || empty($_POST['mot_de_passe_verification'])) {
    $_SESSION['message'] = "❌ Les champs mot de passe et confirmation ne peuvent pas être vides.";

    }elseif ($_POST['mot_de_passe'] !== $_POST['mot_de_passe_verification']) {
    $_SESSION['message'] = "❌ Les mots de passe ne correspondent pas. Veuillez réessayer.";

    }else 
    $mot_de_passe = $_POST['mot_de_passe'];

    // ✅ Met à jour le mot de passe dans la base de données.
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = :mot_de_passe WHERE email = :email");
    $stmt->bindParam(':mot_de_passe', password_hash($mot_de_passe, PASSWORD_DEFAULT));
    $stmt->bindParam(':email', $_SESSION['email']);
    
    $stmt->execute(); 
        $_SESSION['message'] = "✅ Mot de passe mis à jour avec succès.";
        unset($_SESSION['email']);
        header('Location: ../index.php');
        exit();
    } 
 
?>