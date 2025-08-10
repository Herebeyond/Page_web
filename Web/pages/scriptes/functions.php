<?php

// ============================================================================
// CONSTANTS - Common String Literals
// ============================================================================

// Path constants
const IMAGES_PLACES_PATH = 'images/places/';
const REDIRECT_LOCATION_LOGIN = 'Location: login.php';
const REDIRECT_LOCATION_CHARACTER_ADD = 'Location: Character_add.php';
const REDIRECT_LOCATION_RACE_ADD = 'Location: Race_add.php';
const REDIRECT_LOCATION_SPECIE_ADD = 'Location: Specie_add.php';
const REDIRECT_LOCATION_MAP_VIEW = 'Location: map_view.php';
const LOCATION_MAP_VIEW = 'Location: map_view.php';

// Display constants
const NOT_SPECIFIED = 'Not specified';
const ACCESS_DENIED_ADMIN_REQUIRED = 'Access denied - Admin required';
const INVALID_SLUG_FORMAT = 'Invalid slug format';
const INVALID_DIRECTORY_PATH = 'Invalid directory path';

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Parse and validate JSON input securely
 * @return mixed Parsed JSON data or false on failure
 */
function parseSecureJsonInput() {
    $raw_input = file_get_contents('php://input');
    
    if (empty($raw_input)) {
        return false;
    }
    
    $input = json_decode($raw_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return false;
    }
    
    return $input;
}

/**
 * Validate if a path is within a safe directory
 * @param string $path Path to validate
 * @param string $allowedBase Base directory path that's allowed
 * @return bool True if path is safe, false otherwise
 */
function isPathSafe($path, $allowedBase) {
    $realBase = realpath($allowedBase);
    
    if ($realBase === false) {
        return false;
    }
    
    // For non-existent paths (like when creating folders), 
    // we need to check the parent directory instead
    if (!file_exists($path)) {
        // Normalize the path without requiring it to exist
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedBase = str_replace('\\', '/', $realBase);
        
        // Check if the path starts with the allowed base
        return strpos($normalizedPath, $normalizedBase) === 0;
    } else {
        // For existing paths, use realpath as before
        $realPath = realpath($path);
        if ($realPath === false) {
            return false;
        }
        return strpos($realPath, $realBase) === 0;
    }
}

/**
 * Create a safe directory slug from a string
 * @param string $string Input string
 * @return string Safe directory slug
 */
function createSafeSlug($string) {
    // Convert to lowercase
    $slug = strtolower(trim($string));
    
    // Remove accents using a more reliable method
    $slug = str_replace(
        ['à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'ñ', 'ç', 'ß', 'œ'],
        ['a', 'a', 'a', 'a', 'a', 'a', 'ae', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'n', 'c', 'ss', 'oe'],
        $slug
    );
    
    // Replace any remaining non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    
    // Remove multiple consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Remove leading and trailing hyphens
    $slug = trim($slug, '-');
    
    return $slug;
}

/**
 * Sanitize filename for safe file operations
 * @param string $filename Input filename
 * @return string|false Sanitized filename or false if invalid
 */
function sanitizeFilename($filename) {
    // Remove any path components
    $filename = basename($filename);
    
    // Check for valid characters
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
        return false;
    }
    
    // Check length
    if (strlen($filename) > 255) {
        return false;
    }
    
    return $filename;
}

/**
 * Get MIME type from file extension
 * @param string $filename Filename with extension
 * @return string MIME type
 */
function getMimeTypeFromExtension($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
    ];
    
    return isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';
}

/**
 * Validate image file
 * @param array $file $_FILES array element
 * @return bool True if valid image
 */
function isValidImage($file) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    return in_array($mimeType, $allowedTypes);
}

/**
 * Get file size in human readable format
 * @param int $bytes File size in bytes
 * @return string Human readable size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Log security events
 * @param string $event Event description
 * @param array $context Additional context
 */
function logSecurityEvent($event, $context = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'context' => $context
    ];
    
    error_log("SECURITY: " . json_encode($logEntry));
}

/**
 * Validate and sanitize user input
 * @param mixed $input Input to validate
 * @param string $type Expected type (string, int, email, etc.)
 * @param array $options Additional validation options
 * @return mixed Sanitized input or false on failure
 */
function validateInput($input, $type = 'string', $options = []) {
    switch ($type) {
        case 'string':
            if (!is_string($input)) return false;
            $input = trim($input);
            if (isset($options['max_length']) && strlen($input) > $options['max_length']) return false;
            if (isset($options['min_length']) && strlen($input) < $options['min_length']) return false;
            return $input;
            
        case 'int':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false) return false;
            if (isset($options['min']) && $input < $options['min']) return false;
            if (isset($options['max']) && $input > $options['max']) return false;
            return $input;
            
        case 'float':
            $input = filter_var($input, FILTER_VALIDATE_FLOAT);
            if ($input === false) return false;
            if (isset($options['min']) && $input < $options['min']) return false;
            if (isset($options['max']) && $input > $options['max']) return false;
            return $input;
            
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL);
            
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL);
            
        case 'slug':
            if (!is_string($input)) return false;
            return preg_match('/^[a-z0-9\-_]+$/', $input) ? $input : false;
            
        default:
            return false;
    }
}

/**
 * Sanitizes output for safe HTML display
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validates and sanitizes a slug for directory/file operations
 * @param string $slug The slug to validate
 * @return string|false The sanitized slug or false if invalid
 */
function validateAndSanitizeSlug($slug) {
    if (empty($slug) || !is_string($slug)) {
        return false;
    }
    
    // Remove dangerous characters and normalize
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($slug));
    
    // Check for dangerous patterns
    if (empty($slug) || $slug === '.' || $slug === '..' || strlen($slug) > 100) {
        return false;
    }
    
    return $slug;
}

// Note: JavaScript functions were moved to appropriate frontend files
// to prevent HTML contamination in JSON API responses
?>
