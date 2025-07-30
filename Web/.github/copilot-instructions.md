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

### Database Connection
- Centralized in `login/db.php` using environment variables from `BDD.env`
- Always use: `require_once '../../login/db.php';` for DB access
- PDO with prepared statements standard

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

## Common Debugging
- Check `authorisation.php` for page access issues
- Verify blueprint includes for missing footer/header elements
- Database connection issues: check `BDD.env` and Docker services
- CSS not updating: cache busting with `?ver=` parameter working
