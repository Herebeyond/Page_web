<?php
// filepath: c:\Users\baill\OneDrive\Desktop\Docker\html\test\Web\pages\scriptes\search_user.php

require_once '../../login/db.php'; // Include the database connection

header('Content-Type: application/json');

if (isset($_GET['query']) && strlen($_GET['query']) > 1) {
    $query = '%' . trim($_GET['query']) . '%';

    try {
        $stmt = $pdo->prepare("SELECT id_user, username FROM users WHERE username LIKE ? ORDER BY username LIMIT 10");
        $stmt->execute([$query]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing query'
    ]);
}