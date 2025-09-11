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
- Four access levels: `all`, `admin`, `user` (authenticated), `hidden` (no navigation)
- Two arrays: `$authorisation` (access level) and `$type` (common/parent-page)
- Role checking: `in_array('admin', $user_roles)` from `page_init.php`
- **New role system**: Many-to-many relationships with role management interface
- When adding pages, **must update both arrays in authorisation.php**

### Authorization System Updates
- Added `user` level for authenticated-only pages (in addition to `all`, `admin`, `hidden`)
- Pages requiring login: `User_profil` (user level), admin tools (admin level)
- Centralized authentication in `page_init.php` eliminates "headers already sent" errors
- Role-based access using new many-to-many role system

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
- **Beings** → Species, Races (handled dynamically through Beings_display.php)

### Role-Based Access Control System (NEW)
- **New many-to-many role system**: Replaces old comma-separated user_roles column
- **Default roles**: user, admin, moderator, editor, viewer
- **Role Management**: Admin interface at `Role_management.php` for assigning/removing roles
- **Migration System**: Automatic migration from old to new role system with backward compatibility
- **Audit Trail**: Track who assigned roles and when with `assigned_by` and timestamps

**Key Role Functions** (in `functions.php`):
- `getUserRoles($userId, $pdo)`: Get all roles for a user
- `userHasRole($userId, $roleName, $pdo)`: Check specific role
- `addUserRole($userId, $roleName, $pdo, $assignedBy)`: Add role to user  
- `removeUserRole($userId, $roleName, $pdo)`: Remove role from user
- `getUserRolesCompatibility($user, $pdo)`: Backward compatibility bridge

### Database Connection
- Centralized in `database/db.php` using environment variables from `BDD.env`
- Always use: `require_once '../../database/db.php';` for DB access
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
- **JavaScript Functions**: Use the utility functions in `functions.php` to generate consistent JavaScript code patterns

#### JavaScript Function Generation Pattern
For consistent JavaScript functionality across pages, use the utility functions:

```php
// Generate JavaScript deletion functions for entities with dynamic updates
outputEntityDeleteFunctions(['species', 'race'], './api_endpoint.php', true);

// Or generate custom JavaScript functions using the generator
echo generateEntityDeleteFunctions('species', './custom_endpoint.php', true);
```

This ensures consistent confirmation dialogs, error handling, API communication patterns, and **dynamic DOM updates without page reloads**.

**Dynamic Update Features:**
- Smooth fade-out animations for deleted items
- Success/error notifications with auto-hide
- Loading indicators during API calls
- Automatic count updates
- Form submission handling without page refresh
- Real-time DOM manipulation for better UX

#### Available Constants (Use These Instead of String Literals!):
```php
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
```

#### Available Security Functions (Use These!):
```php
// Input validation and sanitization
validateAndSanitizeSlug($slug);           // Returns safe slug or false
createSecureSlug($name);                  // Creates slug from name
constructSafePlacePath($slug);            // Safe path construction for places
validateInput($input, $type, $options);   // Multi-type input validation
sanitize_output($data);                   // Safe HTML output
parseSecureJsonInput();                   // Secure JSON parsing
isPathSafe($path, $allowedBase);          // Path traversal prevention
sanitizeFilename($filename);              // Safe filename validation
isValidImage($file);                      // Image file validation
logSecurityEvent($event, $context);       // Security event logging
```

#### Available JavaScript Generation Functions (Use These!):
```php
// Modern approach (RECOMMENDED)
includeJavaScriptAssets($scripts, $config);  // Include JS files with configuration

// Legacy entity deletion patterns (DEPRECATED - use separate .js files)
outputEntityDeleteFunctions($entityTypes, $apiEndpoint);  // Output JS functions to page
generateEntityDeleteFunctions($entityType, $apiEndpoint); // Return JS functions as string

// Deprecated functions (kept for compatibility)
generateBeingsPageFunctions($apiEndpoint);                // Use assets/js/beings.js instead
generateTabSwitchingFunctions();                          // Use assets/js/tab-manager.js instead
generateCharacterPageFunctions($apiEndpoint);             // Use assets/js/character-manager.js instead
```

