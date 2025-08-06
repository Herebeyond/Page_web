<?php
session_start();
require_once '../../login/db.php';

header('Content-Type: application/json');

// Handle different request types
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file uploads (admin only)
    if (isset($_FILES['image'])) {
        // Check if user is admin for uploads
        if (!isset($_SESSION['user']) || !isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied - Admin required for uploads']);
            exit;
        }
        handleImageUpload();
    } else {
        // Handle JSON requests
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'list_images':
                    // Allow anyone to list images (no admin check needed)
                    listImages($input['slug']);
                    break;
                case 'rename_image':
                    // Require admin access for renaming
                    if (!isset($_SESSION['user']) || !isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Access denied - Admin required']);
                        exit;
                    }
                    renameImage($input);
                    break;
                case 'delete_image':
                    // Require admin access for deleting
                    if (!isset($_SESSION['user']) || !isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Access denied - Admin required']);
                        exit;
                    }
                    deleteImage($input);
                    break;
                default:
                    // Other actions require admin access
                    if (!isset($_SESSION['user']) || !isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Access denied - Admin required']);
                        exit;
                    }
                        echo json_encode(['success' => false, 'message' => 'Unknown action']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request format']);
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

function renameImage($data) {
    $slug = $data['slug'] ?? '';
    $oldName = $data['old_name'] ?? '';
    $newName = $data['new_name'] ?? '';
    
    if (empty($slug) || empty($oldName) || empty($newName)) {
        echo json_encode(['success' => false, 'message' => 'Slug, old name, and new name are required']);
        return;
    }
    
    // Sanitize the new name
    $newName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $newName);
    if (empty($newName)) {
        echo json_encode(['success' => false, 'message' => 'Invalid new name']);
        return;
    }
    
    $placeDir = '../../images/places/' . $slug;
    
    if (!is_dir($placeDir)) {
        echo json_encode(['success' => false, 'message' => 'Place directory not found']);
        return;
    }
    
    // Find the old file
    $oldFile = null;
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    foreach ($allowedExtensions as $ext) {
        $testFile = $placeDir . '/' . $oldName . '.' . $ext;
        if (file_exists($testFile)) {
            $oldFile = $testFile;
            $extension = $ext;
            break;
        }
    }
    
    if (!$oldFile) {
        echo json_encode(['success' => false, 'message' => 'Original file not found']);
        return;
    }
    
    // Prevent renaming main images
    if (strpos(basename($oldFile), 'main.') === 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot rename main image']);
        return;
    }
    
    $newFile = $placeDir . '/' . $newName . '.' . $extension;
    
    // Check if new name already exists
    if (file_exists($newFile)) {
        echo json_encode(['success' => false, 'message' => 'A file with that name already exists']);
        return;
    }
    
    if (rename($oldFile, $newFile)) {
        echo json_encode(['success' => true, 'message' => 'Image renamed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to rename image']);
    }
}

function deleteImage($data) {
    $slug = $data['slug'] ?? '';
    $imageName = $data['image_name'] ?? '';
    
    if (empty($slug) || empty($imageName)) {
        echo json_encode(['success' => false, 'message' => 'Slug and image name are required']);
        return;
    }
    
    $placeDir = '../../images/places/' . $slug;
    
    if (!is_dir($placeDir)) {
        echo json_encode(['success' => false, 'message' => 'Place directory not found']);
        return;
    }
    
    // Find the file
    $fileToDelete = null;
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    foreach ($allowedExtensions as $ext) {
        $testFile = $placeDir . '/' . $imageName . '.' . $ext;
        if (file_exists($testFile)) {
            $fileToDelete = $testFile;
            break;
        }
    }
    
    if (!$fileToDelete) {
        echo json_encode(['success' => false, 'message' => 'File not found']);
        return;
    }
    
    // Prevent deleting main images
    if (strpos(basename($fileToDelete), 'main.') === 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete main image']);
        return;
    }
    
    if (unlink($fileToDelete)) {
        echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
    }
}
?>
