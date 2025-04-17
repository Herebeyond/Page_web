<?php

require_once '../../login/db.php'; // Connexion à la base

header('Content-Type: application/json');

if (isset($_GET['user'])) { // Make the function fetchUserInfo work
    $userId = trim($_GET['user']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
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
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No user specified'
    ]);
}