**Migration to Separate JS Files**: New implementations should use dedicated JavaScript files in `/assets/js/` directory with `includeJavaScriptAssets()` for better maintainability.

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
# Services: web:80, db:3306 (chroniques_db), phpmyadmin:8080
```

### Project Structure
- **Main Application**: `html/test/Web/` (PHP/MySQL application)
- **Database Container**: `chroniques_db` with database `univers`
- **User Credentials**: `chroniques_user` / `ChroniquesAppUser2025!`
- **Environment Config**: `BDD.env` for database credentials
- **Backup System**: PowerShell script `simple-backup.ps1` for automated backups

### Backup System Configuration
- **Database Backups**: Automated MySQL dumps every 30 minutes
- **Image Backups**: Compressed archives of images directory  
- **Retention Policy**: Keeps 20 database backups, 5 image backups
- **Change Detection**: Only creates new backups when content changes
- **Backup Location**: `C:\Users\baill\OneDrive\Documents\Docker saves`
- **Manual Execution**: `.\simple-backup.ps1 -RunOnce` for single backup

## Recent Major Updates

### Role System Migration (September 2025)
- **Complete overhaul**: Migrated from comma-separated `user_roles` column to proper many-to-many system
- **New tables**: `roles` and `role_to_user` with foreign key constraints
- **Admin interface**: `Role_management.php` for role assignment and management
- **Backward compatibility**: Transition period maintains old system support
- **Default roles**: user, admin, moderator, editor, viewer with extensibility

### Entity Linking System
- **Automatic linking**: Entity names in content automatically become clickable links
- **Cross-references**: Characters, species, races, dimensions, and places
- **Context preservation**: Links maintain proper page parameters and relationships

### Enhanced Security Framework
- **Input validation**: Comprehensive validation functions for all input types
- **Path traversal prevention**: Safe directory and file operations
- **Image validation**: Secure image upload and validation
- **Security logging**: Event logging for security-related activities
- **JSON parsing**: Secure JSON input handling with error management

### Administrative Interface Improvements
- **Unified interfaces**: Consistent admin patterns across entity management
- **Modal-based editing**: In-place editing without page refreshes
- **Real-time updates**: AJAX operations with immediate UI feedback
- **Tab organization**: Multiple entity types in single interfaces
- **Error handling**: Comprehensive error reporting and user feedback

### File Structure
- `pages/` - Main application pages
- `pages/blueprints/` - Shared page structure components
- `pages/scriptes/` - Backend logic, API endpoints
- `database/` - Database connection and configuration
- `assets/` - Frontend assets (JavaScript, CSS, images)
- `style/PageStyle.css` - Centralized styling
- `login/` - Authentication system (deprecated path for db.php)
- `images/` - Static assets organized by category
  - `places/` - Location images organized by place slugs
  - `species/` - Species images
  - `races/` - Race images
  - `user_icon/` - User profile icons
  - `small_img/` - UI icons and small graphics
  - `maps/` - Map-related images
  - `unused/` - Deprecated images (not tracked in Git)

## Key Patterns

### Entity Linking System (NEW)
- **Automatic content linking**: Converts entity names in content to clickable links
- **Cross-reference support**: Links characters, species, races, dimensions, and places
- **Context-aware linking**: Uses proper page parameters for entity display
- **Implementation**: `entity_linking.php` with `getAllEntityNames()` function
- **Usage**: Include and call linking functions on content before display

### Administrative Interface Pattern
- **Unified admin interfaces**: `Beings_admin_interface.php`, `Character_admin_interface.php`, `role_management_api.php`
- **Consistent API structure**: JSON responses with success/error handling
- **Modal-based editing**: Use modals for CRUD operations without page refresh
- **Tab-based organization**: Multiple entity types managed in single interface
- **Real-time updates**: AJAX-based operations with immediate UI feedback

### Place Management System
- **Hierarchical organization**: Places with images organized by slugs
- **Folder management**: `folder_manager.php` for creating/managing place directories
- **Image management**: `place_image_manager.php` for uploading/organizing images
- **Map integration**: Places linked to interactive map system
- **Safe slug generation**: Automatic slug creation with security validation

### Universe Ideas Management System
- **Hierarchical content**: Parent-child idea relationships for organized world-building
- **Category system**: Organized ideas by type and subject matter
- **Full-text search**: Advanced search capabilities with content indexing
- **Entity linking**: Automatic linking of entity names in idea content
- **Admin interface**: `Ideas.php` page for creating and managing universe lore
- **API backend**: `ideas_manager.php` with full CRUD operations
- **Content management**: Rich text content with entity auto-linking

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

### Current Pages Structure
**Core Pages**:
- `Homepage.php` - Main landing page with world overview
- `Map_view.php` - Interactive map for users
- `Map_modif.php` - Admin map editing interface

**Entity Management**:
- `Beings.php` - Species and races overview page
- `Beings_display.php` - Detailed species/race display
- `Characters.php` - Character listing and management
- `Character_display.php` - Individual character details
- `Character_add.php` - Character creation form (admin)

**World Building**:
- `Dimensions.php` - Parent page for dimension management
- `Dimension_affichage.php` - Dimension details view
- `Dimension_list.php` - Dimension listing
- `Ideas.php` - Universe ideas and lore management (admin)
- `places_manager.php` - Place management interface (admin)
- `place_detail.php` - Individual place details

**Administration**:
- `Admin.php` - User management and admin tools
- `Role_management.php` - Role assignment interface (admin)
- `User_profil.php` - User profile page (authenticated users)

### Function Organization
- **PHP Functions**: Add all shared PHP functions to `pages/scriptes/functions.php`
- **JavaScript Functions**: Create page-specific generation functions in `functions.php` for reusable JS patterns
- **Page-Specific Logic**: Keep only minimal, truly page-specific JavaScript inline in the page file
- **API Functions**: Place in dedicated API files in `pages/scriptes/` directory
- **Always check** if similar functionality already exists before creating new functions

#### JavaScript Function Organization Pattern:
1. **Shared/Reusable Functions**: Add generator functions to `functions.php` (like `generateEntityDeleteFunctions`)
2. **Page-Specific Functions**: Create page-specific generators (like `generateBeingsPageFunctions`)
3. **Simple Interactive Functions**: Keep basic toggle/navigation functions inline if they're truly page-specific
4. **Admin vs User Functions**: Use conditional output based on user roles

### Database Operations
- Always use prepared statements
- Include error handling and transactions for multi-step operations
- Follow existing patterns in `map_save_points.php` for API structure

### API Endpoints Structure
**Core Management APIs**:
- `Beings_admin_interface.php` - Species and races CRUD operations
- `Character_admin_interface.php` - Character management
- `role_management_api.php` - User role assignment/management
- `map_save_points.php` - Interactive map data management

**Specialized APIs**:
- `place_manager.php` - Place information updates
- `place_image_manager.php` - Place image uploads and management
- `folder_manager.php` - Directory creation and management
- `ideas_manager.php` - Universe ideas and lore management
- `entity_linking.php` - Automatic entity name linking

**Utility APIs**:
- `fetch_*.php` - Data fetching endpoints (character_info, race_info, specie_info, user_info)
- `delete_*.php` - Entity deletion endpoints
- `search_user.php` - Real-time user search
- `block_user.php` / `unblock_user.php` - User management

### CSS Organization
- Use existing classes: `.content-page`, `.notification-banner`, `.map-*` prefixes
- Responsive design: percentage-based positioning, `clamp()` for fonts
- Color scheme: `#222088` (primary), `#a1abff` (hover), `#d4af37` (accents)
- **Design Philosophy**: Minimize white backgrounds - prefer dark themes with gradients and transparency
- **Background Preference**: Use dark/semi-transparent backgrounds with `rgba()` values and `backdrop-filter: blur()`
- **Text on Dark**: Use light colors (`rgba(255, 255, 255, 0.8)`) or gold accents (`#d4af37`) for readability

