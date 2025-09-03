<?php
// Disable all HTML error output for clean JSON responses
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../database/db.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Not logged in']);
    exit;
}

// Get user roles from database
$user_roles = [];
if (isset($_SESSION['user_roles'])) {
    $user_roles = $_SESSION['user_roles'];
} else {
    // Fetch user roles from database if not in session
    try {
        // Validate PDO connection
        if (!$pdo) {
            throw new Exception('Database connection failed');
        }
        
        $stmt = $pdo->prepare("SELECT r.name FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $user_roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $_SESSION['user_roles'] = $user_roles;
    } catch (Exception $e) {
        error_log('Error fetching user roles: ' . $e->getMessage());
        $user_roles = [];
    }
}

// Ensure user is admin
if (!in_array('admin', $user_roles)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Admin required']);
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
    return createSafeSlug($name);
}

function createPlaceFolders($basePath, $slug) {
    // Construct safe path for the place
    $placePath = $basePath . DIRECTORY_SEPARATOR . $slug;
    
    // Validate the path for security
    if (!isPathSafe($placePath, $basePath)) {
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
    $safePath = $basePath . DIRECTORY_SEPARATOR . $slug;
    
    // Validate the path for security
    if (!isPathSafe($safePath, $basePath)) {
        return false;
    }
    return is_dir($safePath);
}

function deletePlaceFolder($basePath, $slug) {
    $debugSteps = [];
    $debugSteps[] = "deletePlaceFolder START - basePath: $basePath, slug: $slug";
    
    $placePath = $basePath . DIRECTORY_SEPARATOR . $slug;
    $debugSteps[] = "Constructed placePath: $placePath";
    
    // Validate the path for security
    if (!isPathSafe($placePath, $basePath)) {
        $debugSteps[] = "ERROR: Path not safe";
        // Store debug info globally for access in the action handler
        $GLOBALS['delete_debug'] = $debugSteps;
        return false;
    }
    $debugSteps[] = "Path safety check passed";
    
    if (!is_dir($placePath)) {
        $debugSteps[] = "Directory doesn't exist - returning true";
        $GLOBALS['delete_debug'] = $debugSteps;
        return true; // Already doesn't exist
    }
    $debugSteps[] = "Directory exists, proceeding with deletion";
    
    // Check permissions on the directory
    $debugSteps[] = "Directory permissions: " . decoct(fileperms($placePath) & 0777);
    $debugSteps[] = "Directory owner: " . fileowner($placePath);
    $debugSteps[] = "Directory group: " . filegroup($placePath);
    $debugSteps[] = "Directory is readable: " . (is_readable($placePath) ? 'yes' : 'no');
    $debugSteps[] = "Directory is writable: " . (is_writable($placePath) ? 'yes' : 'no');
    
    // List contents before deletion
    $contents = @scandir($placePath);
    if ($contents !== false) {
        $debugSteps[] = "Directory contents found: " . count($contents) . " items";
        foreach ($contents as $item) {
            if ($item !== '.' && $item !== '..') {
                $itemPath = $placePath . DIRECTORY_SEPARATOR . $item;
                $itemType = is_dir($itemPath) ? 'DIR' : 'FILE';
                $itemPerms = decoct(fileperms($itemPath) & 0777);
                $debugSteps[] = "  $itemType: $item (perms: $itemPerms)";
            }
        }
    } else {
        $debugSteps[] = "ERROR: Failed to list directory contents";
        $GLOBALS['delete_debug'] = $debugSteps;
        return false;
    }
    
    // Simple and reliable recursive deletion (based on working debug script)
    function deleteDirectory($dir, &$debugSteps) {
        $debugSteps[] = "deleteDirectory called for: $dir";
        
        if (!is_dir($dir)) {
            $debugSteps[] = "ERROR: Not a directory - $dir";
            return false;
        }
        
        try {
            // Get directory contents
            $contents = @scandir($dir);
            if ($contents === false) {
                $debugSteps[] = "ERROR: Cannot read directory contents - $dir";
                return false;
            }
            
            $debugSteps[] = "Found " . count($contents) . " items in $dir";
            
            // Process each item
            foreach ($contents as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                
                $itemPath = $dir . DIRECTORY_SEPARATOR . $item;
                $debugSteps[] = "Processing item: $itemPath";
                
                if (is_dir($itemPath)) {
                    $debugSteps[] = "Item is directory, recursing";
                    // Recursively delete subdirectory
                    if (!deleteDirectory($itemPath, $debugSteps)) {
                        $debugSteps[] = "ERROR: Failed to delete subdirectory - $itemPath";
                        return false;
                    }
                    $debugSteps[] = "Successfully deleted subdirectory: $itemPath";
                } else {
                    $debugSteps[] = "Item is file, deleting";
                    // Delete file - force ownership and permissions
                    @chown($itemPath, 0); // Change to root
                    @chgrp($itemPath, 0); // Change to root group
                    @chmod($itemPath, 0666);
                    
                    if (!@unlink($itemPath)) {
                        $error = error_get_last();
                        $debugSteps[] = "ERROR: Failed to delete file - $itemPath. Error: " . ($error ? $error['message'] : 'unknown');
                        $debugSteps[] = "File still exists after unlink attempt: " . (file_exists($itemPath) ? 'yes' : 'no');
                        return false;
                    }
                    $debugSteps[] = "Successfully deleted file: $itemPath";
                }
            }
            
            // Delete the directory itself
            $debugSteps[] = "Deleting directory: $dir";
            
            // Force ownership and permissions before deletion
            $debugSteps[] = "Attempting to change ownership and permissions before deletion";
            @chown($dir, 0); // Change to root
            @chgrp($dir, 0); // Change to root group
            @chmod($dir, 0755);
            
            // Check final permissions before deletion attempt
            $debugSteps[] = "Final permissions before rmdir: " . decoct(fileperms($dir) & 0777);
            $debugSteps[] = "Final owner before rmdir: " . fileowner($dir);
            $debugSteps[] = "Final group before rmdir: " . filegroup($dir);
            
            if (!@rmdir($dir)) {
                $error = error_get_last();
                $debugSteps[] = "ERROR: Failed to delete directory - $dir. Error: " . ($error ? $error['message'] : 'unknown');
                
                // Try to get more detailed error information
                $debugSteps[] = "Directory still exists after rmdir attempt: " . (is_dir($dir) ? 'yes' : 'no');
                if (is_dir($dir)) {
                    $contents = @scandir($dir);
                    $debugSteps[] = "Directory contents after failed rmdir: " . ($contents ? json_encode($contents) : 'cannot read');
                }
                
                return false;
            }
            
            $debugSteps[] = "Successfully deleted directory: $dir";
            return true;
            
        } catch (Exception $e) {
            $debugSteps[] = "EXCEPTION: " . $e->getMessage() . " - Directory: $dir";
            return false;
        }
    }
    
    $result = deleteDirectory($placePath, $debugSteps);
    $debugSteps[] = "deletePlaceFolder result: " . ($result ? 'SUCCESS' : 'FAILURE');
    
    // Store debug info globally for access in the action handler
    $GLOBALS['delete_debug'] = $debugSteps;
    return $result;
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
        $debugInfo = [];
        $debugInfo['step1'] = 'action_handler_start';
        
        $slug = $input['slug'] ?? '';
        $debugInfo['step2'] = 'slug_extracted: ' . $slug;
        
        if (empty($slug)) {
            $debugInfo['error'] = 'slug_empty';
            echo json_encode(['success' => false, 'message' => 'Slug is required', 'debug' => $debugInfo]);
            break;
        }
        
        try {
            $debugInfo['step3'] = 'validation_start';
            $validatedSlug = validateAndSanitizeSlug($slug);
            $debugInfo['step4'] = 'validation_result: ' . ($validatedSlug === false ? 'FALSE' : $validatedSlug);
            
            if ($validatedSlug === false) {
                $debugInfo['error'] = 'validation_failed';
                echo json_encode(['success' => false, 'message' => 'Invalid place identifier provided', 'debug' => $debugInfo]);
                break;
            }
            
            $debugInfo['step5'] = 'calling_deletePlaceFolder';
            $debugInfo['basePath'] = $basePath;
            $debugInfo['validatedSlug'] = $validatedSlug;
            
            // Check if folder exists before deletion
            $folderPath = $basePath . DIRECTORY_SEPARATOR . $validatedSlug;
            $debugInfo['folderPath'] = $folderPath;
            $debugInfo['folderExists'] = file_exists($folderPath) ? 'yes' : 'no';
            $debugInfo['isDir'] = is_dir($folderPath) ? 'yes' : 'no';
            
            if (file_exists($folderPath)) {
                $debugInfo['folderPerms'] = decoct(fileperms($folderPath) & 0777);
                $debugInfo['folderOwner'] = fileowner($folderPath);
                $debugInfo['folderGroup'] = filegroup($folderPath);
                $debugInfo['currentUID'] = getmyuid();
                $debugInfo['currentUser'] = get_current_user();
            }
            
            $success = deletePlaceFolder($basePath, $validatedSlug);
            $debugInfo['step6'] = 'deletePlaceFolder_result: ' . ($success ? 'TRUE' : 'FALSE');
            
            // Add detailed debug steps from the deletion function
            if (isset($GLOBALS['delete_debug'])) {
                $debugInfo['detailed_steps'] = $GLOBALS['delete_debug'];
            }
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Folder deleted successfully', 'debug' => $debugInfo]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete folder', 'debug' => $debugInfo]);
            }
        } catch (Exception $e) {
            $debugInfo['exception'] = $e->getMessage();
            $debugInfo['trace'] = $e->getTraceAsString();
            echo json_encode(['success' => false, 'message' => 'Error deleting place folder', 'debug' => $debugInfo]);
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
