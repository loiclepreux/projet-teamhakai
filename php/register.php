<?php
// ✅ Démarrage de la session (obligatoire pour utiliser $_SESSION).
session_start();

// ✅ Inclusion du fichier de configuration de la base de données.
require_once 'config.php';

// ✅ Génération du token CSRF s'il n'existe pas encore.
if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ Vérifie que le formulaire a été envoyé via méthode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // 🔒 Vérifie la validité du token CSRF.
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur CSRF : formulaire invalide.");
        }

// ⏱ Anti spam/robot : limite les soumissions rapides.       
if (isset($_SESSION['last_register']) && time() - $_SESSION['last_register'] < 10) {
        die("Merci d'attendre quelques secondes avant de soumettre à nouveau.");
        }
        $_SESSION['last_register'] = time(); // Enregistre le moment de soumission.

        // 🔎 Récupération des champs du formulaire.
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        $genre = $_POST['genre'] ?? '';
        $age = intval($_POST['age'] ?? 0);
        $plateforme = $_POST['plateforme'] ?? '';
        $style_jeu = $_POST['style_jeu'] ?? '';
        $biographie = strip_tags(trim($_POST['biographie'] ?? '')); // Protection XSS.

        // 🔘 Choix des modes de jeu.
        $warzone = isset($_POST['warzone']) ? 'oui' : 'non';
        $multijoueur = isset($_POST['multijoueur']) ? 'oui' : 'non';
        $zombie = isset($_POST['zombie']) ? 'oui' : 'non';
        $campagne = isset($_POST['campagne']) ? 'oui' : 'non';

        $photo = $_FILES['photo_profil'];
        $filename = '';

if ($photo['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mimeType = mime_content_type($photo['tmp_name']);
        $maxSize = 2 * 1024 * 1024; // 2 Mo

        if (in_array($mimeType, $allowedTypes) && $photo['size'] <= $maxSize) {
                $extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
                $filename = uniqid('profil_', true) . '.' . $extension;
                move_uploaded_file($photo['tmp_name'], '../php/profils/' . $filename);
        } else {
                die("Image invalide ou trop volumineuse.");
        }
}

$genres_valides     = ['homme', 'femme'];
$plateformes_valides = ['xbox', 'play', 'pc'];
$styles_valides     = ['rusheur', 'campeur', 'sniper', 'tacticien', 'ninja', 'support'];

if (!in_array($genre, $genres_valides) || !in_array($plateforme, $plateformes_valides) || !in_array($style_jeu, $styles_valides)) {
        $_SESSION['message'] = "Valeur invalide pour genre, plateforme ou style de jeu.";
        header('Location: ../index.php');
        exit();
}

if (!empty($pseudo) && !empty($email) && !empty($mot_de_passe)) {
        try {

        // 🔍 Vérifie si pseudo ou email existe déjà.   
        $check = $pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo = :pseudo OR email = :email");
        $check->execute([':pseudo' => $pseudo, ':email' => $email]);

        if ($check->fetch()) {
                $_SESSION['message'] = "Un compte avec ce pseudo ou cet email existe déjà.";
                header('Location: ../index.php');
                exit();
        } else {

                // 🔐 Hachage du mot de passe.
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

                // 💾 Insertion en base de donnée.
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (pseudo, email, mot_de_passe, genre, age, plateforme, style_jeu, biographie, photo_profil, warzone, multijoueur, zombie, campagne, date_inscription) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$pseudo, $email, $hash, $genre, $age, $plateforme, $style_jeu, $biographie, $filename, $warzone, $multijoueur, $zombie, $campagne]);

                 // ✅ Message et redirection
                $_SESSION['message'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                header('Location: ../index.php');
                exit();
        }
        } catch (PDOException $e) {
            $_SESSION['message'] = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
            header('Location: ../index.php');
            exit();
        }
} else {
        $_SESSION['message'] = "Tous les champs obligatoires doivent être remplis.";
        header('Location: ../index.php');
        exit();
}
}
?>