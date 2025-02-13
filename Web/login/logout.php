<?php
session_start();
session_destroy(); // Détruit toutes les données de session

// Redirige l'utilisateur vers la page d'accueil
header('Location: ../pages/Accueil.php');
exit;
?>