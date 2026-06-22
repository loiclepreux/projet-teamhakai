<?php
// ✅ Démarre la session pour utiliser $_SESSION.
session_start();

// ✅ Connexion à la base de données.
require_once 'config.php';

// 🔐 Génère un token CSRF s'il n'existe pas encore.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ Seuls les admins peuvent modifier un article.
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = "❌ ID d'article manquant.";
    header('Location: boutique.php');
    exit();
}

// ✅ Requête SQL pour récupérer l'article depuis la base de données.
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id_article = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    $_SESSION['message'] = "❌ Article introuvable.";
    header('Location: boutique.php');
    exit();
}

// ✅ Vérifie que le formulaire a été envoyé via méthode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 🔐 Vérifie que le CSRF token est correct.
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur CSRF : requête invalide.");
    }

    // 📥 Données du formulaire.
    $nom = trim($_POST['nom_article']);
    $prix = floatval($_POST['prix']);
    $image = $_FILES['image'] ?? null;

    // Par défaut, on garde l'image actuelle.
    $filename = $article['image']; 
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2 Mo

    if ($image && $image['error'] === 0) {
        // Vérifie si le fichier est une image et respecte la taille maximale.
        if (in_array($image['type'], $allowedTypes) && $image['size'] <= $maxSize) {
            $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_', true) . '.' . $extension;
            move_uploaded_file($image['tmp_name'], '../img/' . $filename);
        } else {
            // Si l'image n'est pas valide, on redirige avec un message d'erreur.
            $_SESSION['message'] = "❌ Image non valide ou trop volumineuse.";
            header("Location: modif_article.php?id=$id");
            exit();
        }
    }

    try {
        $update = $pdo->prepare("UPDATE articles SET nom_article = ?, prix = ?, image = ? WHERE id_article = ?");
        $update->execute([$nom, $prix, $filename, $id]);

        $_SESSION['message'] = "✅ Article modifié avec succès.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "❌ Erreur lors de la modification : " . $e->getMessage();
    }

    header('Location: boutique.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un article</title>

    <!-- ✅ Icône de l'onglet (favicon) -->
    <link rel="icon" href="../img/favicon.ico" type="image/x-icon" />

    <!-- 🧩 Feuilles de style -->
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/boutique.css">
    <link rel="stylesheet" href="../css/formulaire.css">
</head>
<body style="background-image: url('../img/orage.png'); background-size: cover;">

<main class="form-container">
    <!-- 🔙 Lien retour -->
    <a href="boutique.php" class="retour-btn">← Retour à la boutique</a>

    <h2>✏️ Modifier l'article</h2>

    <!-- 📝 Formulaire de mise à jour -->
    <form method="POST" enctype="multipart/form-data">
        <!-- 🔐 Protection CSRF -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label>Nom :</label>
        <input type="text" name="nom_article" value="<?= htmlspecialchars($article['nom_article']) ?>" required>

        <label>Prix (€) :</label>
        <input type="number" name="prix" step="0.01" value="<?= htmlspecialchars($article['prix']) ?>" required>

        <label>Image (laisser vide pour conserver l'actuelle) :</label>
        <input type="file" name="image" accept="image/*">

        <!-- 🖼 Prévisualisation de l'image actuelle -->
        <p>Image actuelle :</p>
        <img src="../img/<?= htmlspecialchars($article['image']) ?>" width="100" style="border-radius: 8px;">

        <button type="submit">Enregistrer</button>
    </form>
</main>
</body>
</html>