### Modern Development Practices
- **JavaScript Modularization**: Move inline JavaScript to separate files in `/assets/js/`
- **API Security**: Always include CORS headers and security validation
- **Error Handling**: Comprehensive error reporting with user-friendly messages
- **Input Validation**: Use provided validation functions for all user inputs
- **Session Management**: Leverage `page_init.php` for consistent authentication
- **Database Transactions**: Use transactions for multi-step operations
- **File Operations**: Always validate paths and filenames for security

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
- Role system issues: Check `Role_management.php` for migration status
- User authentication: Verify `page_init.php` role assignment and session management
- API errors: Check browser console and PHP error logs
- File upload issues: Verify directory permissions and path validation

### Current Database Schema
**Core Tables**:
- `users` - User accounts with authentication
- `roles` - Available system roles (NEW)
- `role_to_user` - User-role assignments (NEW many-to-many)
- `species` - Fantasy species
- `races` - Sub-categories of species
- `characters` - Character entities
- `interest_points` - Map locations and places
- `ideas` - Universe lore and world-building content

**Legacy Compatibility**:
- Old `user_roles` column preserved during transition period
- Backward compatibility functions bridge old/new role systems
- Migration tools available in `Role_management.php`

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

## Modifications Documentation


### Modifications.txt Format
when a new feature is added or an existing feature is changed, it must be documented in the modifications.txt file.
When documenting changes in `.github/modifications.txt`, use the following format:

