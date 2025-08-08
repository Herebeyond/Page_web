<?php
// Disable all HTML error output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Environment detection for development vs production
$isLocalDevelopment = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) || 
                     (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', 'localhost:80', '127.0.0.1', '127.0.0.1:80']));

// Secure CORS policy with development flexibility
$allowedOrigins = [
    // Production HTTPS origins
    'https://localhost',
    'https://localhost:443',
    'https://localhost:8080',
    'https://127.0.0.1',
    'https://127.0.0.1:443',
    'https://127.0.0.1:8080',
    'https://host.docker.internal',
    'https://host.docker.internal:443'
];

// Allow HTTP for local development only
if ($isLocalDevelopment) {
    $allowedOrigins = array_merge($allowedOrigins, [
        'http://localhost',
        'http://localhost:80',
        'http://localhost:8080',
        'http://127.0.0.1',
        'http://127.0.0.1:80',
        'http://127.0.0.1:8080',
        'http://host.docker.internal',
        'http://host.docker.internal:80'
    ]);
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

// Security check: Block HTTP in production but allow in local development
if (!empty($origin) && str_starts_with($origin, 'http://') && !$isLocalDevelopment) {
    error_log("SECURITY VIOLATION: HTTP request blocked in production - HTTPS required: " . $origin);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'HTTPS required in production - HTTP not allowed for security']);
    exit;
}

// Handle CORS headers
if (empty($origin)) {
    // Same-origin request - apply development flexibility
    if (!$isHttps && !$isLocalDevelopment) {
        error_log("SECURITY VIOLATION: Same-origin HTTP request blocked in production - HTTPS required");
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'HTTPS required in production - HTTP not allowed for security']);
        exit;
    }
    header('Access-Control-Allow-Origin: null');
} elseif (in_array($origin, $allowedOrigins)) {
    // Allowed origin request
    header('Access-Control-Allow-Origin: ' . $origin);
    
    // Add HSTS header for HTTPS connections only
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
} else {
    // Block unauthorized cross-origin requests
    error_log("SECURITY VIOLATION: Blocked request from unauthorized origin: " . $origin);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Invalid origin']);
    exit;
}

header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Additional security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Content Security Policy for API responses
header('Content-Security-Policy: default-src \'none\'; frame-ancestors \'none\';');

// Start session to check authentication for write operations
session_start();

// Use the existing database connection
try {
    require_once '../../login/db.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if user is authenticated for write operations
function requireAuthentication($action) {
    $writeOperations = ['save_points', 'savePoints', 'delete_point', 'update_point', 'clear_points', 'add_type', 'delete_type', 'update_type_color'];
    
    if (in_array($action, $writeOperations)) {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required']);
            exit;
        }
        
        // For admin-only operations, check admin role
        $adminOnlyOperations = ['delete_point', 'clear_points', 'add_type', 'delete_type', 'update_type_color'];
        if (in_array($action, $adminOnlyOperations)) {
            // Load user roles - this mimics the pattern from page_init.php
            try {
                require_once '../blueprints/page_init.php';
                if (!in_array('admin', $user_roles ?? [])) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Admin privileges required']);
                    exit;
                }
            } catch (Exception $e) {
                // Fallback: if we can't load roles, require session user at minimum
                if (!isset($_SESSION['user'])) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Admin privileges required']);
                    exit;
                }
            }
        }
    }
}

// Get input data
try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input: ' . json_last_error_msg()]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error reading input: ' . $e->getMessage()]);
    exit;
}

$action = $input['action'] ?? '';

// Check authentication before processing the action
requireAuthentication($action);

switch ($action) {
    case 'save_points':
    case 'savePoints':
        savePoints($pdo, $input);
        break;
    case 'load_points':
    case 'loadPoints':
        loadPoints($pdo, $input);
        break;
    case 'delete_point':
        deletePoint($pdo, $input);
        break;
    case 'update_point':
        updatePoint($pdo, $input);
        break;
    case 'clear_points':
        clearAllPoints($pdo, $input);
        break;
    case 'load_types':
        loadPointTypes($pdo);
        break;
    case 'add_type':
        addPointType($pdo, $input);
        break;
    case 'delete_type':
        deletePointType($pdo, $input);
        break;
    case 'update_type_color':
        updateTypeColor($pdo, $input);
        break;
    case 'check_duplicate_name':
        checkDuplicateName($pdo, $input);
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
        
        $savedPoints = [];
        
        foreach ($points as $point) {
            // Prepare coordinates as JSON
            $coordinates = json_encode(['x' => $point['x'], 'y' => $point['y']]);
            
            // Convert type name to type ID
            $typeId = getTypeId($point['type'], $pdo);
            
            // Check if point already has a database_id (existing point)
            if (isset($point['database_id']) && $point['database_id']) {
                // Update existing point
                $stmt = $pdo->prepare("UPDATE interest_points SET name_IP = ?, description_IP = ?, type_IP = ?, coordinates_IP = ? WHERE id_IP = ?");
                $stmt->execute([
                    $point['name'],
                    $point['description'],
                    $typeId, // Store type ID instead of name
                    $coordinates,
                    $point['database_id']
                ]);
                
                $savedPoints[] = [
                    'local_id' => $point['id'],
                    'database_id' => $point['database_id']
                ];
            } else {
                // Insert new point
                $stmt = $pdo->prepare("INSERT INTO interest_points (name_IP, description_IP, map_IP, type_IP, coordinates_IP) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $point['name'],
                    $point['description'],
                    $mapId,
                    $typeId, // Store type ID instead of name
                    $coordinates
                ]);
                
                $savedPoints[] = [
                    'local_id' => $point['id'],
                    'database_id' => $pdo->lastInsertId()
                ];
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => count($points) . ' points saved',
            'saved_points' => $savedPoints
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'Error during save: ' . $e->getMessage()]);
    }
}

