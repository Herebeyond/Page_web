<?php
require_once "../blueprints/page_init.php";

// Ensure user is admin
if (!isset($_SESSION['user']) || !isset($user_roles) || !in_array('admin', $user_roles)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

// Parse JSON input using shared secure function
$input = parseSecureJsonInput();
if ($input === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$action = $input['action'] ?? '';

// Base path for places folders
$basePath = realpath('../../images/places');
if ($basePath === false) {
    echo json_encode(['success' => false, 'message' => 'Could not resolve images directory path']);
    exit;
}

// Ensure places directory exists
if (!is_dir($basePath)) {
    if (!mkdir($basePath, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create places directory']);
        exit;
    }
}

function createPlaceSlug($name) {
    // Use shared secure slug creation function
    return createSecureSlug($name);
}

function createPlaceFolders($basePath, $slug) {
    // Construct safe path using shared function
    $placePath = constructSafePlacePath($slug, $basePath);
    if ($placePath === false) {
        return false;
    }
    
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
    $safePath = constructSafePlacePath($slug, $basePath);
    if ($safePath === false) {
        return false;
    }
    return is_dir($safePath);
}

function deletePlaceFolder($basePath, $slug) {
    $placePath = constructSafePlacePath($slug, $basePath);
    if ($placePath === false) {
        return false;
    }
    
    if (!is_dir($placePath)) {
        return true; // Already doesn't exist
    }
    
    // Recursively delete folder and contents with safe directory traversal
    function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        try {
            $iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
            
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            return rmdir($dir);
        } catch (Exception $e) {
            error_log('Error deleting directory: ' . $e->getMessage());
            return false;
        }
    }
    
    return deleteDirectory($placePath);
}

function getAllPlaceFolders($basePath) {
    $folders = [];
    if (!is_dir($basePath)) {
        return $folders;
    }
    
    try {
        $iterator = new DirectoryIterator($basePath);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }
            
            $itemName = $fileInfo->getFilename();
            
            // Validate the folder name for security
            if (validateAndSanitizeSlug($itemName) === false) {
                continue; // Skip potentially dangerous folder names
            }
            
            $itemPath = $fileInfo->getRealPath();
            
            // Verify the path is within our base directory
            if (strpos($itemPath, $basePath) !== 0) {
                continue; // Skip paths outside our base directory
            }
            
            $galleryPath = $itemPath . DIRECTORY_SEPARATOR . 'gallery';
            $hasImages = is_dir($galleryPath) && count(glob($galleryPath . DIRECTORY_SEPARATOR . '*')) > 0;
            
            $folders[] = [
                'slug' => $itemName,
                'name' => ucwords(str_replace('-', ' ', $itemName)),
                'path' => IMAGES_PLACES_PATH . $itemName, // Relative path for web access
                'has_images' => $hasImages
            ];
        }
    } catch (Exception $e) {
        error_log('Error reading directories: ' . $e->getMessage());
        return [];
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
            if ($slug === false) {
                echo json_encode(['success' => false, 'message' => 'Invalid place name provided']);
                break;
            }
            
            $exists = checkFolderExists($basePath, $slug);
            
            echo json_encode([
                'success' => true,
                'exists' => $exists,
                'slug' => $slug,
                'folder_path' => IMAGES_PLACES_PATH . $slug
            ]);
        } catch (Exception $e) {
            error_log('Error checking folder: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error checking folder']);
        }
        break;
        
    case 'create_place_folder':
        $name = $input['name'] ?? '';
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            break;
        }
        
        try {
            $slug = createPlaceSlug($name);
            if ($slug === false) {
                echo json_encode(['success' => false, 'message' => 'Invalid place name provided']);
                break;
            }
            
            if (checkFolderExists($basePath, $slug)) {
                echo json_encode(['success' => false, 'message' => 'Place already exists']);
                break;
            }
            
            $success = createPlaceFolders($basePath, $slug);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Folder structure created successfully',
                    'slug' => $slug,
                    'folder_path' => IMAGES_PLACES_PATH . $slug
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create folder structure']);
            }
        } catch (Exception $e) {
            error_log('Error creating place folder: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error creating place folder']);
        }
        break;
        
    case 'delete_place_folder':
        $slug = $input['slug'] ?? '';
        if (empty($slug)) {
            echo json_encode(['success' => false, 'message' => 'Slug is required']);
            break;
        }
        
        try {
            $validatedSlug = validateAndSanitizeSlug($slug);
            if ($validatedSlug === false) {
                echo json_encode(['success' => false, 'message' => 'Invalid place identifier provided']);
                break;
            }
            
            $success = deletePlaceFolder($basePath, $validatedSlug);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Folder deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete folder']);
            }
        } catch (Exception $e) {
            error_log('Error deleting place folder: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error deleting place folder']);
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
