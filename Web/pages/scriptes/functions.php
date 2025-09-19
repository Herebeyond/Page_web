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
const DATABASE_ERROR_PREFIX = 'Database error: ';

// Database query constants
const SQL_SELECT_PLACE_NAME_BY_ID = 'SELECT name_IP FROM interest_points WHERE id_IP = ?';

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
 * @param string|null $data The data to sanitize
 * @return string The sanitized data (empty string if null)
 */
function sanitize_output($data) {
    if ($data === null) {
        return '';
    }
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
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

/**
 * Create a secure slug from a name for directory/file operations
 * @param string $name The name to convert to a slug
 * @return string|false The secure slug or false if invalid
 */
function createSecureSlug($name) {
    if (empty($name) || !is_string($name)) {
        return false;
    }
    
    // Use the existing createSafeSlug function
    $slug = createSafeSlug($name);
    
    // Additional validation for security
    return validateAndSanitizeSlug($slug);
}

/**
 * Construct a safe path for place operations
 * @param string $slug The sanitized slug
 * @return string|false The safe path or false if invalid
 */
function constructSafePlacePath($slug) {
    $sanitizedSlug = validateAndSanitizeSlug($slug);
    if ($sanitizedSlug === false) {
        return false;
    }
    
    // Return the base path for places images
    return IMAGES_PLACES_PATH . $sanitizedSlug;
}

/**
 * Check if an image link/path is valid and accessible
 * @param string $url The image path or URL to check
 * @return bool True if the image exists and is valid, false otherwise
 */
function isImageLinkValid($url) {
    // Handle local file paths (relative paths starting with ../)
    if (strpos($url, '../') === 0) {
        // Convert the relative path to an absolute path from the document root
        // The caller (Species.php) is in the pages directory
        // So ../images/file.png should resolve to [web_root]/images/file.png
        
        // Get the document root path (where the calling script is located)
        $callerDir = dirname($_SERVER['SCRIPT_FILENAME']);
        $absolutePath = $callerDir . '/' . $url;
        $absolutePath = realpath($absolutePath);
        
        // Check if file exists and is actually a file (not directory)
        if ($absolutePath && file_exists($absolutePath) && is_file($absolutePath)) {
            // Check if it's an image by checking the file extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            return in_array($extension, $allowedExtensions);
        }
        return false;
    }
    
    // Handle URLs (for remote images)
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        // Use cURL for URL validation
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        // Check HTTP code and content type
        return $httpCode === 200 && strpos($contentType, 'image/') !== false;
    }
    
    // For other paths, assume invalid
    return false;
}

// ============================================================================
// JAVASCRIPT UTILITIES
// ============================================================================

/**
 * Generate shared JavaScript utility functions
 * @return string JavaScript utility functions as string
 */
function generateSharedJavaScriptUtilities() {
    return "
    // Shared utility functions
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = 'notification-banner ' + type + '-notification';
        
        let backgroundColor, textColor, borderColor;
        switch(type) {
            case 'success':
                backgroundColor = '#d4edda';
                textColor = '#155724';
                borderColor = '#c3e6cb';
                break;
            case 'error':
                backgroundColor = '#f8d7da';
                textColor = '#721c24';
                borderColor = '#f5c6cb';
                break;
            case 'warning':
                backgroundColor = '#fff3cd';
                textColor = '#856404';
                borderColor = '#ffeaa7';
                break;
            default: // info
                backgroundColor = '#d1ecf1';
                textColor = '#0c5460';
                borderColor = '#bee5eb';
        }
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            background: ` + backgroundColor + `;
            color: ` + textColor + `;
            border: 1px solid ` + borderColor + `;
            border-radius: 4px;
            padding: 15px 20px;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            opacity: 1;
            transition: opacity 0.3s ease;
        `;
        notification.innerHTML = '<span>' + message + '</span><button style=\"margin-left: 15px; background: none; border: none; font-size: 18px; cursor: pointer; color: ' + textColor + ';\" onclick=\"this.parentElement.remove()\">&times;</button>';
        document.body.appendChild(notification);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (notification && notification.parentElement) {
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification && notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    function showSuccessMessage(message) {
        showNotification(message, 'success');
    }
    
    function showErrorMessage(message) {
        showNotification(message, 'error');
    }
    
    function showLoadingIndicator() {
        if (!document.getElementById('loadingIndicator')) {
            const loader = document.createElement('div');
            loader.id = 'loadingIndicator';
            loader.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
            `;
            
            const spinner = document.createElement('div');
            spinner.style.cssText = `
                width: 50px;
                height: 50px;
                border: 5px solid #f3f3f3;
                border-top: 5px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            `;
            
            // Add keyframe animation
            if (!document.getElementById('spinKeyframes')) {
                const style = document.createElement('style');
                style.id = 'spinKeyframes';
                style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
                document.head.appendChild(style);
            }
            
            loader.appendChild(spinner);
            document.body.appendChild(loader);
        }
    }
    
    function hideLoadingIndicator() {
        const loader = document.getElementById('loadingIndicator');
        if (loader) {
            loader.remove();
        }
    }
    
    function updateEntityCount(type) {
        // Update count displays if they exist
        const countElements = document.querySelectorAll('[data-count-type=\"' + type + '\"]');
        countElements.forEach(element => {
            const currentCount = parseInt(element.textContent) || 0;
            element.textContent = Math.max(0, currentCount - 1);
        });
    }";
}

