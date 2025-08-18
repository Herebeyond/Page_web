# AI Coding Agent Instructions

## Project Overview
This is a French(original)-English(work in progress) fantasy world-building web application called "Les Chroniques de la Faille - Les mondes oubliés" built with PHP/MySQL. It allows users to explore and admins to manage species, races, characters, dimensions, and an interactive map system.

## Architecture

### Blueprint System (Critical Pattern)
Every page (with few exceptions) follows a strict 3-part structure using blueprints in `pages/blueprints/`:

```php
<?php
require_once "./blueprints/page_init.php";     // Auth, sessions, DB
require_once "./blueprints/gl_ap_start.php";   // HTML start, header
?>
<!-- Custom page content here -->
<!-- Optional: additional CSS/JS between blueprints -->
<?php
require_once "./blueprints/gl_ap_end.php";     // Footer, scroll-to-top, closing tags
?>
```

**Never duplicate HTML structure** - `gl_ap_start.php` provides `<!DOCTYPE>`, `<html>`, `<head>`, `<body>`, header. `gl_ap_end.php` provides footer, scroll-to-top arrow, closing tags.

### Authorization System
- All page access controlled by `pages/scriptes/authorisation.php`
- Two arrays: `$authorisation` (all/admin/hidden) and `$type` (common/parent-page)
- Role checking: `in_array('admin', $user_roles)` from `page_init.php`
- When adding pages, **must update both arrays in authorisation.php**

#### Parent-Child Page Relationships
Pages can be organized into parent-child hierarchies using the `$type` array:
- **Parent pages**: Set `$type['PageName'] => 'common'` - acts as landing page
- **Child pages**: Set `$type['ChildPage'] => 'ParentPage'` - appears under parent in navigation

**Example Implementation Pattern**:
```php
// In authorisation.php
$authorisation = array(
    'Beings' => 'all',          // Parent page - accessible to all
    'Species' => 'all',         // Child page - accessible to all  
    'Races' => 'all',           // Child page - accessible to all
);

$type = array(
    'Beings' => 'common',       // Parent page
    'Species' => 'Beings',      // Child of Beings
    'Races' => 'Beings',        // Child of Beings
);
```

**Parent Page Structure**: Parent pages scan for child pages and display them organized with descriptions:
```php
// Filter pages that are [Parent] type and user has access to
$child_pages = [];
foreach ($pages as $page) {
    if (isset($type[$page]) && $type[$page] === 'ParentName' && hasPageAccess($page, $authorisation, $user_roles)) {
        $child_pages[] = $page;
    }
}
```

**Navigation Dropdown Logic**: The header navigation automatically creates dropdowns for parent pages:
```php
// In header.php - Generic logic for any parent-child relationship
foreach ($pages as $page3) {
    if ($type[$page3] == $page) {  // Show all children of current parent
        // Display child page in dropdown
    }
}
```

**CRITICAL**: When adding new parent-child relationships, ensure the `header.php` navigation logic is generic (not hardcoded to specific page names).

**Current Hierarchies**:
- **Dimensions** → Dimension_affichage, Dimension_list
- **Beings** → Species, Races

### Database Connection
- Centralized in `login/db.php` using environment variables from `BDD.env`
- Always use: `require_once '../../login/db.php';` for DB access
- PDO with prepared statements standard
- **CRITICAL**: All API files must validate PDO connection:
```php
// Verify database connection was successful
if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
```

### Shared Functions System
- **CRITICAL**: All shared functions go in `pages/scriptes/functions.php`
- This file is included in `gl_ap_start.php`, making functions globally available
- **Never duplicate functions** across multiple files - use shared functions instead
- When creating new utility functions, add them to `functions.php` with proper documentation

#### Available Constants (Use These Instead of String Literals!):
```php
// Path constants
const IMAGES_PLACES_PATH = 'images/places/';
const REDIRECT_LOCATION_LOGIN = 'Location: login.php';
const NOT_SPECIFIED = 'Not specified';
const ACCESS_DENIED_ADMIN_REQUIRED = 'Access denied - Admin required';
const INVALID_SLUG_FORMAT = 'Invalid slug format';
const INVALID_DIRECTORY_PATH = 'Invalid directory path';
const DATABASE_ERROR_PREFIX = 'Database error: ';
const SQL_SELECT_PLACE_NAME_BY_ID = 'SELECT name_IP FROM interest_points WHERE id_IP = ?';
```

#### Available Security Functions (Use These!):
```php
// Input validation and sanitization
validateAndSanitizeSlug($slug);           // Returns safe slug or false
createSecureSlug($name);                  // Creates slug from name
constructSafePlacePath($slug, $baseDir); // Safe path construction
validateFileExtension($filename, $allowed); // File type validation
parseSecureJsonInput($jsonInput);        // Secure JSON parsing
```

