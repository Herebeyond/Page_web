<?php
// Character Admin Interface - AJAX endpoint for managing characters
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set error handler for JSON responses
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'PHP Error: ' . $message]);
    exit;
});

// Set exception handler for JSON responses
set_exception_handler(function($exception) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $exception->getMessage()]);
    exit;
});

require_once __DIR__ . '/../../login/db.php';

// Verify database connection was successful  
if (!isset($pdo) || !$pdo) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Handle AJAX POST requests first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleAjaxRequest();
    exit; // Important: stop execution after handling AJAX
}

// Handle GET requests (load admin interface)
$action = isset($_GET['action']) ? $_GET['action'] : 'main';

switch ($action) {
    case 'main':
        renderMainInterface();
        break;
    default:
        renderMainInterface();
        break;
}

function renderMainInterface() {
    try {
        // Get all species and races for the form
        global $pdo;
        $speciesQuery = $pdo->prepare("SELECT * FROM species ORDER BY specie_name");
        $speciesQuery->execute();
        $species = $speciesQuery->fetchAll(PDO::FETCH_ASSOC);
        
        $racesQuery = $pdo->prepare("SELECT r.*, s.specie_name FROM races r 
                                     JOIN species s ON r.correspondence = s.id_specie 
                                     ORDER BY s.specie_name, r.race_name");
        $racesQuery->execute();
        $races = $racesQuery->fetchAll(PDO::FETCH_ASSOC);
        
        outputCharacterAdminInterface($species, $races);
    } catch (PDOException $e) {
        echo '<div class="error-message">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

function handleAjaxRequest() {
    header('Content-Type: application/json');
    
    global $pdo;
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'addCharacter':
                handleAddCharacter($pdo);
                break;
                
            case 'updateCharacter':
                handleUpdateCharacter($pdo);
                break;
                
            case 'deleteCharacter':
                handleDeleteCharacter($pdo);
                break;
                
            case 'getCharacterData':
                handleGetCharacterData($pdo);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Character admin error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
    
    exit; // Important: Stop execution to prevent any additional output
}

/**
 * Handle adding a new character
 */
function handleAddCharacter($pdo) {
    $characterName = trim($_POST['character_name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $habitat = trim($_POST['habitat'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $content = trim($_POST['content_character'] ?? '');
    $raceId = (int)($_POST['race_id'] ?? 0);
    
    if (empty($characterName)) {
        echo json_encode(['success' => false, 'message' => 'Character name is required']);
        return;
    }
    
    if ($raceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid race selection is required']);
        return;
    }
    
    // Check if character name already exists in this race
    $checkStmt = $pdo->prepare("SELECT id_character FROM characters WHERE character_name = ? AND correspondence = ?");
    $checkStmt->execute([$characterName, $raceId]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A character with this name already exists in this race']);
        return;
    }
    
    // Handle file upload if present
    $iconPath = null;
    if (isset($_FILES['icon_character']) && $_FILES['icon_character']['error'] === UPLOAD_ERR_OK) {
        $iconPath = handleCharacterIconUpload($_FILES['icon_character']);
        if ($iconPath === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload character icon']);
            return;
        }
    }
    
    // Insert new character
    $stmt = $pdo->prepare("INSERT INTO characters (character_name, age, habitat, country, content_character, correspondence, icon_character) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $success = $stmt->execute([
        $characterName,
        !empty($age) ? $age : null,
        !empty($habitat) ? $habitat : null,
        !empty($country) ? $country : null,
        !empty($content) ? $content : null,
        $raceId,
        $iconPath
    ]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Character added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add character']);
    }
}

/**
 * Handle updating an existing character
 */
function handleUpdateCharacter($pdo) {
    $characterId = (int)($_POST['character_id'] ?? 0);
    $characterName = trim($_POST['character_name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $habitat = trim($_POST['habitat'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $content = trim($_POST['content_character'] ?? '');
    $raceId = (int)($_POST['race_id'] ?? 0);
    
    if ($characterId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid character ID']);
        return;
    }
    
    if (empty($characterName)) {
        echo json_encode(['success' => false, 'message' => 'Character name is required']);
        return;
    }
    
    if ($raceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid race selection is required']);
        return;
    }
    
    // Check if character name already exists in this race (excluding current character)
    $checkStmt = $pdo->prepare("SELECT id_character FROM characters WHERE character_name = ? AND correspondence = ? AND id_character != ?");
    $checkStmt->execute([$characterName, $raceId, $characterId]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A character with this name already exists in this race']);
        return;
    }
    
    // Handle file upload if present
    $iconPath = null;
    $updateIcon = false;
    if (isset($_FILES['icon_character']) && $_FILES['icon_character']['error'] === UPLOAD_ERR_OK) {
        $iconPath = handleCharacterIconUpload($_FILES['icon_character']);
        if ($iconPath === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload character icon']);
            return;
        }
        $updateIcon = true;
    }
    
    // Update character
    if ($updateIcon) {
        $stmt = $pdo->prepare("UPDATE characters SET character_name = ?, age = ?, habitat = ?, country = ?, content_character = ?, correspondence = ?, icon_character = ? WHERE id_character = ?");
        $success = $stmt->execute([
            $characterName,
            !empty($age) ? $age : null,
            !empty($habitat) ? $habitat : null,
            !empty($country) ? $country : null,
            !empty($content) ? $content : null,
            $raceId,
            $iconPath,
            $characterId
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE characters SET character_name = ?, age = ?, habitat = ?, country = ?, content_character = ?, correspondence = ? WHERE id_character = ?");
        $success = $stmt->execute([
            $characterName,
            !empty($age) ? $age : null,
            !empty($habitat) ? $habitat : null,
            !empty($country) ? $country : null,
            !empty($content) ? $content : null,
            $raceId,
            $characterId
        ]);
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Character updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update character']);
    }
}

/**
 * Handle deleting a character
 */
function handleDeleteCharacter($pdo) {
    $characterId = (int)($_POST['character_id'] ?? 0);
    
    if ($characterId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid character ID']);
        return;
    }
    
    // Get character info before deletion for cleanup
    $stmt = $pdo->prepare("SELECT character_name, icon_character FROM characters WHERE id_character = ?");
    $stmt->execute([$characterId]);
    $character = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$character) {
        echo json_encode(['success' => false, 'message' => 'Character not found']);
        return;
    }
    
    // Delete character
    $deleteStmt = $pdo->prepare("DELETE FROM characters WHERE id_character = ?");
    $success = $deleteStmt->execute([$characterId]);
    
    if ($success) {
        // Clean up character icon file if it exists
        if (!empty($character['icon_character'])) {
            $iconPath = '../images/' . $character['icon_character'];
            if (file_exists($iconPath) && $character['icon_character'] !== 'icon_default.png') {
                unlink($iconPath);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Character "' . $character['character_name'] . '" deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete character']);
    }
}

/**
 * Handle getting character data for editing
 */
function handleGetCharacterData($pdo) {
    $characterId = (int)($_POST['character_id'] ?? 0);
    
    if ($characterId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid character ID']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM characters WHERE id_character = ?");
    $stmt->execute([$characterId]);
    $character = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($character) {
        echo json_encode(['success' => true, 'character' => $character]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Character not found']);
    }
}

/**
 * Handle character icon upload
 */
function handleCharacterIconUpload($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $uploadDir = '../images/';
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'character_' . uniqid() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $fileName;
    }
    
    return false;
}

/**
 * Output the character admin interface HTML
 */
function outputCharacterAdminInterface($species, $races) {
    ?>
    <div class="character-admin-interface">
        <h2>Character Management</h2>
        
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="showTab('character-form')">Add/Edit Character</button>
            <button class="tab-btn" onclick="showTab('character-list')">Character List</button>
        </div>
        
        <!-- Character Form Tab -->
        <div id="character-form" class="tab-content active">
            <h3 id="characterFormTitle">Add New Character</h3>
            <form id="characterForm" enctype="multipart/form-data">
                <input type="hidden" id="characterId" name="character_id" value="">
                
                <div class="char-admin-form-group">
                    <label for="characterName">Character Name *</label>
                    <input type="text" id="characterName" name="character_name" required>
                </div>
                
                <div class="char-admin-form-group">
                    <label for="characterRaceId">Race *</label>
                    <select id="characterRaceId" name="race_id" required>
                        <option value="">Select a race...</option>
                        <?php
                        $currentSpecies = null;
                        foreach ($races as $race) {
                            if ($currentSpecies !== $race['specie_name']) {
                                if ($currentSpecies !== null) echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars($race['specie_name']) . '">';
                                $currentSpecies = $race['specie_name'];
                            }
                            echo '<option value="' . $race['id_race'] . '">' . htmlspecialchars($race['race_name']) . '</option>';
                        }
                        if ($currentSpecies !== null) echo '</optgroup>';
                        ?>
                    </select>
                </div>
                
                <div class="char-admin-form-row">
                    <div class="char-admin-form-group">
                        <label for="characterAge">Age</label>
                        <input type="number" id="characterAge" name="age" min="0">
                    </div>
                    
                    <div class="char-admin-form-group">
                        <label for="characterHabitat">Origin/Habitat</label>
                        <input type="text" id="characterHabitat" name="habitat">
                    </div>
                    
                    <div class="char-admin-form-group">
                        <label for="characterCountry">Country</label>
                        <input type="text" id="characterCountry" name="country">
                    </div>
                </div>
                
                <div class="char-admin-form-group">
                    <label for="characterIcon">Character Icon</label>
                    <input type="file" id="characterIcon" name="icon_character" accept="image/*">
                    <small>Supported formats: JPEG, PNG, GIF, WebP (Max 5MB)</small>
                </div>
                
                <div class="char-admin-form-group">
                    <label for="characterContent">Character Description</label>
                    <textarea id="characterContent" name="content_character" rows="6"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="characterSubmitBtn" onclick="submitCharacterForm()" class="char-admin-btn-primary">
                        Add Character
                    </button>
                    <button type="button" onclick="clearCharacterForm()" class="char-admin-btn-secondary">
                        Clear Form
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Character List Tab -->
        <div id="character-list" class="tab-content">
            <h3>All Characters</h3>
            <div id="characterListContent">
                <!-- Character list will be loaded here -->
                <p>Loading characters...</p>
            </div>
        </div>
    </div>
    
    <?php
}
?>
