<?php
// Démarre la session : nécessaire pour utiliser $_SESSION[]
session_start();

// Inclut la connexion PDO à la base de données
require_once 'config.php';
    
$membres = []; // Initialise un tableau vide pour y stocker les membres


try {
    // Prépare une requête SQL qui sélectionne les champs nécessaires pour l'affichage des membres
    $stmt = $pdo->query("SELECT id, pseudo, photo_profil, style_jeu, age, genre, biographie FROM utilisateurs ORDER BY date_inscription DESC");
    if ($stmt) {
        $membres = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Récupère tous les résultats sous forme de tableau associatif
    }
} catch (PDOException $e) {
    // Affiche une erreur si la connexion ou la requête échoue
    echo "Erreur lors de la récupération des membres : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team HAKAI</title>

    <!-- ✅ Icône de l’onglet (favicon) -->
    <link rel="icon" href="../img/favicon.ico" type="image/x-icon" />

    <!-- ✅ Feuilles de styles du site -->
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/membre.css">
    <link rel="stylesheet" href="../css/responsive.css">

    <!-- ✅ Intégration de Font Awesome (icônes etc) -->
    <script src="https://kit.fontawesome.com/694cc6b3bf.js" crossorigin="anonymous"></script>
</head>
<body>

<!-- ======================= -->
<!-- ✅ En-tête de la page --->
<!-- ======================= -->

    <header class="entête">
            <video class="brume" src="../img/Brume.mp4" autoplay muted loop></video>   
            <h1>Presentation des membres <span class="hakai">"HAKAI"</span></h1>

            <!-- ✅ Menu de navigation principal -->
            <nav id="menu1" aria-label="Menu principal">
                <a class="nav-link accueil" href="../index.php" title="Aller à l'accueil">Accueil</a>
                <a class="nav-link bibliotheque" href="../php/bibliotheque.php" title="Voir la bibliothèque">Bibliotheque</a>
                <a class="nav-link boutique" href="../php/boutique.php" title="Accéder à la boutique">Boutique</a>
            </nav>

                <!-- ✅ Images décoratives -->
                <img class="animation-balle" src="../img/balle.png" alt="image d une balle" aria-hidden="true">
                <img class="menuburger" src="../img/menu-burger-removebg-preview.png" alt="ouvrir le menu" aria-label="Menu burger" role="button" />
    </header>

<!-- ✅ Message de confirmation (ex : après une action réussie) -->
<?php if (!empty($_SESSION['message'])): ?>
    <div class="message-confirmation">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<!-- Contenu principal -->
<main role="main" aria-label="page des membres">

    <!-- Gabarit invisible qui sera cloné via JavaScript -->
    <template id="membre-template">
        <div class="membre" role="region" aria-label="Carte membre"> <!-- Carte individuelle d’un membre -->
        <div class="photo">
        <img src="" alt="photo de profil du membre"> <!-- Image de profil (remplie en JS) -->
        </div>
        <div class="descriptif">
        <div class="pseudo"><span>Pseudo</span></div> <!-- Pseudo -->
        <div class="info">
            <div class="style"><span>Style de jeu</span></div> <!-- Style de jeu -->
            <div class="age"><span>Âge</span></div> <!-- Âge -->
            <div class="genre"><span>Genre</span></div> <!-- Genre -->
        </div>
        <div class="profil">
            <span>Biographie</span> <!-- Biographie (texte libre) -->
        </div>
        </div>
        </div>
    </template>

<!-- Grille où les cartes membres seront ajoutées -->
<section class="membre-grid" aria-label="Liste des membres"></section>

<footer role="contentinfo" aria-label="Pied de page">
    <p>&copy; 2024 Team HAKAI - Tous droits réservés.</p>
    <a href="../php/mention.php">Mentions légales</a> |
    <a href="../php/politique.php">Politique de confidentialité</a>
</footer>
</main>

<script>
    const membres = <?= json_encode($membres, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>; // Les membres récupérés en PHP, transmis au JS
    const currentUserRole = <?= json_encode($_SESSION['user']['role'] ?? '') ?>; // Rôle de l’utilisateur connecté
    const csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>; // Token CSRF pour les requêtes JS
    const currentUser = <?= json_encode($_SESSION['user']['pseudo'] ?? '') ?>; // Pseudo de l’utilisateur connecté
</script>

<!-- Inclusion du fichier JavaScript principal -->
<script src="../js/main.js"></script>
</body>
</html>