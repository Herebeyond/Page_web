<?php
// Simple working version for gallery listing
if (ob_get_level()) ob_end_clean();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json');

function validateSlug($slug) {
    if (empty($slug) || !is_string($slug)) {
        return false;
    }
    // Simple slug validation - alphanumeric and hyphens only
    return preg_match('/^[a-z0-9-]+$/', $slug) ? $slug : false;
}

function getMainImage($slug) {
    $validSlug = validateSlug($slug);
    if (!$validSlug) {
        return ['success' => false, 'message' => 'Invalid slug'];
    }
    
    $basePlacesDir = realpath('../../images/places');
    if ($basePlacesDir === false) {
        return ['success' => false, 'message' => 'Base directory not found'];
    }
    
    $placeDir = $basePlacesDir . DIRECTORY_SEPARATOR . $validSlug;
    
    if (!is_dir($placeDir)) {
        return ['success' => false, 'message' => 'Place directory not found'];
    }
    
    $allowedExts = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
    
    foreach ($allowedExts as $ext) {
        $mainImagePath = $placeDir . DIRECTORY_SEPARATOR . 'main.' . $ext;
        if (file_exists($mainImagePath)) {
            $relativePath = '../images/places/' . $validSlug . '/main.' . $ext;
            return [
                'success' => true, 
                'image' => [
                    'path' => $relativePath,
                    'extension' => $ext,
                    'exists' => true
                ]
            ];
        }
    }
    
    return ['success' => true, 'image' => ['exists' => false]];
}

function getGalleryImages($slug) {
    $validSlug = validateSlug($slug);
    if (!$validSlug) {
        return ['success' => false, 'message' => 'Invalid slug'];
    }
    
    $basePlacesDir = realpath('../../images/places');
    if ($basePlacesDir === false) {
        return ['success' => false, 'message' => 'Base directory not found'];
    }
    
    $placeDir = $basePlacesDir . DIRECTORY_SEPARATOR . $validSlug;
    $galleryDir = $placeDir . DIRECTORY_SEPARATOR . 'gallery';
    
    if (!is_dir($galleryDir)) {
        return ['success' => true, 'images' => []];
    }
    
    $images = [];
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    try {
        $iterator = new DirectoryIterator($galleryDir);
        foreach ($iterator as $file) {
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }
            
            $filename = $file->getFilename();
            $pathInfo = pathinfo($filename);
            $ext = strtolower($pathInfo['extension'] ?? '');
            
            if (!in_array($ext, $allowedExts)) {
                continue;
            }
            
            if (strpos($filename, 'main.') === 0) {
                continue;
            }
            
            $relativePath = '../images/places/' . $validSlug . '/gallery/' . $filename;
            
            $images[] = [
                'name' => $pathInfo['filename'],
                'full_name' => $filename,
                'full_path' => $relativePath,
                'thumb_path' => $relativePath,
                'size' => $file->getSize()
            ];
        }
        
        return ['success' => true, 'images' => $images];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error reading directory'];
    }
}

// Handle the request
try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    if ($input['action'] === 'list_images' && isset($input['slug'])) {
        $result = getGalleryImages($input['slug']);
        echo json_encode($result);
    } elseif ($input['action'] === 'get_main_image' && isset($input['slug'])) {
        $result = getMainImage($input['slug']);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Action not supported by this endpoint']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>