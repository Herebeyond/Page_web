<?php
// Simplified place_image_manager.php that actually works
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Only POST method allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
$slug = $_POST['slug'] ?? '';

// Simple slug validation
if (!empty($slug) && !preg_match('/^[a-z0-9_-]+$/', $slug)) {
    echo json_encode(['success' => false, 'error' => 'Invalid slug format']);
    exit;
}

if ($action === 'listImages') {
    if (empty($slug)) {
        echo json_encode(['success' => false, 'error' => 'Slug parameter is required']);
        exit;
    }
    
    // Simple path construction
    $placePath = __DIR__ . '/../../images/places/' . $slug;
    
    if (!is_dir($placePath)) {
        echo json_encode([
            'success' => true,
            'mainImage' => null,
            'images' => [],
            'total' => 0,
            'message' => 'Directory does not exist'
        ]);
        exit;
    }
    
    // Look for main image
    $mainImage = null;
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    foreach ($allowedExtensions as $ext) {
        if (file_exists($placePath . '/main.' . $ext)) {
            $mainImage = [
                'name' => 'main.' . $ext,
                'full_path' => "../images/places/{$slug}/main.{$ext}",
                'thumb_path' => "../images/places/{$slug}/main.{$ext}"
            ];
            break;
        }
    }
    
    // List all other images
    $images = [];
    $files = scandir($placePath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $allowedExtensions) && strpos($file, 'main.') !== 0) {
            $filePath = $placePath . DIRECTORY_SEPARATOR . $file;
            $images[] = [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'full_name' => $file,
                'full_path' => "../images/places/{$slug}/{$file}",
                'thumb_path' => "../images/places/{$slug}/{$file}",
                'size' => file_exists($filePath) ? filesize($filePath) : 0
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'mainImage' => $mainImage,
        'images' => $images,
        'total' => count($images)
    ]);
    
} elseif ($action === 'upload_main_image' || $action === 'upload_gallery_image') {
    if (empty($slug)) {
        echo json_encode(['success' => false, 'error' => 'Slug parameter is required']);
        exit;
    }
    
    if (!isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'error' => 'No image file provided']);
        exit;
    }
    
    $placePath = __DIR__ . '/../../images/places/' . $slug;
    
    // Create place directory if it doesn't exist
    if (!is_dir($placePath)) {
        if (!mkdir($placePath, 0755, true)) {
            echo json_encode(['success' => false, 'error' => 'Failed to create directory']);
            exit;
        }
    }
    
    $uploadedFile = $_FILES['image'];
    
    // Validate file
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Upload error: ' . $uploadedFile['error']]);
        exit;
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($uploadedFile['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Only images are allowed.']);
        exit;
    }
    
    // Check file size (max 10MB)
    if ($uploadedFile['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 10MB.']);
        exit;
    }
    
    // Get file extension and sanitize filename
    $pathInfo = pathinfo($uploadedFile['name']);
    $extension = strtolower($pathInfo['extension']);
    
    // Validate extension against allowed list
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file extension.']);
        exit;
    }
    
    if ($action === 'upload_main_image') {
        // For main image, always name it "main"
        $targetFile = $placePath . DIRECTORY_SEPARATOR . 'main.' . $extension;
        
        // Remove any existing main images
        foreach ($allowedExtensions as $ext) {
            $existingFile = $placePath . DIRECTORY_SEPARATOR . 'main.' . $ext;
            if (file_exists($existingFile)) {
                unlink($existingFile);
            }
        }
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
            echo json_encode(['success' => true, 'message' => 'Main image uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
        }
        
    } elseif ($action === 'upload_gallery_image') {
        // For gallery images, sanitize filename
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pathInfo['filename']);
        if (empty($fileName)) {
            $fileName = 'image_' . time();
        }
        
        $targetFile = $placePath . DIRECTORY_SEPARATOR . $fileName . '.' . $extension;
        
        // If file exists, add a number suffix
        $counter = 1;
        while (file_exists($targetFile)) {
            $targetFile = $placePath . DIRECTORY_SEPARATOR . $fileName . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
            echo json_encode(['success' => true, 'message' => 'Gallery image uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
        }
    }
    
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
}
?>