<?php
// Disable all HTML error output for clean JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Use the shared database connection file
try {
    require_once '../../login/db.php';
    
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }
} catch (Exception $e) {
    error_log('Database connection error in maps_manager.php: ' . $e->getMessage());
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
            $stmt = $pdo->prepare("
                SELECT id_map, map_name as name_map, map_file as image_map, dimension_id, description, created_at 
                FROM maps ORDER BY id_map
            ");
            $stmt->execute();
            $maps = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'maps' => $maps
            ]);
            break;
            
        case 'get_map_by_id':
            $mapId = $input['map_id'] ?? 1;
            $stmt = $pdo->prepare("
                SELECT id_map, map_name as name_map, map_file as image_map, dimension_id, description, created_at 
                FROM maps WHERE id_map = ?
            ");
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
                    'message' => 'Map not found'
                ]);
            }
            break;
            
        case 'get_maps_with_point_counts':
            $stmt = $pdo->prepare("
                SELECT m.id_map, m.map_name as name_map, m.map_file as image_map, m.dimension_id, m.description, m.created_at, COUNT(ip.id_IP) as point_count 
                FROM maps m 
                LEFT JOIN interest_points ip ON m.id_map = ip.map_IP 
                GROUP BY m.id_map 
                ORDER BY m.id_map
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