#### Function Documentation Standards:
- Use PHPDoc comments for all functions in `functions.php`
- Include `@param` and `@return` annotations
- Group related functions with section headers
- Security functions must include vulnerability prevention notes

#### Code Quality Standards:
- **String Literals**: Use constants instead of duplicating strings 3+ times
- **Bracing**: Always use curly braces, even for single statements
- **CSS**: Avoid conflicting shorthand properties (margin-top + margin)
- **Complexity**: Keep functions under 15 cognitive complexity points

## Development Environment

### Docker Setup
```bash
cd Docker/
docker-compose up -d
# Services: web:80, db:3306, phpmyadmin:8080
```

### File Structure
- `pages/` - Main application pages
- `pages/blueprints/` - Shared page structure components
- `pages/scriptes/` - Backend logic, API endpoints
- `style/PageStyle.css` - Centralized styling
- `login/` - Authentication system
- `images/` - Static assets

## Key Patterns

### Interactive Map System
- Admin interface: `Map_modif.php` (editing, CRUD operations)
- User interface: `Map_view.php` (read-only exploration)
- Backend: `pages/scriptes/map_save_points.php` (API endpoints)
- Coordinate system: Percentage-based for responsive design
- Database table: `interest_points`

### Navigation & UI
- Dynamic dropdown menus in `blueprints/header.php`
- Admin tools only visible to admin users
- Notification banners: `.notification-banner` with close functionality
- CSS versioning: `?ver=" . time()` to bust cache

### Help System Integration Pattern
Smart positioning help icons that remain contextual to content while maintaining visibility during scroll:

**HTML Structure** (inside `#mainText` element):
```html
<div class="map-help-container" id="help-container-id">
    <div class="map-help-icon [admin-help-icon]" id="help-trigger-id">
        <span>?</span>
    </div>
    <div class="map-help-tooltip [admin-help-tooltip]" id="help-content-id">
        <div class="notification-content">
            <h3>📍 Title</h3>
            <ul><li>Instructions...</li></ul>
        </div>
        <button class="tooltip-close" onclick="hideHelp()">&times;</button>
    </div>
</div>
```

