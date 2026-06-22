<?php
// ✅ Démarre la session pour accéder à $_SESSION.
session_start();

// ✅ Génère un token CSRF s'il n'existe pas encore.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ Connexion à la base de données.
require_once 'config.php';

// ✅ Vérifie que l'utilisateur est connecté et qu’il est administrateur.
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php'); // Redirection si non administrateur.
    exit();
}

// ✅ Vérifie que le formulaire a été envoyé via méthode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Vérifie le token CSRF pour éviter les requêtes frauduleuses
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Erreur CSRF : requête invalide.");
}

    // 🔎 Récupération des champs du formulaire.
    $nom = $_POST['nom_article'] ?? '';
    $prix = $_POST['prix'] ?? '';
    $image = $_FILES['image'] ?? null;

     // ✅ Si nom, prix et image sont valides.
    if (!empty($nom) && !empty($prix) && $image && $image['error'] === 0) {

         // 🔐 Sécurité fichier : types autorisés + taille max.
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2 Mo

if ($image['error'] === 0 && in_array($image['type'], $allowedTypes) && $image['size'] <= $maxSize) {

    // 🧾 Génère un nom de fichier unique.
    $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $targetPath = '../img/' . $filename;

    // ✅ Déplace le fichier vers le dossier img/.
    if (move_uploaded_file($image['tmp_name'], $targetPath)) {

         // ✅ Insère l’article dans la base de données.
        $stmt = $pdo->prepare("INSERT INTO articles (nom_article, prix, image) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $prix, $filename]);

        $_SESSION['message'] = "✅ Article ajouté avec succès.";
        header('Location: boutique.php');
        exit();
    } else {
        $erreur = "Erreur lors de l'enregistrement du fichier.";
    }
    } else {
    $erreur = "Image invalide (format ou taille incorrecte).";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article</title>

    <!-- ✅ Icône de l’onglet (favicon) -->
    <link rel="icon" href="../img/favicon.ico" type="image/x-icon" />

    <!-- ✅ Feuilles de style -->
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/boutique.css">
    <link rel="stylesheet" href="../css/formulaire.css">
</head>

<body style="background-image: url('../img/orage.png'); background-size: cover;">

<main class="form-container">
    <!-- 🔙 Lien retour vers la boutique -->
    <a href="boutique.php" class="retour-btn">← Retour à la boutique</a>

    <h2>✏️ ajouter un article</h2>

    <!-- ✅ Formulaire d'ajout -->
    <form method="POST" enctype="multipart/form-data">
        <!-- 🔐 CSRF token de protection -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- 🖊 Nom de l'article -->
        <label>Nom :</label>
        <input type="text" name="nom_article" required>

        <!-- 💰 Prix de l'article -->
        <label>Prix (€) :</label>
        <input type="number" name="prix" step="0.01" required>

        <!-- 🖼 Ajout d’image -->
        <label>Image (laisser vide pour conserver l'actuelle) :</label>
        <input type="file" name="image" accept="image/*">

        <!-- ✅ Zone pour affichage de prévisualisation éventuelle -->
        <img src="../img/" width="100" style="border-radius: 8px;">

        <!-- 🟢 Bouton de validation -->
        <button type="submit">Enregistrer</button>
    </form>
</main>

</body>
</html>