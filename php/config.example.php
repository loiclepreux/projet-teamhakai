<?php
// ====================================
// 🔧 Configuration base de données  =
// ====================================
// Copiez ce fichier en config.php et remplissez vos vraies valeurs.
// NE JAMAIS commiter config.php dans git.

$host    = 'votre-hote-mysql';       // Ex: mysql-xxx.alwaysdata.net
$dbname1 = 'votre_base_de_donnees';  // Ex: utilisateur_hakai
$username = 'votre_utilisateur';     // Identifiant MySQL
$password = 'votre_mot_de_passe';    // Mot de passe MySQL

// Configuration mail (utilisez un mot de passe d'application Gmail)
define('MAIL_USER', 'votre@email.com');
define('MAIL_PASS', 'xxxx xxxx xxxx xxxx'); // Mot de passe d'application Gmail

// Connexion PDO
$dsn = "mysql:host=$host;dbname=$dbname1;charset=utf8";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    exit('Erreur de connexion à la base de données.');
}
?>
