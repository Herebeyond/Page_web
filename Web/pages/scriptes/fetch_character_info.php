<?php

require_once '../../login/db.php';

header('Content-Type: application/json');

if (isset($_GET['character'])) { // Make the function fetchCharacterInfo work
    $characterName = trim($_GET['character']);
    error_log("Received character: " . $characterName); // Log the received character name

    try {
        $stmt = $pdo->prepare("SELECT character_name, characters.correspondence, characters.habitat, icon_character, characters.age, characters.country, race_name 
                               FROM characters 
                               INNER JOIN races ON characters.correspondence = races.id_race 
                               WHERE character_name = ?");
        $stmt->execute([$characterName]);
        $character = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($character) {
            echo json_encode([
                'success' => true,
                'character_name' => $character['character_name'],
                'correspondence' => $character['race_name'], // Corresponding race name
                'habitat' => $character['habitat'],
                'icon' => $character['icon_character'],
                'age' => $character['age'],
                'country' => $character['country'],
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Character not found'
            ]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage()); // Log database errors
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No character specified'
    ]);
}
?>