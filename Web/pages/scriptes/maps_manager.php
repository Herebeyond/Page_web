<?php
// Disable all HTML error output for clean JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Try multiple database connection configurations
$pdo = null;
$connections_to_try = [
    ['host' => 'localhost', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root_password'],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => ''],
    ['host' => 'db', 'user' => 'root', 'pass' => 'root_password']
];

foreach ($connections_to_try as $config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname=univers;charset=utf8", 
            $config['user'], 
            $config['pass'], 
            [
                PDO::ATTR_TIMEOUT => 3,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
        break; // Stop trying once we have a successful connection
    } catch (PDOException $e) {
        // Continue to next configuration
        continue;
    }
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
            $stmt = $pdo->prepare("SELECT * FROM maps WHERE is_active = 1 ORDER BY display_order");
            $stmt->execute();
            $maps = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'maps' => $maps
            ]);
            break;
            
        case 'get_map_by_id':
            $mapId = $input['map_id'] ?? 1;
            $stmt = $pdo->prepare("SELECT * FROM maps WHERE id_map = ? AND is_active = 1");
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
                SELECT m.*, COUNT(ip.id_IP) as point_count 
                FROM maps m 
                LEFT JOIN interest_points ip ON m.id_map = ip.map_IP 
                WHERE m.is_active = 1 
                GROUP BY m.id_map 
                ORDER BY m.display_order
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
