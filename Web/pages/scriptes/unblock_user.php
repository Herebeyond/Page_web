<?php
// filepath: c:\Users\baill\OneDrive\Desktop\Docker\html\test\Web\pages\scriptes\unblock_user.php

require_once '../../login/db.php'; // Include the database connection

header('Content-Type: application/json');

if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $userId = (int)$_GET['user'];

    try {
        // Update the user's blocked status to 0 (unblocked)
        $stmt = $pdo->prepare("UPDATE users SET blocked = null WHERE id = ?");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'User unblocked successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found or already unblocked'
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