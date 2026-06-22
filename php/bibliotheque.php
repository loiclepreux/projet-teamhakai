<?php
// 🔓 Démarre la session pour accéder aux données utilisateur (id, pseudo, etc.).
session_start();

// 🔐 Création du token CSRF si inexistant (sécurité contre les attaques externes).
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 🔌 Inclusion du fichier de configuration PDO (connexion à la BDD).
require_once 'config.php';

// 📦 Initialise un tableau vide de vidéos par défaut.
$videos = [];

try {
     // 📄 Requête SQL : sélectionne toutes les vidéos avec le pseudo du posteur et le nombre de likes.
    $stmt = $pdo->query("SELECT v.*, u.pseudo,
        (SELECT COUNT(*) FROM likes WHERE id_video = v.id) AS likes
        FROM videos v
        JOIN utilisateurs u ON v.id_utilisateur = u.id
        ORDER BY v.date_publication DESC"); // 🔽 Tri des vidéos du plus récent au plus ancien.

    // 🔁 Récupère toutes les vidéos sous forme de tableau associatif.  
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // ❌ Affiche une erreur s'il y a un problème avec la base de données.
    echo "Erreur : " . $e->getMessage();
}

// ✅ Vérifie que le formulaire a été envoyé via méthode POST et l'utilisateur connecté.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user']['id'])) {

    // ✅ Vérifie le token CSRF pour éviter les requêtes frauduleuses.
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur CSRF : requête invalide."); // 🚨 Bloque si token invalide.
    }

    $video_url = trim($_POST['video_url'] ?? ''); // 🧼 Nettoie l'URL (trim).
    if (!empty($video_url) && filter_var($video_url, FILTER_VALIDATE_URL)) { // ✅ Vérifie que c'est bien une URL valide.

        // 📏 Longueur maximale de l'url.
        if (strlen($video_url) > 255) {
            $erreur = "L'URL est trop longue.";
        } else {

            // 🔍 Vérifie si la vidéo existe déjà dans la BDD.
            $check = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE url = ?");
            $check->execute([$video_url]);
            $alreadyExists = $check->fetchColumn() > 0;
            
            // 🔄 Si l'utilisateur a déjà ajouté cette vidéo, on ne l'ajoute pas à nouveau.
            if (isset($_POST['ajouter'])) {
                if ($alreadyExists) {
                    $erreur = "Cette vidéo est déjà présente dans la bibliothèque.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO videos (id_utilisateur, url, date_publication) VALUES (:id_utilisateur, :url, NOW())");
                    $stmt->execute([
                        ':id_utilisateur' => $_SESSION['id'],
                        ':url' => $video_url
                    ]);
                    $_SESSION['message'] = "✅ Vidéo ajoutée avec succès.";
                    header('Location: bibliotheque.php'); // 🔁 Recharge la page.
                    exit();
                }
            }

            // 🔄 Si l'utilisateur veut supprimer la vidéo.
            if (isset($_POST['supprimer'])) {
                $role = $_SESSION['user']['role'] ?? 'user';
                if ($role === 'admin') {
                    $stmt = $pdo->prepare("DELETE FROM videos WHERE url = :url");
                    $stmt->execute([':url' => $video_url]);
                } else {
                    $stmt = $pdo->prepare("DELETE FROM videos WHERE url = :url AND id_utilisateur = :id");
                    $stmt->execute([':url' => $video_url, ':id' => $_SESSION['id']]);
                }
                $_SESSION['message'] = "🗑 Vidéo supprimée avec succès.";
                header('Location: bibliotheque.php');
                exit();
            }
        }
    } else {
        $erreur = "URL invalide."; // ❌ Format invalide.
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<title>Team HAKAI - Bibliothèque</title>

<!-- ✅ Icône de l'onglet (favicon) -->
<link rel="icon" href="../img/favicon.ico" type="image/x-icon" />

<!-- ✅ Feuilles de styles du site -->
<link rel="stylesheet" href="../css/base.css" />
<link rel="stylesheet" href="../css/bibliotheque.css" />
<link rel="stylesheet" href="../css/responsive.css" />
</head>
<body>
    <!-- ======================= -->
    <!-- ✅ En-tête de la page -->
    <!-- ======================= -->  
<header class="entête">
    <video class="brume" src="../img/Brume.mp4" autoplay muted loop></video>
    <h1>Clip video des membres <span class="hakai">"HAKAI"</span></h1>

    <!-- ✅ Menu de navigation principal -->
    <nav id="menu1" aria-label="navigation principale">
        <a class="nav-link accueil" href="../index.php">Accueil</a>
        <a class="nav-link membres" href="membre.php">Membre</a>
        <a class="nav-link boutique" href="boutique.php">Boutique</a>
    </nav>

    <!-- ✅ Images décoratives -->
    <img class="animation-balle" src="../img/balle.png" alt="image d une balle" />
    <img class="menuburger" src="../img/menu-burger-removebg-preview.png" alt="menu burger" role="button"/>
</header>

<!-- ✅ Message de confirmation (ex : après une action réussie) -->
<?php if (!empty($_SESSION['message'])): ?>
    <div class="message-confirmation">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<!-- Contenu principal -->
<main role="main" aria-label="page de la bibliothèque">

    <!-- Si utilisateur connecté -->
    <?php if (isset($_SESSION['user']['id'])): ?>
        <div class="titre-container">
        <button id="toggle-form" type="button" title="Afficher ou cacher le formulaire">Ajouter / Supprimer</button>
        </div>
    <?php endif; ?>

    <!-- Conteneur des vidéos (rempli via JS) -->
    <section class="bibliotheque-grid"></section>

    <template id="bibliotheque-template">
        <div class="bibliotheque">
        <div class="video"></div> <!-- Contiendra la balise <video> ou <iframe> -->
        <div class="infos">
            <div class="pseudo"><p></p></div> <!-- Pseudo de l'auteur -->
            <div class="like-container" data-id="">  <!-- Like + compteur -->
            <span class="like-icon">👍</span>
            <span class="like-count">0</span>
            </div>
            <div class="date"><p></p></div> <!-- Date de publication -->
            <div class="select-checkbox" style="display: none;">  <!-- Case pour suppression -->
            <input type="checkbox" class="delete-checkbox" title="Sélectionner pour suppression">
            </div>
        </div>
        </div>
    </template>

    <!-- Si une erreur existe -->
    <?php if (!empty($erreur)): ?>
        <div class="erreur" style="color:red;"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <!-- Si utilisateur connecté -->
    <?php if (isset($_SESSION['user']['id'])): ?>
        <section id="video-form" class="formulaire-section">
        <h2>Ajouter / Supprimer une vidéo</h2>
        <button type="button" class="close-btn" title="Fermer">✖</button>
        <form method="post" action="bibliotheque.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="url" name="video_url" id="video-url" placeholder="URL de la vidéo" required>
            <button type="submit" name="ajouter">Ajouter</button>
            <button type="submit" name="supprimer">Supprimer</button>
        </form>
        </section>
    <?php endif; ?>

    <footer role="contentinfo" aria-label="Pied de page">
        <p>&copy; 2024 Team HAKAI - Tous droits réservés.</p>
        <a href="../php/mention.php">Mentions légales</a> |
        <a href="../php/politique.php">Politique de confidentialité</a>
    </footer>
</main>

<script>
    const videos = <?= json_encode($videos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>; // Tableau de toutes les vidéos (depuis PHP)
    const currentUser = <?= isset($_SESSION['user']['pseudo']) ? json_encode($_SESSION['user']['pseudo']) : 'null' ?>; // Pseudo de l'utilisateur connecté
    const currentUserRole = <?= isset($_SESSION['user']['role']) ? json_encode($_SESSION['user']['role']) : 'null' ?>; // Rôle (admin/user)
    const csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>; // Token CSRF pour la sécurité
</script>

<!-- Script principal JavaScript -->
<script src="../js/main.js"></script>
</body>
</html>