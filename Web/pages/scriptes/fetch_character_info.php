<?php
// Disable all HTML error output for clean JSON responses
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../../database/db.php';

// Verify database connection was successful  
if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

header('Content-Type: application/json');
// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if (isset($_GET['character'])) { // Make the function fetchCharacterInfo work
    $characterName = trim($_GET['character']);
    
    // Validate character name input (security: prevent injection attempts)
    if (empty($characterName) || strlen($characterName) > 255) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid character name parameter'
        ]);
        exit;
    }
    
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
        error_log("Database error in fetch_character_info.php: " . $e->getMessage()); // Log database errors
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No character specified'
    ]);
}
?>