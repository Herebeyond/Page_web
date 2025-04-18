<?php

require_once '../../login/db.php'; // Database connection

header('Content-Type: application/json');

if (isset($_GET['user'])) { // Check if the 'user' parameter is provided
    $username = trim($_GET['user']); // Get the username from the query parameter

    try {
        // Query the database to fetch user information by username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Return user information as JSON
            echo json_encode([
                'success' => true,
                'id' => $user['id'],
                'icon' => $user['icon'],
                'username' => $user['username'],
                'blocked' => $user['blocked'],
                'admin' => $user['admin'],
                'email' => $user['email'],
                'created_at' => $user['created_at'],
                'last_updated_at' => $user['last_updated_at'],
            ]);
        } else {
            // User not found
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    // No username provided
    echo json_encode([
        'success' => false,
        'message' => 'No username specified'
    ]);
}