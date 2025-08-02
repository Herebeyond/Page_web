<?php
require_once "../blueprints/page_init.php";

// Ensure user is admin
if (!isset($_SESSION['user']) || !isset($user_roles) || !in_array('admin', $user_roles)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Base path for places folders
$basePath = realpath('../../images') . DIRECTORY_SEPARATOR . 'places' . DIRECTORY_SEPARATOR;

// Debug: Check if the base path is valid
if (!$basePath || $basePath === false) {
    echo json_encode(['success' => false, 'message' => 'Could not resolve images directory path']);
    exit;
}

// Ensure places directory exists
if (!is_dir($basePath)) {
    if (!mkdir($basePath, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create places directory at: ' . $basePath]);
        exit;
    }
}

function createPlaceSlug($name) {
    // Convert to lowercase, replace spaces and special chars with hyphens
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug); // Remove multiple consecutive hyphens
    $slug = trim($slug, '-'); // Remove leading/trailing hyphens
    return $slug;
}

function createPlaceFolders($basePath, $slug) {
    $placePath = $basePath . $slug;
    $folders = [
        $placePath,
        $placePath . DIRECTORY_SEPARATOR . 'gallery',
        $placePath . DIRECTORY_SEPARATOR . 'thumbs'
    ];
    
    foreach ($folders as $folder) {
        if (!is_dir($folder)) {
            if (!mkdir($folder, 0755, true)) {
                return false;
            }
        }
    }
    return true;
}

function checkFolderExists($basePath, $slug) {
    return is_dir($basePath . $slug);
}

function deletePlaceFolder($basePath, $slug) {
    $placePath = $basePath . $slug;
    if (!is_dir($placePath)) {
        return true; // Already doesn't exist
    }
    
    // Recursively delete folder and contents
    function deleteDirectory($dir) {
        if (!is_dir($dir)) return false;
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? deleteDirectory($path) : unlink($path);
        }
        return rmdir($dir);
    }
    
    return deleteDirectory($placePath);
}

function getAllPlaceFolders($basePath) {
    $folders = [];
    if (!is_dir($basePath)) {
        return $folders;
    }
    
    $items = scandir($basePath);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $itemPath = $basePath . $item;
        if (is_dir($itemPath)) {
            $folders[] = [
                'slug' => $item,
                'name' => ucwords(str_replace('-', ' ', $item)),
                'path' => $itemPath,
                'has_images' => count(glob($itemPath . DIRECTORY_SEPARATOR . 'gallery' . DIRECTORY_SEPARATOR . '*')) > 0
            ];
        }
    }
    return $folders;
}

switch ($action) {
    case 'check_folder_exists':
        $name = $input['name'] ?? '';
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            break;
        }
        
        try {
            $slug = createPlaceSlug($name);
            $exists = checkFolderExists($basePath, $slug);
            
            echo json_encode([
                'success' => true,
                'exists' => $exists,
                'slug' => $slug,
                'folder_path' => 'images/places/' . $slug,
                'debug_base_path' => $basePath // Remove this after debugging
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error checking folder: ' . $e->getMessage()]);
        }
        break;
        
    case 'create_place_folder':
        $name = $input['name'] ?? '';
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            break;
        }
        
        $slug = createPlaceSlug($name);
        $success = createPlaceFolders($basePath, $slug);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Folder structure created successfully',
                'slug' => $slug,
                'folder_path' => 'images/places/' . $slug
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create folder structure']);
        }
        break;
        
    case 'delete_place_folder':
        $slug = $input['slug'] ?? '';
        if (empty($slug)) {
            echo json_encode(['success' => false, 'message' => 'Slug is required']);
            break;
        }
        
        $success = deletePlaceFolder($basePath, $slug);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Folder deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete folder']);
        }
        break;
        
    case 'list_all_folders':
        $folders = getAllPlaceFolders($basePath);
        echo json_encode([
            'success' => true,
            'folders' => $folders,
            'total_count' => count($folders)
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