function loadPoints($pdo, $input) {
    try {
        // Check if PDO connection is valid
        if (!$pdo) {
            echo json_encode(['success' => false, 'message' => 'Database connection not available']);
            return;
        }
        
        $mapId = $input['map_id'] ?? 1;
        
        // First check if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'interest_points'");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Table interest_points does not exist']);
            return;
        }
        
        // Load points with type information from IP_types table
        $stmt = $pdo->prepare("
            SELECT ip.*, ipt.name_IPT as type_name 
            FROM interest_points ip 
            LEFT JOIN IP_types ipt ON ip.type_IP = ipt.id_IPT 
            WHERE ip.map_IP = ? 
            ORDER BY ip.id_IP
        ");
        $stmt->execute([$mapId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $points = [];
        foreach ($results as $row) {
            // Parse JSON coordinates
            $coordinates = json_decode($row['coordinates_IP'], true);
            if (!$coordinates || !isset($coordinates['x']) || !isset($coordinates['y'])) {
                continue; // Skip invalid coordinate data
            }
            
            // Use the type name from the database, or fallback to hardcoded mapping
            $typeName = $row['type_name'] ?: getTypeName($row['type_IP'], $pdo);
            
            $points[] = [
                'id_IP' => (int)$row['id_IP'],
                'x_IP' => (float)$coordinates['x'],
                'y_IP' => (float)$coordinates['y'],
                'name_IP' => $row['name_IP'],
                'description_IP' => $row['description_IP'],
                'type_IP' => $typeName
            ];
        }
        
        echo json_encode(['success' => true, 'points' => $points, 'count' => count($points)]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => DATABASE_ERROR_PREFIX . $e->getMessage()]);
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

function updatePoint($pdo, $input) {
    try {
        $databaseId = $input['database_id'] ?? 0;
        $name = $input['name'] ?? '';
        $description = $input['description'] ?? '';
        $type = $input['type'] ?? '';
        $x = $input['x'] ?? 0;
        $y = $input['y'] ?? 0;
        
        // Validation
        if (!$databaseId) {
            echo json_encode(['success' => false, 'message' => 'Missing point ID']);
            return;
        }
        
        if (empty($name) || empty($type)) {
            echo json_encode(['success' => false, 'message' => 'Name and type are required']);
            return;
        }
        
        // Check if the point exists
        $checkStmt = $pdo->prepare("SELECT id_IP FROM interest_points WHERE id_IP = ?");
        $checkStmt->execute([$databaseId]);
        
        if (!$checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Point not found']);
            return;
        }
        
        // Convert type name to type ID
        $typeId = getTypeId($type, $pdo);
        
        // Update the point
        $coordinates = json_encode(['x' => $x, 'y' => $y]);
        $stmt = $pdo->prepare("UPDATE interest_points SET name_IP = ?, description_IP = ?, type_IP = ?, coordinates_IP = ? WHERE id_IP = ?");
        $stmt->execute([$name, $description, $typeId, $coordinates, $databaseId]);
        
        echo json_encode(['success' => true, 'message' => 'Point updated successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error during update: ' . $e->getMessage()]);
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

// Type mapping - checks database first, then falls back to hardcoded mapping
function getTypeId($typeName, $pdo = null) {
    // First try to get from database if PDO is available
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id_IPT FROM IP_types WHERE name_IPT = ?");
            $stmt->execute([$typeName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return (int)$result['id_IPT'];
            }
        } catch (Exception $e) {
            // Fall through to hardcoded mapping
        }
    }
    
    // Fallback to hardcoded mapping
    $types = [
        'City' => 1,
        'Dungeon' => 2,
        'Temple' => 3,
        'Mountain' => 4,
        'Forest' => 5,
        'River' => 6,
        'Castle' => 7,
        'Cave' => 8,
        'Location' => 9,
        'Lieu' => 9, // French for Location
        'Village' => 10,
        'Tower' => 11,
        'Ruins' => 12,
        'Ancient Ruins' => 12,
        'Port' => 13,
        'Bridge' => 14,
        'Mine' => 15
    ];
    
    return $types[$typeName] ?? 9; // Default to "Location"
}

function getTypeName($typeId, $pdo = null) {
    // First try to get from database if PDO is available
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT name_IPT FROM IP_types WHERE id_IPT = ?");
            $stmt->execute([$typeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return $result['name_IPT'];
            }
        } catch (Exception $e) {
            // Fall through to hardcoded mapping
        }
    }
    
    // Fallback to hardcoded mapping
    $types = [
        1 => 'City',
        2 => 'Dungeon',
        3 => 'Temple',
        4 => 'Mountain',
        5 => 'Forest',
        6 => 'River',
        7 => 'Castle',
        8 => 'Cave',
        9 => 'Location',
        10 => 'Village',
        11 => 'Tower',
        12 => 'Ancient Ruins',
        13 => 'Port',
        14 => 'Bridge',
        15 => 'Mine'
    ];
    
    return $types[$typeId] ?? 'Location';
}

// Point Types Management Functions
function loadPointTypes($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id_IPT, name_IPT, color_IPT FROM IP_types ORDER BY name_IPT");
        $stmt->execute();
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'types' => $types]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => DATABASE_ERROR_PREFIX . $e->getMessage()]);
    }
}

