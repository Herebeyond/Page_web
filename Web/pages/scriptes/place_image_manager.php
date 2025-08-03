<?php
session_start();
require_once '../../login/db.php';

// Check if user is admin
if (!isset($_SESSION['user']) || !isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

// Handle different request types
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file uploads
    if (isset($_FILES['image'])) {
        handleImageUpload();
    } else {
        // Handle JSON requests
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'list_images':
                    listImages($input['slug']);
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Unknown action']);
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function handleImageUpload() {
    $action = $_POST['action'] ?? '';
    $slug = $_POST['slug'] ?? '';
    
    if (empty($slug)) {
        echo json_encode(['success' => false, 'message' => 'Slug is required']);
        return;
    }
    
    // Create place folder if it doesn't exist
    $placesDir = '../../images/places';
    $placeDir = $placesDir . '/' . $slug;
    
    if (!is_dir($placesDir)) {
        mkdir($placesDir, 0755, true);
    }
    
    if (!is_dir($placeDir)) {
        mkdir($placeDir, 0755, true);
    }
    
    $uploadedFile = $_FILES['image'];
    
    // Validate file
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $uploadedFile['error']]);
        return;
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($uploadedFile['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images are allowed.']);
        return;
    }
    
    // Check file size (max 10MB)
    if ($uploadedFile['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 10MB.']);
        return;
    }
    
    // Get file extension
    $pathInfo = pathinfo($uploadedFile['name']);
    $extension = strtolower($pathInfo['extension']);
    
    if ($action === 'upload_main_image') {
        // For main image, always name it "main"
        $targetFile = $placeDir . '/main.' . $extension;
        
        // Remove any existing main images
        $existingMainImages = glob($placeDir . '/main.*');
        foreach ($existingMainImages as $existingImage) {
            unlink($existingImage);
        }
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
            echo json_encode(['success' => true, 'message' => 'Main image uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        }
        
    } elseif ($action === 'upload_gallery_image') {
        // For gallery images, use original name or generate unique name
        $fileName = $pathInfo['filename'];
        $targetFile = $placeDir . '/' . $fileName . '.' . $extension;
        
        // If file exists, add a number suffix
        $counter = 1;
        while (file_exists($targetFile)) {
            $targetFile = $placeDir . '/' . $fileName . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
            echo json_encode(['success' => true, 'message' => 'Gallery image uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function listImages($slug) {
    if (empty($slug)) {
        echo json_encode(['success' => false, 'message' => 'Slug is required']);
        return;
    }
    
    $placeDir = '../../images/places/' . $slug;
    
    if (!is_dir($placeDir)) {
        echo json_encode(['success' => true, 'images' => []]);
        return;
    }
    
    $images = [];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    $files = scandir($placeDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $placeDir . '/' . $file;
        if (!is_file($filePath)) continue;
        
        $pathInfo = pathinfo($file);
        $extension = strtolower($pathInfo['extension'] ?? '');
        
        if (in_array($extension, $allowedExtensions)) {
            // Skip main image in gallery listing
            if (strpos($file, 'main.') === 0) continue;
            
            $images[] = [
                'name' => $pathInfo['filename'],
                'full_name' => $file,
                'full_path' => '../images/places/' . $slug . '/' . $file,
                'thumb_path' => '../images/places/' . $slug . '/' . $file, // For now, use same path for thumbnails
                'size' => filesize($filePath)
            ];
        }
    }
    
    echo json_encode(['success' => true, 'images' => $images]);
}
?>
