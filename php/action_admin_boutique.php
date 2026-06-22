<?php
// ✅ Démarre la session pour accéder aux variables $_SESSION.
session_start();

// ✅ Connexion à la base de données via le fichier config.php.
require_once 'config.php';

// ✅ Vérifie que l’utilisateur est bien connecté ET qu’il est admin.
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php'); // Redirection vers la page de login.
    exit(); // Stoppe l'exécution du script.
}

// ✅ Vérifie que le formulaire a bien été envoyé en POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Protection CSRF : vérifie que le token est bien présent et valide.
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Erreur CSRF : requête invalide.");  // Stoppe l’exécution si une faille est détectée.
}

     // ✅ Récupère l’ID de l’article à traiter (avec fallback à 0).
    $articleId = trim($_POST['articles_id'] ?? 0);

    // ✅ Récupère l’action à effectuer : "modifier" ou "supprimer".
    $action = $_POST['action'] ?? null;

    // ❌ Si aucun article n’est sélectionné, redirige vers la page boutique avec un message d’erreur.
    if (!$articleId) {
        header('Location: boutique.php?erreur=aucun_article_selectionne');
        exit();
    }

     // 🔁 Si l'action est "modifier", on redirige vers la page de modification.
    if ($action === 'modifier') {
        $articleId = urlencode($articleId); // Sécurise l’ID pour l’URL.
        header("Location: ../php/modif_article.php?id=$articleId");
        exit();
    }

    // 🗑 Si l'action est "supprimer", on supprime l’article de la base.
    if ($action === 'supprimer') {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id_article = ?");
    $stmt->execute([$articleId]);

    // ✅ Message de confirmation affiché sur la boutique après redirection.
    $_SESSION['message'] = "🗑 Article supprimé avec succès.";
    
    header("Location: boutique.php");
    exit();
    }
}
?>