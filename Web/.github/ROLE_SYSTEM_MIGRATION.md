# Role System Migration Documentation

## Overview

This document describes the migration from the old `user_roles` column-based system to a new many-to-many relationship using dedicated `roles` and `role_to_user` tables.

## Migration Steps

### 1. Database Schema Changes

**New Tables Created:**
- `roles`: Stores available roles with descriptions
- `role_to_user`: Junction table linking users to roles

**Schema Details:**
```sql
-- Roles table
CREATE TABLE roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    role_description VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Role-to-User junction table
CREATE TABLE role_to_user (
    id_role_user INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_role INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_role) REFERENCES roles(id_role) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id_user) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (id_user, id_role)
);
```

### 2. Default Roles

The system comes with these predefined roles:
- **user**: Standard user with basic access
- **admin**: Administrator with full access
- **moderator**: Moderator with limited admin access
- **editor**: Content editor with creation/editing rights
- **viewer**: Read-only access for specific content

### 3. Migration Process

**Automatic Migration:**
1. Creates new tables if they don't exist
2. Inserts default roles
3. Migrates existing user roles from `user_roles` column
4. Assigns default 'user' role to users without roles
5. Optionally removes old `user_roles` column (separate step)

## New Functions

### Core Role Functions

```php
// Get all roles for a user
$roles = getUserRoles($userId, $pdo);

// Check if user has specific role
$hasAdmin = userHasRole($userId, 'admin', $pdo);

// Add role to user
$success = addUserRole($userId, 'editor', $pdo, $assignedByUserId);

// Remove role from user
$success = removeUserRole($userId, 'editor', $pdo);

// Get all available roles
$allRoles = getAllRoles($pdo, $activeOnly = true);
```

### Compatibility Functions

```php
// Backward compatibility during transition
$roles = getUserRolesCompatibility($user, $pdo);
```

This function checks the new role system first, then falls back to the old `user_roles` column if needed.

## Updated Files

### Core System Files
- `pages/blueprints/page_init.php`: Updated to use new role system
- `pages/scriptes/functions.php`: Added new role management functions
- `pages/scriptes/authorisation.php`: Added Role_management page

### New Files
- `pages/Role_management.php`: Admin interface for role management
- `pages/scriptes/role_management_api.php`: API endpoints for role operations
- `database/migrations/001_create_roles_system.sql`: Migration SQL
- `database/migrations/002_remove_user_roles_column.sql`: Final cleanup
- `database/migrations/run_roles_migration.php`: Migration script

### Testing
- `tests/test_role_system.php`: Test script to verify migration

## Usage Instructions

### For Administrators

1. **Run Migration:**
   - Access `Role_management.php` page
   - Click "Run Migration" button (if migration status shows not completed)
   - Verify migration completed successfully

2. **Manage User Roles:**
   - Select user from dropdown
   - Select role to add/remove
   - Use "Add Role" or "Remove Role" buttons

3. **Create New Roles:**
   - Fill in role name and description
   - Click "Create Role"

### For Developers

1. **Check User Roles:**
   ```php
   // In pages with page_init.php included
   if (in_array('admin', $user_roles)) {
       // Admin-only functionality
   }
   ```

2. **Use New Functions:**
   ```php
   // Check specific role
   if (userHasRole($userId, 'editor', $pdo)) {
       // Editor functionality
   }
   
   // Get all user roles
   $userRoles = getUserRoles($userId, $pdo);
   ```

## Migration Safety

### Backup Recommendations
- Always backup database before migration
- Test on development environment first
- The old `user_roles` column is preserved until manually removed

### Rollback Procedure
If issues occur:
1. Stop using new role functions
2. Restore `page_init.php` to use old column parsing
3. Drop new tables: `DROP TABLE role_to_user, roles`
4. Old system will continue working

### Final Cleanup
Only after thorough testing:
1. Run `002_remove_user_roles_column.sql`
2. Remove compatibility functions
3. Update documentation

## Benefits of New System

1. **Scalability**: Easy to add new roles without code changes
2. **Flexibility**: Users can have multiple roles
3. **Audit Trail**: Track who assigned roles and when
4. **Data Integrity**: Foreign key constraints ensure consistency
5. **Performance**: Indexed queries for role checking
6. **Management**: Admin interface for role management

## Security Considerations

- Role assignments are logged with timestamps
- Admin role cannot be removed from last admin user
- All role operations require admin privileges
- Input validation on all role names
- Prepared statements prevent SQL injection

## Future Enhancements

Possible future additions:
- Role permissions mapping
- Temporary role assignments
- Role hierarchies
- Bulk role assignment
- Role assignment approval workflow
