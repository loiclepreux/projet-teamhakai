<?php
// ✅ Démarre la session.
session_start();

// ✅ Indique que la réponse sera en JSON.
header('Content-Type: application/json');

// Connexion PDO à la base de données.
require_once 'config.php';

// ✅ Vérifie le token CSRF pour empêcher les attaques CSRF.
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur CSRF']);
    exit;
}

// ✅ Vérifie que l’ID de la vidéo est bien transmis et valide.
$videoId = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;
if ($videoId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID vidéo invalide']);
    exit;
}

// ✅ Si l'utilisateur est connecté → on utilise son ID, sinon on utilise son adresse IP.
$isUser = isset($_SESSION['user']['id']);
$userId = $isUser ? $_SESSION['user']['id'] : null;
$userIP = $_SERVER['REMOTE_ADDR']; // IP du visiteur.

try {
    if ($isUser) {
        // ✅ Pour un utilisateur connecté → on vérifie si ce user a déjà liké cette vidéo.
        $check = $pdo->prepare("SELECT 1 FROM likes WHERE id_video = ? AND id_utilisateur = ?");
        $check->execute([$videoId, $userId]);
    } else {
        // ✅ Pour un visiteur → on vérifie avec son adresse IP.
        $check = $pdo->prepare("SELECT 1 FROM likes WHERE id_video = ? AND ip_address = ?");
        $check->execute([$videoId, $userIP]);
    }

    // ❌ Si déjà liké → on refuse le like.
    if ($check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Déjà liké']);
        exit;
    }

    if ($isUser) {
        // Ajoute un like lié à un utilisateur connecté.
        $stmt = $pdo->prepare("INSERT INTO likes (id_video, id_utilisateur, date_like) VALUES (?, ?, NOW())");
        $stmt->execute([$videoId, $userId]);
    } else {
        // Ajoute un like lié à une IP de visiteur.
        $stmt = $pdo->prepare("INSERT INTO likes (id_video, ip_address, date_like) VALUES (?, ?, NOW())");
        $stmt->execute([$videoId, $userIP]);
    }

    $count = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_video = ?");
    $count->execute([$videoId]);
    $totalLikes = $count->fetchColumn();

    // ✅ Réponse JSON avec le nouveau total de likes.
    echo json_encode(['status' => 'success', 'likes' => $totalLikes]);

} catch (PDOException $e) {
    // ❌ Réponse générique en cas d’erreur base de données.
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur']);
}