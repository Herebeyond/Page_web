<?php
session_start();

require_once '../../login/db.php'; // Include the database connection

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['roles']) || !in_array('admin', $_SESSION['user']['roles'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. Admin privileges required.'
    ]);
    exit;
}

// Verify PDO connection exists
if (!isset($pdo) || $pdo === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]);
    exit;
}

if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $userId = (int)$_GET['user'];
    
    // Prevent admin from blocking themselves
    if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $userId) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot block yourself'
        ]);
        exit;
    }

    try {
        // Update the user's blocked status to 1 (blocked)
        $stmt = $pdo->prepare("UPDATE users SET blocked = NOW() WHERE id = ?");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'User blocked successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found or already blocked'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing user ID'
    ]);
}