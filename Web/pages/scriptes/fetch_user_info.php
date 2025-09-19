<?php
// Disable all HTML error output for clean JSON responses
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../../login/db.php'; // Database connection

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

if (isset($_GET['user'])) { // Check if the 'user' parameter is provided
    $username = trim($_GET['user']); // Get the username from the query parameter
    
    // Validate username input (security: prevent injection attempts)
    if (empty($username) || strlen($username) > 255) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username parameter'
        ]);
        exit;
    }

    try {
        // Query the database to fetch user information by username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Try to fetch roles, but don't fail if roles table doesn't exist
            $roles = [];
            try {
                $stmt = $pdo->prepare("SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?");
                $stmt->execute([$user['id_user']]);
                $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                // Roles table might not exist, continue without roles
                $roles = [];
            }

            // Return user information as JSON
            echo json_encode([
                'success' => true,
                'user' => [
                    'id_user' => $user['id_user'],
                    'icon' => $user['icon'],
                    'username' => $user['username'],
                    'blocked' => $user['blocked'],
                    'is_active' => $user['is_active'],
                    'roles' => $roles,
                    'email' => $user['email'],
                    'created_at' => $user['created_at'],
                ]
            ]);
        } else {
            // User not found
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
    } catch (PDOException $e) {
        // Handle database errors (security: log but don't expose internal details)
        error_log("Database error in fetch_user_info.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);
    }
} else {
    // No username provided
    echo json_encode([
        'success' => false,
        'message' => 'No username specified'
    ]);
}