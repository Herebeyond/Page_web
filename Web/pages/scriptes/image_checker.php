<?php
// Disable all HTML error output for clean JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    if ($action === 'check_place_image') {
        $slug = $input['slug'] ?? '';
        if (empty($slug)) {
            echo json_encode(['success' => false, 'message' => 'Slug required']);
            exit;
        }
        
        // Sanitize slug for security
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));
        
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $basePath = __DIR__ . '/../../images/places/' . $slug . '/main.';
        
        foreach ($extensions as $ext) {
            $fullPath = $basePath . $ext;
            if (file_exists($fullPath) && is_readable($fullPath)) {
                echo json_encode([
                    'success' => true, 
                    'found' => true, 
                    'extension' => $ext,
                    'path' => "../images/places/{$slug}/main.{$ext}"
                ]);
                exit;
            }
        }
        
        // No image found
        echo json_encode(['success' => true, 'found' => false]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
