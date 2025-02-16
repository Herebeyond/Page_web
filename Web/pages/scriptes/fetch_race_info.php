<?php
require '../../login/db.php'; // Connexion à la base

header('Content-Type: application/json');

if (isset($_GET['race'])) {
    $raceName = trim($_GET['race']);
    $stmt = $pdo->prepare("SELECT correspondance, icon_Race, icon_Type_Race, content_Race, lifespan, homeworld, country, habitat FROM races WHERE nom_Race = ?");
    $stmt->execute([$raceName]);
    $race = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($race) {
        echo json_encode([
            'success' => true,
            'correspondance' => $race['correspondance'],
            'icon' => $race['icon_Race'],
            'icon_type' => $race['icon_Type_Race'],
            'content' => $race['content_Race'],
            'lifespan' => $race['lifespan'],
            'homeworld' => $race['homeworld'],
            'country' => $race['country'],
            'habitat' => $race['habitat']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Race not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No race specified'
    ]);
}
?>