<?php

// Start the session securely
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

require '../login/db.php'; // Connexion à la base
require "./scriptes/autorisation.php"; // inclut le fichier autorisation.php
?>