**CSS Classes**:
- `.map-help-container` (absolute positioning relative to #mainText)
- `.map-help-container.fixed` (fixed positioning at screen top when scrolled)
- Color variants: blue gradient for users, red `.admin-help-icon` for admin

**JavaScript Handler**:
```javascript
function handleHelpContainerPosition() {
    const helpContainer = document.getElementById('help-container-id');
    const mainText = document.getElementById('mainText');
    if (!helpContainer || !mainText) return;
    
    const mainTextRect = mainText.getBoundingClientRect();
    if (mainTextRect.top < 0) {
        helpContainer.className = 'map-help-container fixed';
    } else {
        helpContainer.className = 'map-help-container';
    }
}
// Add: window.addEventListener('scroll', handleHelpContainerPosition);
```

**Implementation Examples**: `Map_view.php` (user blue), `Map_modif.php` (admin red)

### Form Handling
- Fetch existing data with dedicated endpoints
- "Fetch Info" buttons populate forms for editing
- Empty fields ignored during updates (doesn't overwrite existing data)
- File uploads handled with unique IDs appended to original names

## Development Guidelines

### Adding New Pages
1. Create page file following blueprint pattern
2. Add entries to both arrays in `authorisation.php`
3. Update navigation in `header.php` if needed
4. Use consistent CSS classes from `PageStyle.css`

### Database Operations
- Always use prepared statements
- Include error handling and transactions for multi-step operations
- Follow existing patterns in `map_save_points.php` for API structure

### CSS Organization
- Use existing classes: `.content-page`, `.notification-banner`, `.map-*` prefixes
- Responsive design: percentage-based positioning, `clamp()` for fonts
- Color scheme: `#222088` (primary), `#a1abff` (hover), `#d4af37` (accents)

### Security Considerations
- Session management in `page_init.php` with timeout/regeneration
- Role-based access control enforced at page level
- XSS protection via proper escaping in output
- File upload validation and unique naming
- **Path traversal prevention**: Never construct file paths directly from user input
- **Input validation**: Always validate and sanitize all user inputs before processing
- **Directory confinement**: Ensure all file operations stay within intended directories

## Security Guidelines (CRITICAL)

### Path Traversal Prevention
**NEVER construct file paths directly from user input**. Always validate and sanitize:

```php
// ❌ VULNERABLE - Direct concatenation
$userPath = $_POST['path'];
$filePath = '/var/www/uploads/' . $userPath;  // Can be exploited with ../../../etc/passwd

// ✅ SECURE - Proper validation and sanitization
function validateAndSanitizeSlug($slug) {
    if (empty($slug)) return false;
    
    // Remove path traversal attempts
    $slug = str_replace(['../', '..\\', '/', '\\', '.', '~'], '', $slug);
    
    // Only allow safe characters
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $slug)) return false;
    
    // Limit length
    if (strlen($slug) > 100) return false;
    
    return $slug;
}

function constructSafePath($userInput, $baseDir) {
    $sanitized = validateAndSanitizeSlug($userInput);
    if ($sanitized === false) return false;
    
    $basePath = realpath($baseDir);
    if ($basePath === false) return false;
    
    $targetPath = $basePath . DIRECTORY_SEPARATOR . $sanitized;
    
    // Verify path stays within base directory
    $realTargetPath = realpath($targetPath);
    if ($realTargetPath !== false && strpos($realTargetPath, $basePath) !== 0) {
        return false;
    }
    
    return $targetPath;
}
```

### Input Validation Rules
1. **Validate ALL user inputs** before processing
2. **Use whitelist validation** (allow only known good characters)
3. **Sanitize file/folder names** to alphanumeric + underscore/hyphen only
4. **Length limits** to prevent buffer overflow attacks
5. **JSON input validation** with proper error handling

### File Operation Security
```php
// ❌ VULNERABLE - Direct scandir with user input
$files = scandir('/uploads/' . $_POST['folder']);

// ✅ SECURE - Safe directory traversal
try {
    $safeDir = constructSafePath($_POST['folder'], '/uploads');
    if ($safeDir === false) throw new Exception('Invalid path');
    
    $iterator = new DirectoryIterator($safeDir);
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isDot() || !$fileInfo->isFile()) continue;
        // Process files safely
    }
} catch (Exception $e) {
    error_log('Directory traversal attempt: ' . $_POST['folder']);
    return false;
}
```

### Required Security Patterns
- **Path Construction**: Always use `constructSafePath()` pattern
- **Input Sanitization**: Always use `validateAndSanitizeSlug()` pattern  
- **Directory Operations**: Use `DirectoryIterator` instead of `scandir()`
- **File Verification**: Verify `realpath()` stays within expected directories
- **Error Handling**: Log security violations, return safe error messages
- **CORS Security**: Use restrictive origin whitelisting, prioritize HTTPS
- **Security Headers**: Always include security headers for API endpoints

### API Security Standards
For API endpoints handling sensitive data:

```php
// Secure CORS with HTTPS prioritization
$allowedOrigins = [
    // HTTPS first (production ready)
    'https://localhost', 'https://127.0.0.1',
    // HTTP only for local development (log warnings)
    'http://localhost', 'http://127.0.0.1'
];

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'none\'; frame-ancestors \'none\';');

// HTTPS enforcement
if (str_starts_with($origin, 'https://')) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
```

## Common Debugging
- Check `authorisation.php` for page access issues
- Verify blueprint includes for missing footer/header elements
- Database connection issues: check `BDD.env` and Docker services
- CSS not updating: cache busting with `?ver=` parameter working

---

## 🔄 IMPORTANT: Instructions Update Requirement

**MANDATORY**: When implementing new programming patterns, architectural decisions, security measures, or development habits in this project, you **MUST** update this instructions file to include them.

### Update Triggers:
- ✅ New security patterns or functions added
- ✅ New architectural patterns established  
- ✅ New coding standards adopted
- ✅ New shared utilities created
- ✅ New debugging techniques discovered
- ✅ New best practices implemented

### Update Process:
1. Add new patterns to relevant sections
2. Include code examples where applicable
3. Document why the pattern prevents issues
4. Update function listings if new shared functions added
5. Note in troubleshooting log if security-related

**Remember**: This file serves as the single source of truth for project development standards. Keeping it updated ensures consistency across all future development work.

---

## .md Files
- create them in the folder Web/.github/

## Testing Guidelines
- place created testing or one-use files in `html/test/Web/tests/`
- when testing ends, delete them to keep the environment clean but keep the tests folder

## Troubleshooting Log
- Troubleshooting files are to be placed in the `Web/.github/troubleshooting/` folder and shouldn't be placed elsewhere.
- Troubleshooting names should be descriptive and follow the format: `TROUBLESHOOTING_{ISSUE_DESCRIPTION}.md` to ensure no conflicts with other files.

## Commenting

- Use clear and descriptive comments to explain complex logic or important decisions in the code.
- Include comments for any non-obvious code, especially if it involves security or performance considerations.
- Keep comments up to date with code changes to avoid confusion.
- Do not hesitate to add comments to clarify your thought process or the purpose of specific code blocks.