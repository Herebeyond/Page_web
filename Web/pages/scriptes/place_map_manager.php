<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
session_start();

// Include shared functions
require_once 'functions.php';

// Check authentication for admin actions
$allowed_actions = ['get_map_data', 'load_map_points', 'load_types'];
$admin_actions = ['save_points', 'delete_point', 'update_point', 'create_map', 'change_map_image'];

// Parse JSON input using shared secure function
$input = parseSecureJsonInput();
if ($input === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$action = isset($input['action']) && is_string($input['action']) ? $input['action'] : '';

// Validate action parameter
if (empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Action parameter required']);
    exit;
}

// Debug logging (to error log, not output)
error_log("place_map_manager.php - Action: [FILTERED]");
error_log("place_map_manager.php - Input: [FILTERED]");

// Debug logging
error_log("Action: [FILTERED]");
error_log("Input: [FILTERED]");

// Check permissions
if (in_array($action, $admin_actions)) {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        exit;
    }
}

try {
    require_once '../../database/db.php';
    
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }
    
    switch ($action) {
        case 'create_map':
            createMapForPlace($pdo, $input);
            break;
            
        case 'get_map_data':
            getMapData($pdo, $input);
            break;
            
        case 'load_map_points':
            loadMapPoints($pdo, $input);
            break;
            
        case 'save_points':
            saveMapPoints($pdo, $input);
            break;
            
        case 'delete_point':
            deleteMapPoint($pdo, $input);
            break;
            
        case 'update_point':
            updateMapPoint($pdo, $input);
            break;
            
        case 'load_types':
            loadPointTypes($pdo);
            break;
            
        case 'change_map_image':
            changeMapImage($pdo, $input);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function createMapForPlace($pdo, $input) {
    $placeId = $input['place_id'] ?? null;
    $mapName = $input['map_name'] ?? '';
    $mapImage = $input['map_image'] ?? 'default-map.jpg';
    
    if (!$placeId) {
        echo json_encode(['success' => false, 'message' => 'Place ID required']);
        return;
    }
    
    // Check if map already exists for this place
    $stmt = $pdo->prepare("SELECT id_map FROM maps WHERE place_id = ?");
    $stmt->execute([$placeId]);
    $existingMap = $stmt->fetch();
    
    if ($existingMap) {
        echo json_encode(['success' => true, 'map_id' => $existingMap['id_map'], 'message' => 'Map already exists']);
        return;
    }
    
    // Create new place-specific map (is_active = 0 to exclude from main map system)
    $stmt = $pdo->prepare("INSERT INTO maps (name_map, image_map, place_id, is_active, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->execute([$mapName, $mapImage, $placeId]);
    
    $mapId = $pdo->lastInsertId();
    
    echo json_encode(['success' => true, 'map_id' => $mapId, 'message' => 'Place-specific map created successfully']);
}

function getMapData($pdo, $input) {
    $placeId = $input['place_id'] ?? null;
    
    if (!$placeId) {
        echo json_encode(['success' => false, 'message' => 'Place ID required']);
        return;
    }
    
    // Get existing map for this place
    $stmt = $pdo->prepare("SELECT * FROM maps WHERE place_id = ?");
    $stmt->execute([$placeId]);
    $map = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$map) {
        // Create place-specific map automatically (but mark as inactive for main map system)
        $stmt = $pdo->prepare(SQL_SELECT_PLACE_NAME_BY_ID);
        $stmt->execute([$placeId]);
        $place = $stmt->fetch();
        
        $mapName = ($place ? $place['name_IP'] . ' Map' : 'Place Map');
        
        // Create with is_active = 0 so it doesn't appear in main world map selector
        $stmt = $pdo->prepare("INSERT INTO maps (name_map, image_map, place_id, is_active, created_at) VALUES (?, 'default-map.jpg', ?, 0, NOW())");
        $stmt->execute([$mapName, $placeId]);
        
        $mapId = $pdo->lastInsertId();
        
        // Fetch the newly created map
        $stmt = $pdo->prepare("SELECT * FROM maps WHERE id_map = ?");
        $stmt->execute([$mapId]);
        $map = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode(['success' => true, 'map' => $map]);
}

function loadMapPoints($pdo, $input) {
    $mapId = $input['map_id'] ?? null;
    
    if (!$mapId) {
        echo json_encode(['success' => false, 'message' => 'Map ID required']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT ip.*, ipt.name_IPT as type_name, ipt.color_IPT as type_color
        FROM interest_points ip
        LEFT JOIN IP_types ipt ON ip.type_IP = ipt.id_IPT
        WHERE ip.map_IP = ?
    ");
    $stmt->execute([$mapId]);
    $points = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert coordinates from JSON to x,y values
    foreach ($points as &$point) {
        $coordinates = json_decode($point['coordinates_IP'], true);
        $point['x_IP'] = $coordinates['x'] ?? 0;
        $point['y_IP'] = $coordinates['y'] ?? 0;
    }
    
    echo json_encode(['success' => true, 'points' => $points]);
}

function saveMapPoints($pdo, $input) {
    $points = $input['points'] ?? [];
    $mapId = $input['map_id'] ?? null;
    $placeId = $input['place_id'] ?? null;
    
    // If no map_id but we have place_id, create a map first
    if (!$mapId && $placeId) {
        // Get place name for map creation
        $stmt = $pdo->prepare(SQL_SELECT_PLACE_NAME_BY_ID);
        $stmt->execute([$placeId]);
        $place = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($place) {
            // Create a new place-specific map (is_active = 0 to exclude from main map system)
            $mapName = $place['name_IP'] . ' Map';
            $stmt = $pdo->prepare("INSERT INTO maps (name_map, image_map, place_id, is_active) VALUES (?, ?, ?, 0)");
            $stmt->execute([$mapName, 'default-map.jpg', $placeId]);
            $mapId = $pdo->lastInsertId();
        }
    }
    
    if (!$mapId) {
        echo json_encode(['success' => false, 'message' => 'Map ID or Place ID required']);
        return;
    }
    
    $savedPoints = [];
    
    foreach ($points as $point) {
        // Prepare coordinates
        $coordinates = json_encode(['x' => $point['x'], 'y' => $point['y']]);
        
        // Get type ID from type name
        $stmt = $pdo->prepare("SELECT id_IPT FROM IP_types WHERE name_IPT = ?");
        $stmt->execute([$point['type']]);
        $typeData = $stmt->fetch();
        $typeId = $typeData ? $typeData['id_IPT'] : null;
        
        if (!$typeId) {
            continue; // Skip if type not found
        }
        
        // Check if this point already exists in database
        if (isset($point['database_id']) && $point['database_id']) {
            // Update existing point
            $stmt = $pdo->prepare("
                UPDATE interest_points 
                SET name_IP = ?, description_IP = ?, coordinates_IP = ?, type_IP = ? 
                WHERE id_IP = ?
            ");
            $stmt->execute([
                $point['name'],
                $point['description'],
                $coordinates,
                $typeId,
                $point['database_id']
            ]);
            
            $savedPoints[] = [
                'local_id' => $point['id'],
                'database_id' => $point['database_id']
            ];
        } else {
            // Insert new point
            $stmt = $pdo->prepare("
                INSERT INTO interest_points (name_IP, description_IP, coordinates_IP, type_IP, map_IP) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $point['name'],
                $point['description'],
                $coordinates,
                $typeId,
                $mapId
            ]);
            
            $savedPoints[] = [
                'local_id' => $point['id'],
                'database_id' => $pdo->lastInsertId()
            ];
        }
    }
    
    echo json_encode([
        'success' => true, 
        'saved_points' => $savedPoints,
        'map_id' => $mapId
    ]);
}

function deleteMapPoint($pdo, $input) {
    $databaseId = $input['database_id'] ?? null;
    
    if (!$databaseId) {
        echo json_encode(['success' => false, 'message' => 'Database ID required']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM interest_points WHERE id_IP = ?");
    $stmt->execute([$databaseId]);
    
    echo json_encode(['success' => true, 'message' => 'Point deleted successfully']);
}

function updateMapPoint($pdo, $input) {
    $databaseId = $input['database_id'] ?? null;
    $name = $input['name'] ?? '';
    $description = $input['description'] ?? '';
    $type = $input['type'] ?? '';
    $x = $input['x'] ?? 0;
    $y = $input['y'] ?? 0;
    
    if (!$databaseId) {
        echo json_encode(['success' => false, 'message' => 'Database ID required']);
        return;
    }
    
    // Get type ID from type name
    $stmt = $pdo->prepare("SELECT id_IPT FROM IP_types WHERE name_IPT = ?");
    $stmt->execute([$type]);
    $typeData = $stmt->fetch();
    $typeId = $typeData ? $typeData['id_IPT'] : null;
    
    if (!$typeId) {
        echo json_encode(['success' => false, 'message' => 'Type not found']);
        return;
    }
    
    // Prepare coordinates
    $coordinates = json_encode(['x' => $x, 'y' => $y]);
    
    // Update point
    $stmt = $pdo->prepare("
        UPDATE interest_points 
        SET name_IP = ?, description_IP = ?, coordinates_IP = ?, type_IP = ? 
        WHERE id_IP = ?
    ");
    $stmt->execute([$name, $description, $coordinates, $typeId, $databaseId]);
    
    echo json_encode(['success' => true, 'message' => 'Point updated successfully']);
}

function loadPointTypes($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM IP_types ORDER BY name_IPT");
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'types' => $types]);
}

function changeMapImage($pdo, $input) {
    $placeId = $input['place_id'] ?? null;
    $fileData = $input['file_data'] ?? null;
    $fileName = $input['file_name'] ?? '';
    $fileType = $input['file_type'] ?? '';
    
    error_log("changeMapImage called with placeId: $placeId, fileName: $fileName, fileType: $fileType");
    
    if (!$placeId || !$fileData || !$fileName) {
        error_log("Missing parameters: placeId=" . ($placeId ? 'yes' : 'no') . ", fileData=" . ($fileData ? 'yes' : 'no') . ", fileName=" . ($fileName ? 'yes' : 'no'));
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        // Create place slug for folder path
        $stmt = $pdo->prepare(SQL_SELECT_PLACE_NAME_BY_ID);
        $stmt->execute([$placeId]);
        $place = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$place) {
            error_log("Place not found for ID: $placeId");
            echo json_encode(['success' => false, 'message' => 'Place not found']);
            return;
        }
        error_log("Found place: " . $place['name_IP']);
        
        // Create secure slug using shared function
        $slug = createSecureSlug($place['name_IP']);
        if ($slug === false) {
            error_log("Invalid place name for secure slug generation: " . $place['name_IP']);
            echo json_encode(['success' => false, 'message' => 'Invalid place name']);
            return;
        }
        
        error_log("Created secure slug: $slug");
        
        // Set up secure base path
        $baseImagesPath = realpath('../../images/places');
        if ($baseImagesPath === false) {
            error_log("Could not resolve base images path");
            echo json_encode(['success' => false, 'message' => 'System configuration error']);
            return;
        }
        
        // Create secure map directory path
        $placePath = constructSafePlacePath($slug);
        if ($placePath === false) {
            error_log("Failed to construct safe map directory path for slug: $slug");
            echo json_encode(['success' => false, 'message' => 'Invalid place identifier']);
            return;
        }
        
        $mapDir = $placePath . DIRECTORY_SEPARATOR . 'map';
        
        error_log("Target directory: $mapDir");
        error_log("Current working directory: " . getcwd());
        
        if (!is_dir($mapDir)) {
            error_log("Directory does not exist, attempting to create: $mapDir");
            if (!mkdir($mapDir, 0755, true)) {
                $lastError = error_get_last();
                error_log("Failed to create directory: $mapDir. Error: " . print_r($lastError, true));
                echo json_encode(['success' => false, 'message' => 'Failed to create directory']);
                return;
            }
            error_log("Created directory: $mapDir");
        } else {
            error_log("Directory already exists: $mapDir");
        }
        
        // Validate and sanitize filename
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            error_log("Invalid file extension: $fileExtension");
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images allowed.']);
            return;
        }
        
        $newFileName = 'map_' . time() . '.' . strtolower($fileExtension);
        $filePath = $mapDir . DIRECTORY_SEPARATOR . $newFileName;
        
        // Final security check - ensure file path is within expected directory
        $realFilePath = realpath(dirname($filePath)) . DIRECTORY_SEPARATOR . basename($filePath);
        if (strpos($realFilePath, $baseImagesPath) !== 0) {
            error_log("Security violation: File path outside allowed directory");
            echo json_encode(['success' => false, 'message' => 'Security violation']);
            return;
        }
        
        error_log("Target file path: $filePath");
        
        // Decode and save the file
        $imageData = base64_decode($fileData);
        if ($imageData === false) {
            error_log("Failed to decode base64 data");
            echo json_encode(['success' => false, 'message' => 'Invalid image data']);
            return;
        }
        
        error_log("Decoded image data size: " . strlen($imageData) . " bytes");
        
        if (file_put_contents($filePath, $imageData) === false) {
            error_log("Failed to write file: $filePath");
            echo json_encode(['success' => false, 'message' => 'Failed to save image file']);
            return;
        }
        
        error_log("Successfully saved file: $filePath");
        
        // Check if maps table exists
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'maps'");
            $tableExists = $stmt->rowCount() > 0;
            error_log("Maps table exists: " . ($tableExists ? 'yes' : 'no'));
            
            if (!$tableExists) {
                error_log("Maps table does not exist, creating it...");
                $createTableSQL = "
                    CREATE TABLE maps (
                        id_map INT AUTO_INCREMENT PRIMARY KEY,
                        name_map VARCHAR(255) NOT NULL,
                        image_map VARCHAR(255) NOT NULL,
                        place_id INT NOT NULL,
                        FOREIGN KEY (place_id) REFERENCES interest_points(id_IP) ON DELETE CASCADE
                    )
                ";
                $pdo->exec($createTableSQL);
                error_log("Maps table created successfully");
            }
        } catch (Exception $tableError) {
            error_log("Error checking/creating maps table: " . $tableError->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database table error: ' . $tableError->getMessage()]);
            return;
        }
        
        // Check if map record exists for this place
        $stmt = $pdo->prepare("SELECT id_map FROM maps WHERE place_id = ?");
        $stmt->execute([$placeId]);
        $existingMap = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $mapId = null;
        
        if ($existingMap) {
            // Update existing map record
            $mapId = $existingMap['id_map'];
            error_log("Updating existing map record ID: $mapId");
            $stmt = $pdo->prepare("UPDATE maps SET image_map = ? WHERE id_map = ?");
            $stmt->execute([$newFileName, $mapId]);
            error_log("Updated existing map record");
        } else {
            // Create new map record
            $mapName = $place['name_IP'] . ' Map';
            error_log("Creating new map record: $mapName");
            $stmt = $pdo->prepare("INSERT INTO maps (name_map, image_map, place_id) VALUES (?, ?, ?)");
            $stmt->execute([$mapName, $newFileName, $placeId]);
            $mapId = $pdo->lastInsertId();
            error_log("Created new map record with ID: $mapId");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Map image updated successfully',
            'new_filename' => $newFileName,
            'map_id' => $mapId
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