/**
 * Generate JavaScript functions for entity deletion with confirmation
 * @param string $entityType Type of entity (species, race, character, etc.)
 * @param string $apiEndpoint API endpoint for deletion
 * @param bool $dynamicUpdate Whether to update DOM dynamically instead of page reload
 * @return string JavaScript functions as string for inclusion in pages
 */
function generateEntityDeleteFunctions($entityType, $apiEndpoint = null, $dynamicUpdate = true) {
    if ($apiEndpoint === null) {
        $apiEndpoint = './scriptes/Beings_admin_interface.php';
    }
    
    $entityCapitalized = ucfirst($entityType);
    
    $updateCode = $dynamicUpdate ? 
        "// Dynamic update - remove the entity from DOM
                console.log('Looking for entity with ID:', id);
                const entityCard = document.querySelector('[data-{$entityType}-id=\"' + id + '\"]') || 
                                 document.querySelector('.{$entityType}-card[data-id=\"' + id + '\"]') ||
                                 document.querySelector('#{$entityType}-' + id);
                console.log('Found entity card:', entityCard);
                if (entityCard) {
                    entityCard.style.transition = 'opacity 0.3s ease';
                    entityCard.style.opacity = '0';
                    setTimeout(() => {
                        entityCard.remove();
                        console.log('Entity removed from DOM');
                        // Update count if exists
                        updateEntityCount(type);
                        // Show success message with fade effect
                        showSuccessMessage(data.message);
                    }, 300);
                } else {
                    console.log('Entity card not found, showing success message and reloading');
                    showSuccessMessage(data.message);
                    setTimeout(() => location.reload(), 1000);
                }" : 
        "location.reload(); // Refresh the page";
    
    return "
    function confirmDelete{$entityCapitalized}(id, name) {
        if (confirm('Are you sure you want to delete the {$entityType} \"' + name + '\"? This action cannot be undone.')) {
            deleteEntity('$entityType', id, '$apiEndpoint');
        }
    }
    
    function deleteEntity(type, id, endpoint = '$apiEndpoint') {
        // Show loading indicator
        showLoadingIndicator();
        
        fetch(endpoint + '?action=delete_' + type, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingIndicator();
            if (data.success) {
                {$updateCode}
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            hideLoadingIndicator();
            console.error('Error:', error);
            showErrorMessage('An error occurred while deleting the ' + type);
        });
    }";
}

/**
 * Output JavaScript entity deletion functions for inclusion in page
 * @param array $entityTypes Array of entity types to generate functions for
 * @param string $apiEndpoint API endpoint for deletion operations
 * @param bool $dynamicUpdate Whether to update DOM dynamically instead of page reload
 */
