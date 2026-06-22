<?php
// ✅ Démarre la session pour utiliser les variables $_SESSION.
session_start();

// ✅ Connexion à la base de données via le fichier config.php..
require_once 'config.php';

// ✅ Vérifie que l'utilisateur est connecté ET qu’il est administrateur.
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Accès refusé."); // ⚠️ Accès interdit si non-administrateur.
}

// ✅ Vérifie que le formulaire a été envoyé via méthode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Vérifie la validité du token CSRF.
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die("Erreur CSRF."); // Si le token ne correspond pas, on bloque la requête
    }

    // ✅ Récupère l'ID du membre à supprimer (0 si non défini).
    $id_membre = intval($_POST['id_membre'] ?? 0);

     // ✅ Récupère le type d'action à effectuer.
    $action = $_POST['action'] ?? '';

    // ✅ Si l'action est "supprimer" ET qu’un id_membre valide est fourni.
    if ($action === 'supprimer' && $id_membre > 0) {

         // ⛔ L'admin ne peut pas supprimer son propre compte.
        if ($id_membre == $_SESSION['user']['id']) {
            $_SESSION['message'] = "❌ Vous ne pouvez pas supprimer votre propre compte.";
        } else {
             // ✅ Requête de suppression du membre dans la base de données.
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->execute([$id_membre]);

            // ✅ Message de confirmation stocké en session.
            $_SESSION['message'] = "🗑 Membre supprimé avec succès.";
        }
    }

    // ✅ Redirection vers la page membre après traitement
    header('Location: membre.php');
    exit();
}