<?php
require '../../login/db.php'; // Connexion à la base

header('Content-Type: application/json');

if (isset($_GET['race'])) {
    $raceName = trim($_GET['race']);
    $stmt = $pdo->prepare("SELECT correspondence, icon_Race, content_Race, lifespan, homeworld, country, habitat FROM races WHERE race_name = ?");
    $stmt->execute([$raceName]);
    $race = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($race) {
        echo json_encode([
            'success' => true,
            'correspondence' => $race['correspondence'],
            'icon' => $race['icon_Race'],
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