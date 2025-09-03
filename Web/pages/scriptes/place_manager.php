<?php
// Disable all HTML error output for clean JSON responses
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../database/db.php';

// Check if user is admin
if (!isset($_SESSION['user']) || !isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    switch ($input['action']) {
        case 'update_place':
            updatePlace($input);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function updatePlace($data) {
    global $pdo;
    
    $placeId = $data['place_id'] ?? null;
    if (!$placeId || !is_numeric($placeId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid place ID']);
        return;
    }
    
    // Build update query dynamically based on provided fields
    $allowedFields = ['name_IP', 'description_IP', 'other_names_IP', 'type_IP'];
    $updateFields = [];
    $values = [];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    
    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
        return;
    }
    
    $values[] = $placeId; // Add place ID for WHERE clause
    
    try {
        $sql = "UPDATE interest_points SET " . implode(', ', $updateFields) . " WHERE id_IP = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Place updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or place not found']);
        }
        
    } catch (PDOException $e) {
        error_log("Database error in updatePlace: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