function outputEntityDeleteFunctions($entityTypes, $apiEndpoint = null, $dynamicUpdate = true) {
    echo "<script>\n";
    
    // Only output utilities once
    static $utilitiesOutput = false;
    if (!$utilitiesOutput) {
        echo generateSharedJavaScriptUtilities() . "\n";
        $utilitiesOutput = true;
    }
    
    foreach ($entityTypes as $entityType) {
        echo generateEntityDeleteFunctions($entityType, $apiEndpoint, $dynamicUpdate) . "\n";
    }
    echo "</script>\n";
}

/**
 * Generate JavaScript functions for Beings page functionality
 * @param string $apiEndpoint API endpoint for admin operations
 * @return string JavaScript functions for Beings page
 */
function generateBeingsPageFunctions($apiEndpoint = './scriptes/Beings_admin_interface.php') {
    return "
    function toggleSpeciesRaces(speciesId) {
        const racesSection = document.getElementById('races-' + speciesId);
        const speciesCard = racesSection.closest('.species-card');
        
        // Close all other expanded species first
        document.querySelectorAll('.species-card.expanded').forEach(card => {
            if (card !== speciesCard) {
                const otherRacesSection = card.querySelector('.races-section');
                otherRacesSection.classList.remove('show');
                card.classList.remove('expanded');
            }
        });
        
        // Toggle the current species
        if (racesSection.classList.contains('show')) {
            racesSection.classList.remove('show');
            speciesCard.classList.remove('expanded');
        } else {
            racesSection.classList.add('show');
            speciesCard.classList.add('expanded');
        }
    }

    function viewRaceDetails(speciesId, raceId) {
        // Navigate to race details page with both species and race IDs
        window.location.href = `./Beings_display.php?specie_id=\${speciesId}&race_id=\${raceId}`;
    }

    function toggleRaceCharacters(raceId) {
        const charactersSection = document.getElementById('characters-' + raceId);
        const raceCard = charactersSection.closest('.race-card');
        
        // Close all other expanded races first
        document.querySelectorAll('.race-card.expanded').forEach(card => {
            if (card !== raceCard) {
                const otherCharactersSection = card.querySelector('.characters-section');
                if (otherCharactersSection) {
                    otherCharactersSection.classList.remove('show');
                    card.classList.remove('expanded');
                }
            }
        });
        
        // Toggle the current race
        if (charactersSection.classList.contains('show')) {
            charactersSection.classList.remove('show');
            raceCard.classList.remove('expanded');
        } else {
            charactersSection.classList.add('show');
            raceCard.classList.add('expanded');
        }
    }

    function viewCharacterDetails(raceName) {
        // Navigate to character details page showing all characters of this race
        window.location.href = `./Character_display.php?race=\${encodeURIComponent(raceName)}`;
    }

    function viewSpeciesCharacters(speciesId) {
        // Navigate to character display page showing all characters of this species
        window.location.href = `./Character_display.php?specie_id=\${speciesId}`;
    }

    function openAdminModal() {
        document.getElementById('adminModal').style.display = 'block';
        // Load admin interface via AJAX
        fetch('$apiEndpoint')
            .then(response => response.text())
            .then(html => {
                document.getElementById('adminModalContent').innerHTML = html;
                // Set up event listeners for dynamically loaded forms
                setupModalFormHandlers();
            })
            .catch(error => {
                console.error('Error loading admin interface:', error);
                document.getElementById('adminModalContent').innerHTML = 
                    '<div class=\"error-message\">Failed to load admin interface</div>';
            });
    }

    function setupModalFormHandlers() {
        console.log('Setting up modal form handlers...');
        
        // Remove any existing listeners first
        const modalContent = document.getElementById('adminModalContent');
        if (!modalContent) {
            console.error('adminModalContent not found!');
            return;
        }
        
        console.log('Modal content found:', modalContent);
        
        // Use event delegation to handle form submissions
        modalContent.removeEventListener('submit', handleModalFormSubmit);
        modalContent.addEventListener('submit', handleModalFormSubmit);
        
        // Also add direct listeners to forms that might exist now
        const speciesForm = modalContent.querySelector('#speciesForm');
        const raceForm = modalContent.querySelector('#raceForm');
        
        if (speciesForm) {
            console.log('Species form found, adding direct listener');
            speciesForm.removeEventListener('submit', handleModalFormSubmit);
            speciesForm.addEventListener('submit', handleModalFormSubmit);
        }
        
        if (raceForm) {
            console.log('Race form found, adding direct listener');
            raceForm.removeEventListener('submit', handleModalFormSubmit);
            raceForm.addEventListener('submit', handleModalFormSubmit);
        }
        
        console.log('Modal form handlers set up successfully');
    }

    function handleModalFormSubmit(e) {
        console.log('Form submit event caught!', e.target);
        
        // Always prevent default form submission first
        e.preventDefault();
        e.stopPropagation();
        
        const form = e.target;
        
        // Check if it's a species or race form
        if (form.id === 'speciesForm') {
            console.log('Handling species form');
            handleSpeciesFormSubmit(form);
        } else if (form.id === 'raceForm') {
            console.log('Handling race form');
            handleRaceFormSubmit(form);
        } else {
            console.log('Unhandled form:', form.id || 'no id', form);
        }
    }

    function handleSpeciesFormSubmit(form) {
        console.log('Species form submitted via event delegation');
        const formData = new FormData(form);
        
        // Log form data
        for (let [key, value] of formData.entries()) {
            console.log('Form field:', key, '=', value);
        }
        
        fetch('$apiEndpoint?action=save_species', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(responseText => {
            console.log('Raw response:', responseText);
            try {
                const data = JSON.parse(responseText);
                console.log('Parsed response:', data);
                
                if (data.success) {
                    alert('✅ ' + data.message);
                    closeAdminModal();
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                alert('❌ Invalid response from server');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('❌ Network error: ' + error.message);
        });
    }

    function handleRaceFormSubmit(form) {
        console.log('Race form submitted via event delegation');
        const formData = new FormData(form);
        
        fetch('$apiEndpoint?action=save_race', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(responseText => {
            try {
                const data = JSON.parse(responseText);
                if (data.success) {
                    alert('✅ ' + data.message);
                    closeAdminModal();
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                alert('❌ Invalid response from server');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('❌ Network error: ' + error.message);
        });
    }

    function executeScriptsInElement(element) {
        const scripts = element.getElementsByTagName('script');
        for (let i = 0; i < scripts.length; i++) {
            const script = scripts[i];
            const newScript = document.createElement('script');
            
            if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }
            
            // Replace the old script with the new one to execute it
            script.parentNode.replaceChild(newScript, script);
        }
    }

    function closeAdminModal() {
        document.getElementById('adminModal').style.display = 'none';
    }

    function editSpecies(speciesId) {
        openAdminModal();
        // Load edit form after modal is open
        setTimeout(() => {
            loadEditSpeciesForm(speciesId);
        }, 100);
    }

    function editRace(raceId) {
        openAdminModal();
        // Load edit form after modal is open
        setTimeout(() => {
            loadEditRaceForm(raceId);
        }, 100);
    }

    function addRaceToSpecies(speciesId) {
        openAdminModal();
        // Load add race form after modal is open
        setTimeout(() => {
            loadAddRaceForm(speciesId);
        }, 100);
    }

    function loadEditSpeciesForm(speciesId) {
        fetch(`$apiEndpoint?action=edit_species&id=\${speciesId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('adminModalContent').innerHTML = html;
                // Add dynamic save handler
                setupDynamicFormHandler('species', speciesId);
            });
    }

    function loadEditRaceForm(raceId) {
        fetch(`$apiEndpoint?action=edit_race&id=\${raceId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('adminModalContent').innerHTML = html;
                // Add dynamic save handler
                setupDynamicFormHandler('race', raceId);
            });
    }

    function loadAddRaceForm(speciesId) {
        fetch(`$apiEndpoint?action=add_race&species_id=\${speciesId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('adminModalContent').innerHTML = html;
                // Add dynamic save handler
                setupDynamicFormHandler('race', null, speciesId);
            });
    }

    function setupDynamicFormHandler(entityType, entityId = null, parentId = null) {
        const form = document.querySelector('#adminModalContent form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                handleDynamicFormSubmit(this, entityType, entityId, parentId);
            });
        }
    }

    function handleDynamicFormSubmit(form, entityType, entityId = null, parentId = null) {
        const formData = new FormData(form);
        
        // Determine the correct action based on entity type
        let action = '';
        if (entityType === 'species') {
            action = 'save_species';
        } else if (entityType === 'race') {
            action = 'save_race';
        }
        
        showLoadingIndicator();
        
        fetch(`$apiEndpoint?action=\${action}`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if response is JSON or HTML
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If it's not JSON, it's probably an error page
                return response.text().then(text => {
                    throw new Error('Server returned HTML instead of JSON: ' + text.substring(0, 100));
                });
            }
        })
        .then(data => {
            hideLoadingIndicator();
            if (data.success) {
                showSuccessMessage(data.message);
                closeAdminModal();
                // Reload the page to show updated data
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            hideLoadingIndicator();
            console.error('Error:', error);
            showErrorMessage('An error occurred while saving: ' + error.message);
        });
    }

    function updateSpeciesCard(speciesId, speciesData) {
        const speciesCard = document.querySelector(`[data-species-id=\"\${speciesId}\"]`);
        if (speciesCard) {
            // Update species name
            const nameElement = speciesCard.querySelector('.species-name');
            if (nameElement && speciesData.specie_name) {
                nameElement.textContent = speciesData.specie_name;
            }
            
            // Update species description
            const descElement = speciesCard.querySelector('.species-description');
            if (descElement && speciesData.content_specie) {
                const truncated = speciesData.content_specie.length > 150 ? 
                    speciesData.content_specie.substring(0, 150) + '...' : 
                    speciesData.content_specie;
                descElement.textContent = truncated;
            }
            
            // Update species image if changed
            const imgElement = speciesCard.querySelector('.species-image img');
            if (imgElement && speciesData.icon_specie) {
                const imgPath = '../images/species/' + speciesData.icon_specie.replace(' ', '_');
                imgElement.src = imgPath;
            }
            
            // Add update animation
            speciesCard.style.transition = 'transform 0.3s ease';
            speciesCard.style.transform = 'scale(1.02)';
            setTimeout(() => {
                speciesCard.style.transform = 'scale(1)';
            }, 300);
        }
    }

    function addRaceToSpeciesCard(speciesId, raceData) {
        const speciesCard = document.querySelector(`[data-species-id=\"\${speciesId}\"]`);
        if (speciesCard) {
            const racesGrid = speciesCard.querySelector('.races-grid');
            const noRacesDiv = speciesCard.querySelector('.no-races');
            
            // Remove \"no races\" message if it exists
            if (noRacesDiv) {
                noRacesDiv.remove();
            }
            
            // Create races grid if it doesn't exist
            if (!racesGrid) {
                const racesSection = speciesCard.querySelector('.races-section');
                const newRacesGrid = document.createElement('div');
                newRacesGrid.className = 'races-grid';
                racesSection.appendChild(newRacesGrid);
            }
            
            // Create new race card
            const raceCard = createRaceCardElement(raceData, speciesId);
            racesGrid.appendChild(raceCard);
            
            // Update race count
            const countElement = speciesCard.querySelector('[data-count-type=\"race\"]');
            if (countElement) {
                const currentCount = parseInt(countElement.textContent) || 0;
                countElement.textContent = (currentCount + 1) + ' race(s)';
            }
            
            // Animation
            raceCard.style.opacity = '0';
            raceCard.style.transform = 'translateY(20px)';
            setTimeout(() => {
                raceCard.style.transition = 'all 0.3s ease';
                raceCard.style.opacity = '1';
                raceCard.style.transform = 'translateY(0)';
            }, 100);
        }
    }

    function createRaceCardElement(raceData, speciesId) {
        const raceCard = document.createElement('div');
        raceCard.className = 'race-card';
        raceCard.setAttribute('data-race-id', raceData.id_race);
        raceCard.onclick = () => viewRaceDetails(speciesId, raceData.id_race);
        
        const imgPath = raceData.icon_race ? 
            '../images/races/' + raceData.icon_race.replace(' ', '_') : 
            '../images/icon_default.png';
        
        raceCard.innerHTML = `
            <div class=\"race-image\">
                <img src=\"\${imgPath}\" alt=\"\${raceData.race_name}\" onerror=\"this.src='../images/icon_default.png'\">
            </div>
            <div class=\"race-info\">
                <h3 class=\"race-name\">\${raceData.race_name}</h3>
                <div class=\"race-stats\">
                    \${raceData.lifespan ? `<span class=\"race-stat\"><strong>Lifespan:</strong> \${raceData.lifespan}</span>` : ''}
                    \${raceData.homeworld ? `<span class=\"race-stat\"><strong>Homeworld:</strong> \${raceData.homeworld}</span>` : ''}
                </div>
                \${raceData.content_race ? `<p class=\"race-description\">\${raceData.content_race.substring(0, 100)}\${raceData.content_race.length > 100 ? '...' : ''}</p>` : ''}
            </div>
        `;
        
        return raceCard;
    }

    function showTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
            content.classList.remove('active');
        });
        
        // Remove active class from all tab buttons (support both .tab-button and .tab-btn)
        const tabButtons = document.querySelectorAll('.tab-button, .tab-btn');
        tabButtons.forEach(button => {
            button.classList.remove('active');
        });
        
        // Show the selected tab content (support both direct ID and ID with -tab suffix)
        let selectedTab = document.getElementById(tabName);
        if (!selectedTab) {
            selectedTab = document.getElementById(tabName + '-tab');
        }
        
        if (selectedTab) {
            selectedTab.style.display = 'block';
            selectedTab.classList.add('active');
        }
        
        // Add active class to the clicked button
        const activeButton = document.querySelector(`[onclick*=\"showTab('\${tabName}')\"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
    }

    function addNewSpecies() {
        fetch('$apiEndpoint?action=add_species')
            .then(response => response.text())
            .then(data => {
                document.getElementById('adminModalContent').innerHTML = data;
                // Set up event listeners for the new form
                setupModalFormHandlers();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading add species form');
            });
    }

    function addNewRace() {
        fetch('$apiEndpoint?action=add_race')
            .then(response => response.text())
            .then(data => {
                document.getElementById('adminModalContent').innerHTML = data;
                // Set up event listeners for the new form
                setupModalFormHandlers();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading add race form');
            });
    }

    function confirmDeleteSpecies(id, name) {
        if (confirm('Are you sure you want to delete the species \"' + name + '\"? This will also delete all associated races and characters.')) {
            deleteEntity('species', id, '$apiEndpoint');
        }
    }

    function confirmDeleteRace(id, name) {
        if (confirm('Are you sure you want to delete the race \"' + name + '\"? This will also delete all associated characters.')) {
            deleteEntity('race', id, '$apiEndpoint');
        }
    }";
}

/**
 * Generate JavaScript functions for Character management page
 * @param string $apiEndpoint API endpoint for character admin operations
 * @return string JavaScript code for character page functionality
 */
function generateCharacterPageFunctions($apiEndpoint = './scriptes/Character_admin_interface.php') {
    return "
    function openCharacterAdminModal() {
        document.getElementById('characterAdminModal').style.display = 'block';
        // Load admin interface via AJAX
        fetch('$apiEndpoint')
            .then(response => response.text())
            .then(html => {
                document.getElementById('characterAdminModalContent').innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading character admin interface:', error);
                document.getElementById('characterAdminModalContent').innerHTML = 
                    '<div class=\"error-message\">Failed to load character admin interface</div>';
            });
    }

    function closeCharacterAdminModal() {
        document.getElementById('characterAdminModal').style.display = 'none';
    }

    function editCharacter(characterId) {
        openCharacterAdminModal();
        // Load edit form after modal is open
        setTimeout(() => {
            loadEditCharacterForm(characterId);
        }, 100);
    }

    function addCharacterToRace(raceId) {
        openCharacterAdminModal();
        // Load add character form after modal is open
        setTimeout(() => {
            loadAddCharacterForm(raceId);
        }, 100);
    }

    function loadEditCharacterForm(characterId) {
        const formData = new FormData();
        formData.append('action', 'getCharacterData');
        formData.append('character_id', characterId);

        fetch('$apiEndpoint', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateCharacterForm(data.character, 'edit');
                showTab('character-form');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading character data:', error);
            showNotification('Failed to load character data', 'error');
        });
    }

    function loadAddCharacterForm(raceId) {
        // Clear the form and set it to add mode
        clearCharacterForm();
        document.getElementById('characterRaceId').value = raceId;
        document.getElementById('characterSubmitBtn').textContent = 'Add Character';
        document.getElementById('characterFormTitle').textContent = 'Add New Character';
        showTab('character-form');
    }

    function populateCharacterForm(character, mode = 'edit') {
        // Populate all form fields
        document.getElementById('characterId').value = character.id_character || '';
        document.getElementById('characterName').value = character.character_name || '';
        document.getElementById('characterAge').value = character.age || '';
        document.getElementById('characterHabitat').value = character.habitat || '';
        document.getElementById('characterCountry').value = character.country || '';
        document.getElementById('characterContent').value = character.content_character || '';
        document.getElementById('characterRaceId').value = character.correspondence || '';
        
        // Update form title and button text
        if (mode === 'edit') {
            document.getElementById('characterSubmitBtn').textContent = 'Update Character';
            document.getElementById('characterFormTitle').textContent = 'Edit Character: ' + character.character_name;
        } else {
            document.getElementById('characterSubmitBtn').textContent = 'Add Character';
            document.getElementById('characterFormTitle').textContent = 'Add New Character';
        }
    }

    function clearCharacterForm() {
        document.getElementById('characterForm').reset();
        document.getElementById('characterId').value = '';
        document.getElementById('characterPreview').innerHTML = '';
    }

    function submitCharacterForm() {
        const form = document.getElementById('characterForm');
        const formData = new FormData(form);
        
        const characterId = document.getElementById('characterId').value;
        formData.append('action', characterId ? 'updateCharacter' : 'addCharacter');
        
        // Show loading state
        const submitBtn = document.getElementById('characterSubmitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;

        fetch('$apiEndpoint', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeCharacterAdminModal();
                // Refresh the page to show changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error submitting character form:', error);
            showNotification('An error occurred while saving the character', 'error');
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    function addRaceToSpecies(speciesId) {
        // This function might be called from race sections, redirect to proper interface
        window.location.href = './Beings.php?add_race=' + speciesId;
    }

    function showTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
            content.classList.remove('active');
        });
        
        // Remove active class from all tab buttons (support both .tab-button and .tab-btn)
        const tabButtons = document.querySelectorAll('.tab-button, .tab-btn');
        tabButtons.forEach(button => {
            button.classList.remove('active');
        });
        
        // Show the selected tab content (support both direct ID and ID with -tab suffix)
        let selectedTab = document.getElementById(tabName);
        if (!selectedTab) {
            selectedTab = document.getElementById(tabName + '-tab');
        }
        
        if (selectedTab) {
            selectedTab.style.display = 'block';
            selectedTab.classList.add('active');
        }
        
        // Add active class to the clicked button
        const activeButton = document.querySelector(`[onclick*=\"showTab('\${tabName}')\"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
    }";
}

/**
 * Output JavaScript functions for Beings page
 * @param string $apiEndpoint API endpoint for admin operations
 * @param bool $includeAdminFunctions Whether to include admin-only functions
 */
function outputBeingsPageFunctions($apiEndpoint = './scriptes/Beings_admin_interface.php', $includeAdminFunctions = true) {
    echo "<script>\n";
    
    // Only output utilities once
    static $utilitiesOutput = false;
    if (!$utilitiesOutput) {
        echo generateSharedJavaScriptUtilities() . "\n";
        $utilitiesOutput = true;
    }
    
    echo generateBeingsPageFunctions($apiEndpoint) . "\n";
    echo "</script>\n";
}

// Note: JavaScript functions were moved to appropriate frontend files
// to prevent HTML contamination in JSON API responses
?>
