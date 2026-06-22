<?php
// ✅ Démarrage de la session (obligatoire pour utiliser $_SESSION).
session_start();

// ✅ Génération du token CSRF s'il n'existe pas encore.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Crée une chaîne sécurisée de 64 caractères.
}

// ✅ Inclusion du fichier de configuration de la base de données.
require 'php/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Team HAKAI</title>

  <!-- ✅ Icône de l'onglet (favicon) -->
  <link rel="icon" href="img/favicon.ico" type="image/x-icon" />

  <!-- ✅ Feuilles de styles du site -->
  <link rel="stylesheet" href="css/base.css" />
  <link rel="stylesheet" href="css/accueil.css" />
  <link rel="stylesheet" href="css/responsive.css" />

  <!-- ✅ Intégration de Font Awesome (icônes) -->
  <script src="https://kit.fontawesome.com/694cc6b3bf.js" crossorigin="anonymous"></script>
  <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/4.0.0/model-viewer.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

  <!-- ======================= -->
  <!-- ✅ En-tête de la page -->
  <!-- ======================= -->
  
  <header>
    <div class="entête">
      <video class="brume" src="img/Brume.mp4" autoplay muted loop></video>
      <h1>BIENVENUE CHEZ LA TEAM <span class="hakai">"HAKAI"</span></h1>

      <!-- ✅ Menu de navigation principal -->
      <nav id="menu1" aria-label="Menu principal">
        <a href="#" class="nav-link seconnecter" id="open-login">Connexion</a>
        <a class="nav-link membres" href="php/membre.php">Membre</a>
        <a class="nav-link bibliotheque" href="php/bibliotheque.php">Bibliotheque</a>
        <a class="nav-link boutique" href="php/boutique.php">Boutique</a>
      </nav>

        <!-- ✅ Images décoratives -->
        <img class="animation-balle" src="img/balle.png" alt="image d une balle animée" />
        <img class="menuburger" src="img/menu-burger-removebg-preview.png" alt="menuburger" aria-label="menu burger" role="button"/>
    </div>
  </header>

<!-- ✅ Message de confirmation (ex : après une action réussie) -->
<?php if (!empty($_SESSION['message'])): ?>
  <div class="message-confirmation">
    <?= htmlspecialchars($_SESSION['message']) ?>
    <?php unset($_SESSION['message']); ?>
  </div>
<?php endif; ?>

  <!-- ✅ Si l'utilisateur ou l administrateur est connecté, on affiche son pseudo et son avatar -->
  <?php if (isset($_SESSION['user'])): ?>
  <div class="profil-connecte">
    <p>Bienvenue, <strong><?= htmlspecialchars($_SESSION['user']['pseudo']) ?></strong> !</p>
    <p style="font-style: italic;">Rôle : <?= htmlspecialchars($_SESSION['user']['role']) ?></p>

    <!-- ✅ Affichage de la photo de profil -->
    <div class="avatar">
      <img src="php/profils/<?= htmlspecialchars($_SESSION['user']['photo_profil']) ?>" alt="Photo de profil" width="95%" height="85%">
    </div>

    <!-- ✅ Formulaire de déconnexion -->
    <form class="deconnexion" method="POST" action="php/logout.php">
      <button type="submit" class="logout-btn">déconnexion</button>
    </form>
  </div>
<?php endif; ?>

<!-- ============================ -->
<!-- ✅ Section de CONNEXION -->
<!-- ============================ -->

  <main class="principale" role="main" aria-label="Contenu principal">
    <section id="connexion-section" aria-label="Connexion">
      <form method="POST" action="php/login.php">
        <!-- ✅ Protection CSRF -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <h4>Connexion</h4>

        <!-- Champ pseudo -->
        <?php
          if (isset($_COOKIE['remember_me'])) {
                $remember_me = $_COOKIE['remember_me'];
            } else {
                $remember_me = '';
            }
            ?>
        <div class="input-box">
          <input type="text" value="<?= htmlspecialchars($remember_me) ?>" name="username" placeholder="Pseudo Gamertag" required />
          <i class="fa-solid fa-user"></i>
        </div>

        <!-- Champ mot de passe -->
        <div class="input-box">
          <input type="password" name="mot_de_passe" placeholder="Mot de passe" required />
          <i class="fa-solid fa-lock"></i>
        </div>
        <div class="remember-forgot">
          <div id="Remb">
            <input type="checkbox" name="rapel" /><label> Se souvenir de moi</label>
          </div>
          <a href="php/recup_mdp.php">Mot de passe oublié ?</a>
        </div>

        <!-- Boutons -->
        <div class="register-link">
          <button type="submit" class="login-btn">Se connecter</button>
          <p>Vous n'avez pas de compte ?</p>
          <button id="show-signup" class="login-btn">Inscrivez-vous</button>
        </div>
      </form>
    </section>

