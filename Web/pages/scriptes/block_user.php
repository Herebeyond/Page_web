<?php
// filepath: c:\Users\baill\OneDrive\Desktop\Docker\html\test\Web\pages\scriptes\block_user.php

require_once '../../login/db.php'; // Include the database connection

header('Content-Type: application/json');

if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $userId = (int)$_GET['user'];

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