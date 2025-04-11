<?php

namespace Login;

use PDO;
use PDOException;

// Load environment variables from the .env file
if (file_exists(__DIR__ . '/../BDD.env')) {
    $dotenv = parse_ini_file(__DIR__ . '/../BDD.env');
    foreach ($dotenv as $key => $value) {
        putenv("$key=$value");
    }
}

// Get database credentials from environment variables
$host = getenv('DB_HOST') ?: '';
$dbname = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur de connexion : " . $e->getMessage());
    exit('Erreur de connexion à la base de données');
}
?>
