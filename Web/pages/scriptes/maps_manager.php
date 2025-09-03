<?php
// Disable all HTML error output for clean JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Use the standard database connection
try {
    require_once '../../database/db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // If JSON parsing fails, try to get action from GET parameters
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        $input = $_GET + $_POST;
    } else {
        $action = $input['action'] ?? '';
    }

    switch ($action) {
        case 'get_all_maps':
            // Use map_layer table instead of maps
            $stmt = $pdo->prepare("SELECT id_layer as id_map, layer_name as name_map, layer_file as map_file, created_at FROM map_layer ORDER BY id_layer");
            $stmt->execute();
            $maps = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'maps' => $maps
            ]);
            break;
            
        case 'get_map_by_id':
            $mapId = $input['map_id'] ?? 1;
            $stmt = $pdo->prepare("SELECT id_layer as id_map, layer_name as name_map, layer_file as map_file, created_at FROM map_layer WHERE id_layer = ?");
            $stmt->execute([$mapId]);
            $map = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($map) {
                echo json_encode([
                    'success' => true,
                    'map' => $map
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Map layer not found'
                ]);
            }
            break;
            
        case 'get_maps_with_point_counts':
            $stmt = $pdo->prepare("
                SELECT ml.id_layer as id_map, ml.layer_name as name_map, ml.layer_file as map_file, ml.created_at, COUNT(ip.id_IP) as point_count 
                FROM map_layer ml 
                LEFT JOIN interest_points ip ON ml.id_layer = ip.map_IP 
                GROUP BY ml.id_layer, ml.layer_name, ml.layer_file, ml.created_at
                ORDER BY ml.id_layer
            ");
            $stmt->execute();
            $maps = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'maps' => $maps
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
            break;
    }
    
} catch (PDOException $e) {
    error_log('Database error in maps_manager.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log('General error in maps_manager.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing the request'
    ]);
}
?>
