<?php
$host = 'db';
$dbname = 'univers';
$username = 'root';
$password = 'root_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur de connexion : " . $e->getMessage());
    exit('Erreur de connexion à la base de données');
}
?>