**Date Format**: `DD-MM-YYYY HH:MM` (24-hour format)
**Structure**:
```
DD-MM-YYYY HH:MM
### Feature/Change Title

**Features Added:**
- Description of features

**Files Modified:**
1. **filename.ext**
   - Specific changes made
   
**Key Features:**
- Important functionality highlights

**Data Safety Measures:**
- Security and integrity considerations
```

**Example**:
```
21-08-2025 20:46
### Category Management System Implementation

**Features Added:**
- Complete category management system with safe deletion
```

This format ensures consistency and makes it easy to track when changes were made and what was implemented.

---

## .md Files
- create them in the folder Web/.github/

## Testing, Temporary and one use Files/scripts Guidelines
- place created testing, temporary or one-use files in the `html/test/Web/tests/` folder
- when used and its purpose is fulfilled, delete them to keep the environment clean but keep the tests folder

### Antivirus Considerations
- **Important**: When creating test files, antivirus software may flag them as suspicious and block execution
- If a test file is blocked by antivirus, the user will add an exception for that specific file
- Once an exception is made, the same file won't be blocked on subsequent uses
- This is normal behavior for dynamically generated test files and API testing scripts
- Always inform the user if a test file needs to be created for debugging purposes
- The user will only add an exception once the file has been used once, so the first execution may be blocked but the subsequent ones should work without issues from the antivirus.

## Troubleshooting Log
- Troubleshooting files are to be placed in the `Web/.github/troubleshooting/` folder and shouldn't be placed elsewhere.
- Troubleshooting names should be descriptive and follow the format: `TROUBLESHOOTING_{ISSUE_DESCRIPTION}.md` to ensure no conflicts with other files.

## Commenting

- Use clear and descriptive comments to explain complex logic or important decisions in the code.
- Include comments for any non-obvious code, especially if it involves security or performance considerations.
- Keep comments up to date with code changes to avoid confusion.
- Do not hesitate to add comments to clarify your thought process or the purpose of specific code blocks.