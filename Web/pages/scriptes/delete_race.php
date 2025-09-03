<?php


require_once '../../database/db.php'; // Connexion à la base

header('Content-Type: application/json');

if (isset($_GET['race'])) {
    $raceName = trim($_GET['race']);
    $stmt = $pdo->prepare("DELETE FROM races WHERE race_name = ?;");
    $stmt->execute([$raceName]);

    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No specie specified'
    ]);
}
