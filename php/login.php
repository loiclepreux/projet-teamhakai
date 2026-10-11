<?php
// ✅ Démarre la session pour utiliser $_SESSION.
require_once __DIR__ . '/securite.php'; // session protégée

// ✅ Connexion à la base de données.
require_once 'config.php';

// Stocke les messages d'erreur à afficher à l'utilisateur.
$erreur = '';

// 🔒 Génère un token CSRF s'il n'existe pas encore (protège contre les attaques cross-site).
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 🛡️ Protection anti-bruteforce //
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

$maxAttempts = 5;  // Nombre max de tentatives.
$lockoutTime = 60; // Temps de blocage en secondes.

if ($_SESSION['login_attempts'] >= $maxAttempts) {
    $timePassed = time() - $_SESSION['last_attempt_time'];
    if ($timePassed < $lockoutTime) {
        $restant = $lockoutTime - $timePassed;
        die("⛔ Trop de tentatives. Réessaie dans $restant secondes.");
    } else {
        // ✅ On débloque si le temps est écoulé.
        $_SESSION['login_attempts'] = 0;
    }
}

// ✅ Vérifie si le formulaire a été soumis.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 🛡️ Blocage par adresse IP (5 erreurs = 15 minutes d'attente)
    $attente = limite_attente();
    if ($attente > 0) {
        $_SESSION['message'] = "⛔ Trop de tentatives. Réessaie dans " . ceil($attente / 60) . " minute(s).";
        header('Location: ../index.php');
        exit();
    }

    // ✅ Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur CSRF : formulaire invalide.");
    }

    // ✅ Récupération des données du formulaire.
    $pseudo = trim($_POST['username'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (!empty($pseudo) && !empty($mot_de_passe)) {
        try {
            // 🔍 Requête pour récupérer l'utilisateur par pseudo.
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo = :pseudo");
            $stmt->execute([':pseudo' => $pseudo]);
            $user = $stmt->fetch();

            // 🔑 Vérifie que le mot de passe correspond au hash en base.
            if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // ✅ Connexion réussie : nouvel identifiant de session (anti vol de session)
                session_regenerate_id(true);
                limite_reset();
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'pseudo' => $user['pseudo'],
                    'photo_profil' => $user['photo_profil'],
                    'role' => $user['role'] ?? 'user'
                ];

                $_SESSION['id'] = $user['id']; // Stockage simplifié.

                $_SESSION['login_attempts'] = 0; // 🔄 Reset des tentatives après succès.

                if (isset($_POST['rapel']) && $_POST['rapel'] === 'on') {
                    // ✅ Si la case "Se souvenir de moi" est cochée, on crée un cookie.
                    setcookie('remember_me', $user['pseudo'], time() + (86400 * 365), "/"); // 1 an de validité.
                }

                header('Location: ../index.php'); // ✅ Redirection.
                exit();
                
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                limite_echec();
                $erreur = "Identifiants incorrects.";

                header('Location: ../index.php'); // ✅ Redirection.
                exit();
            }
        } catch (PDOException $e) {
            $erreur = "Erreur de connexion à la base.";
        }
    } else {
        $erreur = "Tous les champs sont obligatoires.";
    }
}
?>