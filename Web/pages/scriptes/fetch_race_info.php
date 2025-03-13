<?php
require '../../login/db.php';

header('Content-Type: application/json');

if (isset($_GET['race'])) { // Make the function fetchRaceInfo work
    $raceName = trim($_GET['race']);
    $stmt = $pdo->prepare("SELECT correspondence, icon_Race, content_Race, lifespan, homeworld, country, habitat, race_is_unique FROM races WHERE race_name = ?");
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
            'habitat' => $race['habitat'],
            'unique' => $race['race_is_unique'],
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