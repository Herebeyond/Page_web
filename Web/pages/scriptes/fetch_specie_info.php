<?php
require '../../login/db.php'; // Connexion à la base

header('Content-Type: application/json');

if (isset($_GET['specie'])) { // Make the function fetchSpecieInfo work
    $specieName = trim($_GET['specie']);
    $stmt = $pdo->prepare("SELECT icon_Specie, content_Specie, specie_is_unique FROM species WHERE specie_name = ?");
    $stmt->execute([$specieName]);
    $specie = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($specie) {
        echo json_encode([
            'success' => true,
            'icon' => $specie['icon_Specie'],
            'content' => $specie['content_Specie'],
            'unique' => $specie['specie_is_unique']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Specie not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No specie specified'
    ]);
}
?>
