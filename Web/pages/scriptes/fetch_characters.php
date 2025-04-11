<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../blueprints/page_init.php";

header('Content-Type: application/json'); // Définir le type de contenu comme JSON

if (isset($_GET['race_id']) && is_numeric($_GET['race_id'])) {
    $raceId = (int)$_GET['race_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, race_name 
            FROM characters c
            LEFT JOIN races r ON r.id_race = c.correspondence
            LEFT JOIN species s ON s.id_specie = r.correspondence
            WHERE r.id_race = ?
        ");
        $stmt->execute([$raceId]);
        $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($characters); // Renvoie les résultats sous forme de JSON
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid race ID']);
}
?>