<!-- ============================= -->
<!-- ✅ FORMULAIRE D'INSCRIPTION -->
<!-- ============================= -->

    <div class="form-container" aria-label="Formulaire d'inscription">

      <!-- Affichage d'un message d'erreur (ex : pseudo déjà pris) -->
      <?php if (!empty($erreur)) : ?>
        <p class="erreur" style="color: red"><?= htmlspecialchars($erreur) ?></p>
      <?php endif; ?>

      <form id="inscription-section" method="POST" action="php/register.php" enctype="multipart/form-data">
        <!-- ✅ Protection CSRF -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- Bouton pour fermer le formulaire (croix) --> 
        <div class="close-btn" role="button" aria-label="Fermer le formulaire">&times;</div>

        <h4>Inscription</h4>

        <!-- 🧾 Partie 1 : Identification -->
        <fieldset>
          <legend><span class="number">1</span>Identification</legend>
          <label for="pseudo">Pseudo</label>
          <input type="text" id="pseudo" name="pseudo" required />

          <label for="email">Email</label>
          <input type="email" id="email" name="email" required />

          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="mot_de_passe" required />

          <label for="">Genre</label>
          <input type="radio" name="genre" value="homme" id="homme" />
          <label class="light" for="homme">Homme</label>

          <input type="radio" name="genre" value="femme" id="femme" />
          <label class="light" for="femme">femme</label>
        </fieldset>

        <!-- 🧾 Partie 2 : Profil joueur -->
        <fieldset>
          <legend><span class="number">2</span>Profil</legend>
          <label for="fonction">Mode de jeux:</label>

          <input type="checkbox" name="warzone" id="warzone" value="warzone" />
          <label class="light" for="warzone">Warzone</label>

          <input type="checkbox" name="multijoueur" id="multijoueur" value="multijoueur" />
          <label class="light" for="multijoueur">Multijoueur</label>

          <input type="checkbox" name="zombie" id="zombie" value="zombie" />
          <label class="light" for="zombie">Zombie</label>

          <input type="checkbox" name="campagne" id="campagne" value="campagne" />
          <label class="light" for="campagne">Campagne</label>

          <label for="age">Age</label>
          <input type="number" id="age" name="age" required />

          <label for="photo_profil">Photo de profil</label>
          <input type="file" name="photo_profil" id="profile-img" accept="image/*" />

          <label for="plateforme">Plateforme</label>
          <select name="plateforme" id="plateforme">
            <option value="xbox">Xbox</option>
            <option value="play">Playstation</option>
            <option value="pc">PC</option>
          </select>

          <label for="biographie">Biographie</label>
          <textarea name="biographie" id="biographie"></textarea>

          <label for="style">Style de jeux</label>
          <select name="style_jeu" id="style">
            <option value="rusheur">Rusheur</option>
            <option value="campeur">Campeur</option>
            <option value="sniper">Sniper</option>
            <option value="tacticien">Tacticien</option>
            <option value="ninja">Ninja</option>
            <option value="support">Support</option>
          </select>
        </fieldset>

        <!-- ✅ Bouton pour valider l'inscription -->
        <div id="valider">
            <button type="submit">Valider</button>
        </div>
      </form>
    </div>

    <!-- Section d'intro avec logo et citation -->
    <section id="introduction">
      <model-viewer alt="logo3D" class="logo" src="img/logo4.glb" shadow-intensity="1" style="width: 45%; height: 25vh" environment-image="neutral" autoplay auto-rotate auto-rotate-delay="0" rotation-per-second="90deg"></model-viewer>
      <h3>
        "Nos débuts ainsi que notre parcours d'amusement avec de nombreuses
        rencontres effectuées, se sont faits parmi énormément de jeux" !!!!!
      </h3>
      <model-viewer alt="logo3D" class="logo" src="img/logo4.glb" shadow-intensity="1" style="width: 45%; height: 25vh" environment-image="neutral" autoplay auto-rotate auto-rotate-delay="0" rotation-per-second="90deg"></model-viewer>
    </section>
    
    <!-- Carrousel d'images des jeux COD -->
    <section id="carousel" ria-label="Carrousel des jeux Call of Duty">
      <div class="carousel" role="region" aria-live="polite">
        <!-- Chaque image représente un jeu Call of Duty -->
        <img class="cod activ" src="img/bo1.png" alt="image bo1" />
        <img class="cod" src="img/bo2.png" alt="image bo2" />
        <img class="cod" src="img/bo3.png" alt="image bo3" />
        <img class="cod" src="img/bo4.png" alt="image bo4" />
        <img class="cod" src="img/bo5.png" alt="image bo5" />
        <img class="cod" src="img/bo6.png" alt="image bo6" />
        <img class="cod" src="img/mw2.png" alt="image mw2" />
        <img class="cod" src="img/mw3.png" alt="image mw3" />
        <img class="cod" src="img/warzone 1.jpg" alt="image warzone" />
        <img class="cod" src="img/vanguard.png" alt="image vanguard" />
        <img class="cod" src="img/moderne warfare 1.jpg" alt="image moderne warfare" />
      </div>
    </section>

    <!-- Section de présentation complète de la communauté -->
    <article id="presentation" role="article">

      <h2>Présentation de l'association HAKAI</h2>
      <!-- Description du but et de l'esprit de la team -->
      <p>
        Nous avons créé le groupe HAKAI pour que chacun puisse toujours
        trouver quelqu'un avec qui jouer. Notre association regroupe des
        joueurs de tous horizons, aux profils variés, avec des horaires de jeu
        différents. Mais nous avons tous un point commun : la passion du jeu
        et le plaisir de partager des moments inoubliables ensemble. Nous
        existons depuis maintenant trois ans et demi, et au fil du temps, nous
        avons construit une véritable communauté soudée. Ici, le respect, la
        bonne humeur et l'amusement sont nos priorités. Que ce soit en pleine
        action ou entre deux parties, nous aimons échanger, rire, et bien sûr,
        nous chambrer dans une ambiance bon enfant !
      </p>

      <h2>Nos activités et événements</h2>
      <!-- Description des PP (parties privées) et types d'événements organisés -->
      <p>
        Notre particularité ? Nous adorons organiser des parties privées (PP),
        qui sont devenues un rituel au sein du groupe. Chaque mois, les
        modérateurs mettent en place ces sessions spéciales où toute l'équipe
        HAKAI peut se retrouver. Ces moments sont l'occasion idéale de tester
        nos compétences, de défier nos amis et de créer des souvenirs
        mémorables. Courir après un membre de la team, un ami, le surprendre
        et décrocher cette élimination tant espérée… Rien de tel pour
        déclencher des fous rires et des échanges pleins de taquineries ! Les
        PP sont aussi un excellent moyen d'améliorer notre gameplay tout en
        restant dans une ambiance détendue et conviviale. En plus des parties
        privées, nous proposons :
      </p>

      <!-- Liste des activités proposées -->
      <ul>
        <li>Des tournois internes pour ceux qui aiment la compétition.</li>
        <li>
          Des entraînements pour progresser ensemble et partager des conseils.
        </li>
        <li>
          Des soirées à thème pour découvrir de nouveaux modes de jeu et
          s'amuser autrement.
        </li>
      </ul>

      <h2>Pourquoi nous rejoindre ?</h2>
      <!-- Avantages de rejoindre la team -->
      <p>
        Être membre de HAKAI, c'est bien plus que jouer ensemble. C'est faire
        partie d'une véritable famille de gamers où chacun peut trouver sa
        place. Que tu sois un joueur casual ou un compétiteur acharné, tu
        trouveras toujours quelqu'un avec qui partager une partie, des
        conseils, ou tout simplement un bon moment de discussion. Avec nous,
        tu pourras :
      </p>

      <ul>
        <li>✔ Toujours avoir des coéquipiers motivés.</li>
        <li>✔ Partager des expériences et des fous rires inoubliables.</li>
        <li>✔ Évoluer dans une communauté respectueuse et dynamique.</li>
        <li>✔ Participer à des événements exclusifs chaque mois.</li>
      </ul>

      <h2>Comment nous rejoindre ?</h2>
      <!-- Invitation à rejoindre la team via Discord ou autre plateforme -->
      <p>
        Si tu veux faire partie de l'aventure <strong>HAKAI</strong>, rien de
        plus simple ! Rejoins notre
        <span class="highlight">serveur Discord</span> (ou notre groupe) et
        viens échanger avec nous. Peu importe ton niveau ou ton style de jeu,
        tant que tu es là pour t'amuser et respecter l'esprit de la
        communauté, <strong>tu es le bienvenu !</strong>
        <span>Nous sommes présent sur ces plateformes:</span>

        <!-- Icônes des plateformes disponibles -->
        <span class="inline-icons">
          <img class="plateform-icon" id="xbox" src="img/xbox-removebg-preview.png" alt="xbox" />
          <img class="plateform-icon" id="play" src="img/play-removebg-preview.png" alt="play" />
          <img class="plateform-icon" id="pc" src="img/pc-removebg-preview.png" alt="pc" />
        </span>
      </p>
    </article>

    <footer role="contentinfo" aria-label="Pied de page">
      <p>&copy; 2024 Team HAKAI - Tous droits réservés.</p>
      <nav aria-label="Liens légaux">
        <a href="php/mention.php">Mentions légales</a> |
        <a href="php/politique.php">Politique de confidentialité</a>
      </nav>
    </footer>

    <div class="reseaux">
      <div class="carte"><i class="fa-brands fa-discord"></i></div>
      <div class="carte"><i class="fa-brands fa-facebook"></i></div>
      <div class="carte"><i class="fa-brands fa-twitch"></i></div>
    </div>
  </main>

  <!-- Inclusion du fichier JavaScript principal -->
  <script src="js/main.js"></script>
</body>
</html>