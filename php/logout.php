<?php
// ✅ Démarre la session pour pouvoir la supprimer ensuite.
session_start();

// ✅ Supprime toutes les variables de session (pseudo, id, etc.).
$_SESSION = [];

// ✅ Détruit complètement la session sur le serveur.
session_destroy();

// 🔁 Redirige l'utilisateur vers la page d'accueil (ou login).
header("Location: ../index.php");
exit(); // ❗ Toujours mettre exit() après un header.

