<?php
// ✅ Démarrage de session : indispensable pour accéder aux données de l’utilisateur connecté et aux messages de session.
session_start();

// ✅ Connexion sécurisée à la base via PDO.
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // 🔁 Redirection vers la connexion si utilisateur non authentifié.
    exit(); // ⛔ Stoppe l'exécution du script.
}

// 🔐 Création d’un token CSRF sécurisé si il n'en n'existe pas.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = $_SESSION['user']['id']; // 🔎 On récupère l’ID du membre connecté.
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$utilisateur = $stmt->fetch(); // 💾 Récupération des infos en base.

if (!$utilisateur) {
    die("Utilisateur introuvable.");  // 🛑 Si aucune correspondance trouvée → blocage immédiat.
}

// ✅ Vérifie que le formulaire a été envoyé via méthode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Vérifie le token CSRF pour éviter les requêtes frauduleuses.
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur CSRF.");
    }

    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $mot_de_passe = !empty($_POST['mot_de_passe']) ? password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT) : $utilisateur['mot_de_passe'];
    $genre = $_POST['genre'] ?? '';
    $age = intval($_POST['age']);
    $biographie = trim($_POST['biographie']);
    $style_jeu = $_POST['style_jeu'] ?? '';
    $plateforme = $_POST['plateforme'] ?? '';
    $photo = $_FILES['photo_profil'] ?? null;
    $filename = $utilisateur['photo_profil'];  // Par défaut : on garde l’image actuelle.

    $genres_valides     = ['homme', 'femme'];
    $plateformes_valides = ['xbox', 'play', 'pc'];

    if (!in_array($genre, $genres_valides) || !in_array($plateforme, $plateformes_valides)) {
        $_SESSION['message'] = "Valeur invalide pour le genre ou la plateforme.";
        header('Location: modifier_profils.php');
        exit();
    }

    if ($photo && $photo['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mimeType = mime_content_type($photo['tmp_name']);
        $maxSize  = 2 * 1024 * 1024;

        if (!in_array($mimeType, $allowedTypes) || $photo['size'] > $maxSize) {
            $_SESSION['message'] = "Image invalide ou trop volumineuse (max 2 Mo).";
            header('Location: modifier_profils.php');
            exit();
        }

        $upload_dir = '../php/profils/';
        if (!is_dir($upload_dir)) mkdir($upload_dir);
        $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $filename = 'profil_' . uniqid() . '.' . $ext;
        move_uploaded_file($photo['tmp_name'], $upload_dir . $filename);
    }

    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE (pseudo = ? OR email = ?) AND id != ?");
    $check->execute([$pseudo, $email, $id]);
    if ($check->fetch()) {
        $_SESSION['message'] = "Ce pseudo ou cet email est déjà utilisé par un autre membre.";
        header('Location: modifier_profils.php');
        exit();
    }

    $update = $pdo->prepare("UPDATE utilisateurs SET pseudo = ?, email = ?, mot_de_passe = ?, genre = ?, age = ?, biographie = ?, style_jeu = ?, plateforme = ?, photo_profil = ? WHERE id = ?");
    $update->execute([$pseudo, $email, $mot_de_passe, $genre, $age, $biographie, $style_jeu, $plateforme, $filename, $id]);

    $_SESSION['user']['pseudo'] = $pseudo;
    $_SESSION['user']['photo_profil'] = $filename;

    $_SESSION['message'] = "Profil mis à jour avec succès.";
    header('Location: membre.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon profil</title>

    <!-- ✅ Icône de l’onglet (favicon) -->
    <link rel="icon" href="../img/favicon.ico" type="image/x-icon" />

    <!-- 🎨 Fichier de styles personnalisé -->
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/membre.css">
    <link rel="stylesheet" href="../css/formulaire2.css">
</head>

<body>

    <!-- 🔙 Lien retour vers les membres -->
    <a href="membre.php" class="retour-membre">← Retour à la page Membre</a>

    <h1>Modifier mon profil</h1>

    <?php if (!empty($_SESSION['message'])): ?>
        <p class="message-confirmation"><?= htmlspecialchars($_SESSION['message']) ?></p>
        <?php unset($_SESSION['message']); ?> <!-- 🔄 Supprime le message pour qu'il ne réapparaisse pas -->
    <?php endif; ?>

    <!-- ✅ Formulaire d'ajout -->
    <form method="POST" enctype="multipart/form-data">
        <!-- 🔐 CSRF token de protection -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- Champ pseudo -->
        <label>Pseudo</label>
        <input type="text" name="pseudo" value="<?= htmlspecialchars($utilisateur['pseudo']) ?>" required>

        <!-- Champ email -->
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" required>

        <!-- Champ mot de passe -->
        <label>Mot de passe (laisser vide si inchangé)</label>
        <input type="password" name="mot_de_passe">

        <!-- Champ genre -->
        <label>Genre</label>
        <select name="genre">
            <option value="homme" <?= $utilisateur['genre'] === 'homme' ? 'selected' : '' ?>>Homme</option>
            <option value="femme" <?= $utilisateur['genre'] === 'femme' ? 'selected' : '' ?>>Femme</option>
        </select>

        <!-- Champ âge -->
        <label>Âge</label>
        <input type="number" name="age" value="<?= $utilisateur['age'] ?>">

        <!-- Champ style de jeu -->
        <label>Style de jeu</label>
        <input type="text" name="style_jeu" value="<?= htmlspecialchars($utilisateur['style_jeu']) ?>">

        <!-- Champ plateforme -->
        <label>Plateforme</label>
        <select name="plateforme">
            <option value="xbox" <?= $utilisateur['plateforme'] === 'xbox' ? 'selected' : '' ?>>Xbox</option>
            <option value="play" <?= $utilisateur['plateforme'] === 'play' ? 'selected' : '' ?>>Playstation</option>
            <option value="pc" <?= $utilisateur['plateforme'] === 'pc' ? 'selected' : '' ?>>PC</option>
        </select>

        <!-- Champ biographie -->
        <label>Biographie</label>
        <textarea name="biographie"><?= htmlspecialchars($utilisateur['biographie']) ?></textarea>

        <!-- Champ photo de profil -->
        <label for="photo_profil">Photo de profil</label>
        <input type="file" name="photo_profil" id="profile-img" accept="image/*">

        <button type="submit">✅ Mettre à jour</button>
    </form>
</body>
</html>