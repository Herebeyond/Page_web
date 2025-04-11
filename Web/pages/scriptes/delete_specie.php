<?php


require_once '../../login/db.php'; // Connexion à la base

header('Content-Type: application/json');

if (isset($_GET['specie'])) {
    $specieName = trim($_GET['specie']);
    $stmt = $pdo->prepare("DELETE FROM species WHERE specie_name = ?;");
    $stmt->execute([$specieName]);

    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No specie specified'
    ]);
}
