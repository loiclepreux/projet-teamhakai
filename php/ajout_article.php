<?php
// ✅ Démarre la session pour accéder à $_SESSION.
require_once __DIR__ . '/securite.php'; // session protégée

// ✅ Génère un token CSRF s'il n'existe pas encore.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ Connexion à la base de données.
require_once 'config.php';

// ✅ Vérifie que l'utilisateur est connecté et qu'il est administrateur.
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

         // 🔐 Image vérifiée et renommée par le serveur (voir securite.php)
    $filename = enregistrer_image($image, __DIR__ . '/../img', 'img_');

    if ($filename !== null) {

         // ✅ Insère l'article dans la base de données.
        $stmt = $pdo->prepare("INSERT INTO articles (nom_article, prix, image) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $prix, $filename]);

        $_SESSION['message'] = "✅ Article ajouté avec succès.";
        header('Location: boutique.php');
        exit();
    } else {
        $erreur = "Image invalide (JPG, PNG, WEBP ou GIF, 2 Mo max).";
    }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article</title>

    <!-- ✅ Icône de l'onglet (favicon) -->
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

        <!-- 🖼 Ajout d'image -->
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