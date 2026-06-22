<?php
// Démarre la session pour gérer l'utilisateur connecté et les variables de session
session_start();

 // Si le token CSRF n'existe pas encore, on le créede manière sécurisée.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// On inclut la configuration de la base de données (connexion PDO).
require_once 'config.php';

 // Bloc try pour capturer une éventuelle erreur PDO.
try {
    $stmt = $pdo->query("SELECT * FROM articles ORDER BY date_ajout DESC"); // Requête : récupère tous les articles triés par date d'ajout (du plus récent au plus ancien)
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC); // On stocke les résultats sous forme de tableau associatif
} catch (PDOException $e) {
    $_SESSION['message'] = "Une erreur est survenue. Veuillez réessayer.";
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Team HAKAI - Boutique</title>

  <!-- ✅ Icône de l'onglet (favicon) -->
  <link rel="icon" href="../img/favicon.ico" type="image/x-icon" />

  <!-- ✅ Feuilles de styles du site -->
  <link rel="stylesheet" href="../css/base.css" />
  <link rel="stylesheet" href="../css/boutique.css" />
  <link rel="stylesheet" href="../css/responsive.css" />

  <!-- ✅ Intégration de Font Awesome (icônes etc) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
</head>
<body>

<!-- ======================= -->
<!-- ✅ En-tête de la page --->
<!-- ======================= -->

<header class="entête">
  <video class="brume" src="../img/Brume.mp4" autoplay muted loop></video>
  <h1>Presentation de la Boutique <span class="hakai">"HAKAI"</span></h1>

  <!-- ✅ Menu de navigation principal -->
  <nav id="menu1" aria-label="Menu principal">
    <a class="nav-link accueil" href="../index.php">Accueil</a>
    <a class="nav-link membres" href="membre.php">Membre</a>
    <a class="nav-link bibliotheque" href="bibliotheque.php">Bibliotheque</a>
  </nav>

  <!-- ✅ Images décoratives -->
  <img class="animation-balle" src="../img/balle.png" alt="balle animée" />
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
<main role="main" aria-label="page de la boutique">

  <div id="Pan">
    <i class="fa-solid fa-cart-shopping panier" role="button" aria-label="panier"></i> <!-- Icône du panier -->
    <div class="notif"></div> <!-- Notification de nombre d'articles (remplie dynamiquement en JS) -->
  </div>

<!-- Si admin connecté -->
<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
  <form method="POST" action="action_admin_boutique.php" class="admin-barre">

    <!-- Protection CSRF -->
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <!-- Actions de groupe -->
    <div class="admin-actions-globales">
      <a href="ajout_article.php" class="ajout-btn">➕ Ajouter</a>
      <button type="submit" name="action" class="admin-btn" value="modifier">✏️ Modifier</button>
      <button type="submit" name="action" class="admin-btn-supprimer" value="supprimer" onclick="return confirm('Supprimer les articles sélectionnés ?')">🗑 Supprimer</button>
    </div>
<?php endif; ?>

  <!-- Grille d'affichage des articles -->
  <section class="boutique-grid">

  <?php if (!empty($articles)) : ?> <!-- Si des articles sont présents -->
    <?php foreach ($articles as $article) : ?> <!-- Boucle sur chaque article -->
      <div class="article"> <!-- Carte individuelle -->

        <!-- Pour modifier ou supprimer -->
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
          <input type="radio" name="articles_id" value="<?= $article['id_article'] ?>">
        <?php endif; ?>

        <img src="../img/<?= htmlspecialchars($article['image'] ?? '') ?>" alt="<?= htmlspecialchars($article['nom_article'] ?? 'article') ?>">
        <h2><?= htmlspecialchars($article['nom_article'] ?? 'Nom manquant') ?></h2>
        <p><?= htmlspecialchars($article['prix'] ?? '0.00') ?> €</p>

        <div class="actions">
          <input type="number" class="chiffre" value="1" min="1">
          <button type="button" class="add-btn"
            data-id="<?= htmlspecialchars($article['id_article'] ?? '') ?>"
            data-nom="<?= htmlspecialchars($article['nom_article'] ?? '') ?>"
            data-prix="<?= htmlspecialchars($article['prix'] ?? '') ?>"
            data-image="<?= htmlspecialchars($article['image'] ?? '') ?>">
            Ajouter
          </button>
          <button type="button" class="remove-btn"
            data-id="<?= htmlspecialchars($article['id_article'] ?? '') ?>"
            data-nom="<?= htmlspecialchars($article['nom_article'] ?? '') ?>"
            data-prix="<?= htmlspecialchars($article['prix'] ?? '') ?>">
            Supprimer
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else : ?> <!-- Aucun article -->
    <p>Aucun article disponible.</p>
  <?php endif; ?>
</section>

<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
  </form>
<?php endif; ?>

<!-- ============================= -->
<!-- ✅ FORMULAIRE DE COMMANDE -->
<!-- ============================= -->

  <div class="shop" id="bon-de-commande" style="display: none"> <!-- Conteneur masqué par défaut -->
    <div class="close-btn" role="button" aria-label="fermer">&times;</div> <!-- Bouton de fermeture -->
    <h3>Bon de Commande</h3>
    <form id="commande-form" method="POST" action="traitement_commande.php" target="_blank"> <!-- Formulaire -->
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <div class="renseignement">
        <div>
          <label for="nom">Nom :</label>
          <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required />
          <label for="prenom">Prénom :</label>
          <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required />
          <label for="telephone">Téléphone :</label>
          <input type="tel" id="telephone" name="telephone" placeholder="+33 6 98 76 54 32" required />
        </div>
        <div>
          <label for="email">Email :</label>
          <input type="email" id="email" name="email" placeholder="jean.dupont@email.com" required />
          <label for="adresse">Adresse :</label>
          <input type="text" id="adresse" name="adresse" placeholder="456 Avenue des Clients, 69000 Lyon" required />
          <label for="point-relais">Point relais :</label>
          <select id="point-relais" name="point-relais">
            <option value="relais1">Relais Lyon Centre</option>
            <option value="relais2">Relais Part-Dieu</option>
            <option value="relais3">Relais Perrache</option>
            <option value="relais4">Relais Villerupt</option>
          </select>
        </div>
      </div>

<!-- ============================= -->
<!-- ✅ TABLEAU RECAPITULATIF -->
<!-- ============================= -->

      <table>
        <thead>
          <tr>
            <th class="art">Article</th>
            <th class="Qte">Quantité</th>
            <th class="Montant">Total</th>
          </tr>
        </thead>
        <tbody id="panier-table"></tbody> <!-- Rempli dynamiquement -->
      </table>

      <div class="total">
        <p><strong>Total Panier :</strong> <span id="total-price">0</span> euro</p>
      </div>
      <div class="valid">
        <!-- <button type="submit" class="previsualiser" name="action" value="preview">🔍 Prévisualiser</button> -->
        <button type="submit" class="valider" name="action" value="send">📩 Valider la commande</button>
      </div>
    </form>
  </div>

  <footer role="contentinfo" aria-label="Pied de page">
    <p>&copy; 2024 Team HAKAI - Tous droits réservés.</p>
    <a href="../php/mention.php">Mentions légales</a> |
    <a href="../php/politique.php">Politique de confidentialité</a>
  </footer>
</main>

<!-- Fichier JS global qui gère l'ajout/suppression au panier -->
<script src="../js/main.js"></script>
</body>
</html>
