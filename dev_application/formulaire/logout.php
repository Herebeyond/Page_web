<?php
session_start();
session_destroy(); // Détruit toutes les données de session
echo "Vous êtes déconnecté.";
header('Location: page.php'); // Redirige l'utilisateur vers la page d'accueil
?>