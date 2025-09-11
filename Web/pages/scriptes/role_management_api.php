<?php
/**
 * Role Management API
 * Handles CRUD operations for the new role system
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON content type
header('Content-Type: application/json');

// CORS headers for local development
$allowedOrigins = [
    'https://localhost', 'https://127.0.0.1',
    'http://localhost', 'http://127.0.0.1'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session for authentication
session_start();

// Include required files
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/functions.php';

// Verify database connection
if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get user data and roles
$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user_roles = getUserRolesCompatibility($user, $pdo);

if (!in_array('admin', $user_roles)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin privileges required']);
    exit;
}

// Handle different actions
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            handleAddRole();
            break;
            
        case 'remove':
            handleRemoveRole();
            break;
            
        case 'bulk_add':
            handleBulkAddRole();
            break;
            
        case 'bulk_remove':
            handleBulkRemoveRole();
            break;
            
        case 'create_role':
            handleCreateRole();
            break;
            
        case 'get_user_roles':
            handleGetUserRoles();
            break;
            
        case 'get_all_users':
            handleGetAllUsers();
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Role management API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function handleAddRole() {
    global $pdo, $user;
    
    $userId = $_POST['userId'] ?? '';
    $roleName = $_POST['roleId'] ?? '';
    
    if (empty($userId) || empty($roleName)) {
        echo json_encode(['success' => false, 'message' => 'User ID and role name are required']);
        return;
    }
    
    // Validate user exists
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id_user = ?");
    $stmt->execute([$userId]);
    $targetUser = $stmt->fetch();
    
    if (!$targetUser) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    // Check if user already has this role
    if (userHasRole($userId, $roleName, $pdo)) {
        echo json_encode(['success' => false, 'message' => 'User already has this role']);
        return;
    }
    
    // Add the role
    if (addUserRole($userId, $roleName, $pdo, $user['id_user'])) {
        echo json_encode([
            'success' => true, 
            'message' => "Role '$roleName' added to user '{$targetUser['username']}'"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add role']);
    }
}

function handleRemoveRole() {
    global $pdo;
    
    $userId = $_POST['userId'] ?? '';
    $roleName = $_POST['roleId'] ?? '';
    
    if (empty($userId) || empty($roleName)) {
        echo json_encode(['success' => false, 'message' => 'User ID and role name are required']);
        return;
    }
    
    // Validate user exists
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id_user = ?");
    $stmt->execute([$userId]);
    $targetUser = $stmt->fetch();
    
    if (!$targetUser) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    // Check if user has this role
    if (!userHasRole($userId, $roleName, $pdo)) {
        echo json_encode(['success' => false, 'message' => 'User does not have this role']);
        return;
    }
    
    // Prevent removing the last admin role
    if ($roleName === 'admin') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as admin_count
            FROM roles r 
            INNER JOIN role_to_user rtu ON r.id_role = rtu.id_role 
            WHERE r.role_name = 'admin' AND r.is_active = 1
        ");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount <= 1) {
            echo json_encode(['success' => false, 'message' => 'Cannot remove the last admin user']);
            return;
        }
    }
    
    // Remove the role
    if (removeUserRole($userId, $roleName, $pdo)) {
        echo json_encode([
            'success' => true, 
            'message' => "Role '$roleName' removed from user '{$targetUser['username']}'"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove role']);
    }
}

function handleCreateRole() {
    global $pdo;
    
    $roleName = trim($_POST['roleName'] ?? '');
    $roleDescription = trim($_POST['roleDescription'] ?? '');
    
    if (empty($roleName)) {
        echo json_encode(['success' => false, 'message' => 'Role name is required']);
        return;
    }
    
    // Validate role name format
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $roleName)) {
        echo json_encode(['success' => false, 'message' => 'Role name can only contain letters, numbers, underscores and hyphens']);
        return;
    }
    
    // Check if role already exists
    $stmt = $pdo->prepare("SELECT id_role FROM roles WHERE role_name = ?");
    $stmt->execute([$roleName]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Role already exists']);
        return;
    }
    
    // Create the role
    $stmt = $pdo->prepare("INSERT INTO roles (role_name, role_description) VALUES (?, ?)");
    if ($stmt->execute([$roleName, $roleDescription])) {
        echo json_encode([
            'success' => true, 
            'message' => "Role '$roleName' created successfully"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create role']);
    }
}

function handleGetUserRoles() {
    global $pdo;
    
    $userId = $_GET['user_id'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    $roles = getUserRoles($userId, $pdo);
    
    echo json_encode([
        'success' => true,
        'roles' => $roles
    ]);
}

function handleGetAllUsers() {
    global $pdo;
    
    try {
        // Get all users with their roles
        $stmt = $pdo->query("
            SELECT u.id_user, u.username, u.email, u.created_at
            FROM users u 
            ORDER BY u.username
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get roles for each user
        foreach ($users as &$user) {
            $user['roles'] = getUserRoles($user['id_user'], $pdo);
        }
        
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching users: ' . $e->getMessage()]);
    }
}

function handleBulkAddRole() {
    global $pdo, $user;
    
    $userIds = json_decode($_POST['userIds'] ?? '[]', true);
    $roleName = $_POST['roleId'] ?? '';
    
    if (empty($userIds) || empty($roleName)) {
        echo json_encode(['success' => false, 'message' => 'User IDs and role name are required']);
        return;
    }
    
    if (!is_array($userIds)) {
        echo json_encode(['success' => false, 'message' => 'Invalid user IDs format']);
        return;
    }
    
    $successCount = 0;
    $errors = [];
    
    foreach ($userIds as $userId) {
        // Validate user exists
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id_user = ?");
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch();
        
        if (!$targetUser) {
            $errors[] = "User ID $userId not found";
            continue;
        }
        
        // Check if user already has this role
        if (userHasRole($userId, $roleName, $pdo)) {
            $errors[] = "User '{$targetUser['username']}' already has role '$roleName'";
            continue;
        }
        
        // Add the role
        if (addUserRole($userId, $roleName, $pdo, $user['id_user'])) {
            $successCount++;
        } else {
            $errors[] = "Failed to add role '$roleName' to user '{$targetUser['username']}'";
        }
    }
    
    $message = "Role '$roleName' added to $successCount user(s)";
    if (!empty($errors)) {
        $message .= ". Errors: " . implode(', ', $errors);
    }
    
    echo json_encode([
        'success' => $successCount > 0,
        'message' => $message,
        'successCount' => $successCount,
        'errors' => $errors
    ]);
}

function handleBulkRemoveRole() {
    global $pdo;
    
    $userIds = json_decode($_POST['userIds'] ?? '[]', true);
    $roleName = $_POST['roleId'] ?? '';
    
    if (empty($userIds) || empty($roleName)) {
        echo json_encode(['success' => false, 'message' => 'User IDs and role name are required']);
        return;
    }
    
    if (!is_array($userIds)) {
        echo json_encode(['success' => false, 'message' => 'Invalid user IDs format']);
        return;
    }
    
    // Prevent removing the last admin role
    if ($roleName === 'admin') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as admin_count
            FROM roles r 
            INNER JOIN role_to_user rtu ON r.id_role = rtu.id_role 
            WHERE r.role_name = 'admin' AND r.is_active = 1
        ");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        // Count how many admins we would remove
        $adminUsersToRemove = 0;
        foreach ($userIds as $userId) {
            if (userHasRole($userId, 'admin', $pdo)) {
                $adminUsersToRemove++;
            }
        }
        
        if ($adminCount - $adminUsersToRemove < 1) {
            echo json_encode(['success' => false, 'message' => 'Cannot remove admin role from all admin users. At least one admin must remain.']);
            return;
        }
    }
    
    $successCount = 0;
    $errors = [];
    
    foreach ($userIds as $userId) {
        // Validate user exists
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id_user = ?");
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch();
        
        if (!$targetUser) {
            $errors[] = "User ID $userId not found";
            continue;
        }
        
        // Check if user has this role
        if (!userHasRole($userId, $roleName, $pdo)) {
            $errors[] = "User '{$targetUser['username']}' does not have role '$roleName'";
            continue;
        }
        
        // Remove the role
        if (removeUserRole($userId, $roleName, $pdo)) {
            $successCount++;
        } else {
            $errors[] = "Failed to remove role '$roleName' from user '{$targetUser['username']}'";
        }
    }
    
    $message = "Role '$roleName' removed from $successCount user(s)";
    if (!empty($errors)) {
        $message .= ". Errors: " . implode(', ', $errors);
    }
    
    echo json_encode([
        'success' => $successCount > 0,
        'message' => $message,
        'successCount' => $successCount,
        'errors' => $errors
    ]);
}
?>
