<?php
// ✅ Démarrage de la session (obligatoire pour utiliser $_SESSION).
session_start();

// ✅ Inclusion du fichier de configuration de la base de données.
require_once 'config.php';

// ✅ JSON en réponse (API).
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(403); // Erreur HTTP 403 : accès refusé.
    echo json_encode(['erreur' => 'Non autorisé']);
    exit();
}

// ✅ On lit le corps brut de la requête POST, typiquement envoyé par fetch().
$data = json_decode(file_get_contents('php://input'), true);

// 🔎 On extrait et nettoie l’URL de la vidéo.
$url = trim($data['url'] ?? '');

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400); // Mauvaise requête.
    echo json_encode(['erreur' => 'URL invalide']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM videos WHERE url = :url AND id_utilisateur = :id");
    $stmt->execute([
        ':url' => $url,
        ':id' => $_SESSION['id']
    ]);

    // ✅ Réponse JSON positive.
    echo json_encode(['succès' => true]);

} catch (PDOException $e) {
    // ❌ Erreur SQL (problème serveur, syntaxe, etc.).
    http_response_code(500);
    echo json_encode(['erreur' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>