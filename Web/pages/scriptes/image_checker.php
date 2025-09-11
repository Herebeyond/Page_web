<?php
// Disable all HTML error output for clean JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include functions for slug generation
require_once 'functions.php';

// Include functions for consistent slug generation
require_once 'functions.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    if ($action === 'check_place_image') {
        // Accept either 'name' (original) or 'slug' (pre-generated) for backward compatibility
        $name = $input['name'] ?? '';
        $slug = $input['slug'] ?? '';
        
        if (empty($name) && empty($slug)) {
            echo json_encode(['success' => false, 'message' => 'Name or slug required']);
            exit;
        }
        
        // Generate slug from name using server-side function for consistency
        if (!empty($name)) {
            $slug = createSafeSlug($name);
        }
        
        if (empty($slug)) {
            echo json_encode(['success' => false, 'message' => 'Invalid name for slug generation']);
            exit;
        }
        
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $basePath = __DIR__ . '/../../images/places/' . $slug . '/main.';
        
        foreach ($extensions as $ext) {
            $fullPath = $basePath . $ext;
            if (file_exists($fullPath) && is_readable($fullPath)) {
                echo json_encode([
                    'success' => true, 
                    'found' => true, 
                    'extension' => $ext,
                    'path' => "../images/places/{$slug}/main.{$ext}",
                    'absolute_path' => "/test/Web/images/places/{$slug}/main.{$ext}"
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