function addPointType($pdo, $input) {
    try {
        $typeName = trim($input['type_name'] ?? '');
        $typeColor = trim($input['type_color'] ?? '#ff4444');
        
        if (empty($typeName)) {
            echo json_encode(['success' => false, 'message' => 'Type name is required']);
            return;
        }
        
        // Check if type already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM IP_types WHERE name_IPT = ?");
        $stmt->execute([$typeName]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Type already exists']);
            return;
        }
        
        // Insert new type with color
        $stmt = $pdo->prepare("INSERT INTO IP_types (name_IPT, color_IPT) VALUES (?, ?)");
        $stmt->execute([$typeName, $typeColor]);
        
        echo json_encode(['success' => true, 'message' => 'Type added successfully', 'type_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => DATABASE_ERROR_PREFIX . $e->getMessage()]);
    }
}

function deletePointType($pdo, $input) {
    try {
        $typeId = intval($input['type_id'] ?? 0);
        
        if ($typeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid type ID']);
            return;
        }
        
        // Check if type is being used by any points
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM interest_points WHERE type_IP = ?");
        $stmt->execute([$typeId]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete type: it is being used by existing points']);
            return;
        }
        
        // Delete the type
        $stmt = $pdo->prepare("DELETE FROM IP_types WHERE id_IPT = ?");
        $stmt->execute([$typeId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Type deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Type not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => DATABASE_ERROR_PREFIX . $e->getMessage()]);
    }
}

function updateTypeColor($pdo, $input) {
    try {
        $typeId = intval($input['type_id'] ?? 0);
        $typeColor = trim($input['type_color'] ?? '');
        
        if ($typeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid type ID']);
            return;
        }
        
        if (empty($typeColor)) {
            echo json_encode(['success' => false, 'message' => 'Color is required']);
            return;
        }
        
        // Validate hex color format
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $typeColor)) {
            echo json_encode(['success' => false, 'message' => 'Invalid color format']);
            return;
        }
        
        // Update the type color
        $stmt = $pdo->prepare("UPDATE IP_types SET color_IPT = ? WHERE id_IPT = ?");
        $stmt->execute([$typeColor, $typeId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Type color updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Type not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => DATABASE_ERROR_PREFIX . $e->getMessage()]);
    }
}

function checkDuplicateName($pdo, $input) {
    try {
        $name = $input['name'] ?? '';
        $excludeId = $input['exclude_id'] ?? null; // For updates, exclude current point
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            return;
        }
        
        // Check for case-insensitive duplicate
        if ($excludeId) {
            $stmt = $pdo->prepare("SELECT id_IP, name_IP FROM interest_points WHERE LOWER(name_IP) = LOWER(?) AND id_IP != ?");
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = $pdo->prepare("SELECT id_IP, name_IP FROM interest_points WHERE LOWER(name_IP) = LOWER(?)");
            $stmt->execute([$name]);
        }
        
        $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($duplicate) {
            echo json_encode([
                'success' => true,
                'is_duplicate' => true,
                'existing_name' => $duplicate['name_IP'],
                'existing_id' => $duplicate['id_IP']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'is_duplicate' => false
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => DATABASE_ERROR_PREFIX . $e->getMessage()]);
    }
}
