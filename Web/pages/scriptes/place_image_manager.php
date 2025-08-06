<?php
session_start();
require_once '../../login/db.php';
require_once 'functions.php'; // Include shared functions

header('Content-Type: application/json');

// Security function to validate and sanitize slug
function validateAndSanitizeSlug($slug) {
    if (empty($slug)) {
        return false;
    }
    
    // Remove any path traversal attempts and dangerous characters
    $slug = str_replace(['../', '..\\', '/', '\\', '.', '~'], '', $slug);
    
    // Only allow alphanumeric characters, hyphens, and underscores
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $slug)) {
        return false;
    }
    
    // Limit length to prevent buffer overflow attacks
    if (strlen($slug) > 100) {
        return false;
    }
    
    return $slug;
}

// Security function to construct safe path
function constructSafePlacePath($slug) {
    $sanitizedSlug = validateAndSanitizeSlug($slug);
    if ($sanitizedSlug === false) {
        return false;
    }
    
    // Construct path with sanitized slug
    $basePlacesDir = realpath('../../images/places');
    if ($basePlacesDir === false) {
        return false;
    }
    
    $targetPath = $basePlacesDir . DIRECTORY_SEPARATOR . $sanitizedSlug;
    
    // Ensure the constructed path is within the allowed directory
    $realTargetPath = realpath($targetPath);
    if ($realTargetPath !== false) {
        // Path exists, verify it's within base directory
        if (strpos($realTargetPath, $basePlacesDir) !== 0) {
            return false;
        }
    } else {
        // Path doesn't exist yet, verify the parent directory is safe
        $parentPath = dirname($targetPath);
        if (realpath($parentPath) !== $basePlacesDir) {
            return false;
        }
    }
    
    return $targetPath;
}

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
        // Handle JSON requests with input validation
        $rawInput = file_get_contents('php://input');
        if ($rawInput === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid request data']);
            exit;
        }
        
        $input = json_decode($rawInput, true);
        if ($input === null) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON format']);
            exit;
        }
        
        if (isset($input['action'])) {
            switch ($input['action']) {
                case 'list_images':
                    // Validate slug before processing
                    if (!isset($input['slug']) || validateAndSanitizeSlug($input['slug']) === false) {
                        echo json_encode(['success' => false, 'message' => 'Invalid or missing slug']);
                        exit;
                    }
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
    
    // Validate and sanitize the slug
    $sanitizedSlug = validateAndSanitizeSlug($slug);
    if ($sanitizedSlug === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid slug format']);
        return;
    }
    
    // Construct safe path
    $placeDir = constructSafePlacePath($sanitizedSlug);
    if ($placeDir === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid directory path']);
        return;
    }
    
    // Create place directory if it doesn't exist
    if (!is_dir($placeDir)) {
        if (!mkdir($placeDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create directory']);
            return;
        }
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
    
    // Get file extension and sanitize filename
    $pathInfo = pathinfo($uploadedFile['name']);
    $extension = strtolower($pathInfo['extension']);
    
    // Validate extension against allowed list
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file extension.']);
        return;
    }
    
    if ($action === 'upload_main_image') {
        // For main image, always name it "main"
        $targetFile = $placeDir . DIRECTORY_SEPARATOR . 'main.' . $extension;
        
        // Remove any existing main images
        foreach ($allowedExtensions as $ext) {
            $existingFile = $placeDir . DIRECTORY_SEPARATOR . 'main.' . $ext;
            if (file_exists($existingFile)) {
                unlink($existingFile);
            }
        }
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
            echo json_encode(['success' => true, 'message' => 'Main image uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        }
        
    } elseif ($action === 'upload_gallery_image') {
        // For gallery images, sanitize filename
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pathInfo['filename']);
        if (empty($fileName)) {
            $fileName = 'image_' . time();
        }
        
        $targetFile = $placeDir . DIRECTORY_SEPARATOR . $fileName . '.' . $extension;
        
        // If file exists, add a number suffix
        $counter = 1;
        while (file_exists($targetFile)) {
            $targetFile = $placeDir . DIRECTORY_SEPARATOR . $fileName . '_' . $counter . '.' . $extension;
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
    // Validate and sanitize the slug
    $sanitizedSlug = validateAndSanitizeSlug($slug);
    if ($sanitizedSlug === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid slug format']);
        return;
    }
    
    // Construct safe path
    $placeDir = constructSafePlacePath($sanitizedSlug);
    if ($placeDir === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid directory path']);
        return;
    }
    
    if (!is_dir($placeDir)) {
        echo json_encode(['success' => true, 'images' => []]);
        return;
    }
    
    $images = [];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Use DirectoryIterator for safer directory traversal
    try {
        $iterator = new DirectoryIterator($placeDir);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isFile()) {
                continue;
            }
            
            $fileName = $fileInfo->getFilename();
            $pathInfo = pathinfo($fileName);
            $extension = strtolower($pathInfo['extension'] ?? '');
            
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }
            
            // Skip main image in gallery listing
            if (strpos($fileName, 'main.') === 0) {
                continue;
            }
            
            // Construct safe relative path for web access
            $relativePath = '../images/places/' . $sanitizedSlug . '/' . $fileName;
            
            $images[] = [
                'name' => $pathInfo['filename'],
                'full_name' => $fileName,
                'full_path' => $relativePath,
                'thumb_path' => $relativePath, // For now, use same path for thumbnails
                'size' => $fileInfo->getSize()
            ];
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error reading directory']);
        return;
    }
    
    echo json_encode(['success' => true, 'images' => $images]);
}

function renameImage($data) {
    $slug = $data['slug'] ?? '';
    $oldName = $data['old_name'] ?? '';
    $newName = $data['new_name'] ?? '';
    
    // Validate and sanitize the slug
    $sanitizedSlug = validateAndSanitizeSlug($slug);
    if ($sanitizedSlug === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid slug format']);
        return;
    }
    
    if (empty($oldName) || empty($newName)) {
        echo json_encode(['success' => false, 'message' => 'Old name and new name are required']);
        return;
    }
    
    // Sanitize the new name
    $newName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $newName);
    if (empty($newName)) {
        echo json_encode(['success' => false, 'message' => 'Invalid new name']);
        return;
    }
    
    // Construct safe path
    $placeDir = constructSafePlacePath($sanitizedSlug);
    if ($placeDir === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid directory path']);
        return;
    }
    
    if (!is_dir($placeDir)) {
        echo json_encode(['success' => false, 'message' => 'Place directory not found']);
        return;
    }
    
    // Find the old file safely
    $oldFile = null;
    $extension = null;
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    foreach ($allowedExtensions as $ext) {
        $testFile = $placeDir . DIRECTORY_SEPARATOR . $oldName . '.' . $ext;
        if (file_exists($testFile) && is_file($testFile)) {
            // Verify the file is within our expected directory
            if (dirname(realpath($testFile)) === realpath($placeDir)) {
                $oldFile = $testFile;
                $extension = $ext;
                break;
            }
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
    
    $newFile = $placeDir . DIRECTORY_SEPARATOR . $newName . '.' . $extension;
    
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
    
    // Validate and sanitize the slug
    $sanitizedSlug = validateAndSanitizeSlug($slug);
    if ($sanitizedSlug === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid slug format']);
        return;
    }
    
    if (empty($imageName)) {
        echo json_encode(['success' => false, 'message' => 'Image name is required']);
        return;
    }
    
    // Sanitize image name
    $imageName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $imageName);
    if (empty($imageName)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image name']);
        return;
    }
    
    // Construct safe path
    $placeDir = constructSafePlacePath($sanitizedSlug);
    if ($placeDir === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid directory path']);
        return;
    }
    
    if (!is_dir($placeDir)) {
        echo json_encode(['success' => false, 'message' => 'Place directory not found']);
        return;
    }
    
    // Find the file safely
    $fileToDelete = null;
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    foreach ($allowedExtensions as $ext) {
        $testFile = $placeDir . DIRECTORY_SEPARATOR . $imageName . '.' . $ext;
        if (file_exists($testFile) && is_file($testFile)) {
            // Verify the file is within our expected directory
            if (dirname(realpath($testFile)) === realpath($placeDir)) {
                $fileToDelete = $testFile;
                break;
            }
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
