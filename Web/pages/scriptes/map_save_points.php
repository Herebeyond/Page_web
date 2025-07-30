<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Use the existing database connection
require_once '../../login/db.php';

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'save_points':
        savePoints($pdo, $input);
        break;
    case 'load_points':
        loadPoints($pdo, $input);
        break;
    case 'delete_point':
        deletePoint($pdo, $input);
        break;
    case 'clear_points':
        clearAllPoints($pdo, $input);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Unrecognized action']);
}

function savePoints($pdo, $input) {
    try {
        $points = $input['points'] ?? [];
        $mapId = $input['map_id'] ?? 1;
        
        if (empty($points)) {
            echo json_encode(['success' => false, 'message' => 'No points to save']);
            return;
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Clear existing points for this map
        $stmt = $pdo->prepare("DELETE FROM interest_points WHERE map_IP = ?");
        $stmt->execute([$mapId]);
        
        // Insert new points
        $stmt = $pdo->prepare("INSERT INTO interest_points (name_IP, description_IP, map_IP, type_IP, coordinates_IP) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($points as $point) {
            $coordinates = json_encode(['x' => $point['x'], 'y' => $point['y']]);
            
            // For type_IP, we'll use a simple mapping. You can create a types table later
            $typeId = getTypeId($point['type'] ?? 'Lieu');
            
            $stmt->execute([
                $point['name'],
                $point['description'],
                $mapId,
                $typeId,
                $coordinates
            ]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => count($points) . ' points saved']);
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'Error during save: ' . $e->getMessage()]);
    }
}

function loadPoints($pdo, $input) {
    try {
        $mapId = $input['map_id'] ?? 1;
        
        $stmt = $pdo->prepare("SELECT * FROM interest_points WHERE map_IP = ? ORDER BY creation_date");
        $stmt->execute([$mapId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $points = [];
        foreach ($results as $row) {
            $coordinates = json_decode($row['coordinates_IP'], true);
            
            $points[] = [
                'id' => (int)$row['id_IP'], // Use database ID as local ID
                'database_id' => (int)$row['id_IP'], // Store database ID separately
                'x' => $coordinates['x'],
                'y' => $coordinates['y'],
                'name' => $row['name_IP'],
                'description' => $row['description_IP'],
                'type' => getTypeName($row['type_IP'])
            ];
        }
        
        echo json_encode(['success' => true, 'points' => $points]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error during loading: ' . $e->getMessage()]);
    }
}

function deletePoint($pdo, $input) {
    try {
        $databaseId = $input['database_id'] ?? 0;
        
        if (!$databaseId) {
            echo json_encode(['success' => false, 'message' => 'Missing point ID']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM interest_points WHERE id_IP = ?");
        $stmt->execute([$databaseId]);
        
        echo json_encode(['success' => true, 'message' => 'Point deleted']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error during deletion: ' . $e->getMessage()]);
    }
}

function clearAllPoints($pdo, $input) {
    try {
        $mapId = $input['map_id'] ?? 1;
        
        $stmt = $pdo->prepare("DELETE FROM interest_points WHERE map_IP = ?");
        $stmt->execute([$mapId]);
        
        echo json_encode(['success' => true, 'message' => 'All points deleted']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error during deletion: ' . $e->getMessage()]);
    }
}

// Simple type mapping - you can expand this or create a separate types table
function getTypeId($typeName) {
    $types = [
        'City' => 1,
        'Dungeon' => 2,
        'Temple' => 3,
        'Mountain' => 4,
        'Forest' => 5,
        'River' => 6,
        'Castle' => 7,
        'Cave' => 8,
        'Location' => 9
    ];
    
    return $types[$typeName] ?? 9; // Default to "Location"
}

function getTypeName($typeId) {
    $types = [
        1 => 'City',
        2 => 'Dungeon',
        3 => 'Temple',
        4 => 'Mountain',
        5 => 'Forest',
        6 => 'River',
        7 => 'Castle',
        8 => 'Cave',
        9 => 'Location'
    ];
    
    return $types[$typeId] ?? 'Location';
}
?>
