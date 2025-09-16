<?php
/**
 * Check database state after migration
 */

require_once '/var/www/html/test/Web/database/db.php';

if (!$pdo) {
    echo "❌ Database connection failed\n";
    exit(1);
}

echo "=== Database State Check ===\n";

try {
    // Check users table
    $stmt = $pdo->query("SELECT id_user, username, email, user_roles FROM users");
    $users = $stmt->fetchAll();
    
    echo "Users in database:\n";
    foreach ($users as $user) {
        echo "  ID: {$user['id_user']}, Username: {$user['username']}, Email: {$user['email']}, Old roles: " . ($user['user_roles'] ?? 'NULL') . "\n";
    }
    
    // Check roles table
    $stmt = $pdo->query("SELECT * FROM roles");
    $roles = $stmt->fetchAll();
    
    echo "\nRoles in database:\n";
    foreach ($roles as $role) {
        echo "  ID: {$role['id_role']}, Name: {$role['role_name']}, Description: {$role['role_description']}, Active: " . ($role['is_active'] ? 'Yes' : 'No') . "\n";
    }
    
    // Check role assignments
    $stmt = $pdo->query("
        SELECT u.username, r.role_name, rtu.assigned_at 
        FROM role_to_user rtu 
        JOIN users u ON rtu.id_user = u.id_user 
        JOIN roles r ON rtu.id_role = r.id_role 
        ORDER BY u.username, r.role_name
    ");
    $assignments = $stmt->fetchAll();
    
    echo "\nRole assignments:\n";
    foreach ($assignments as $assignment) {
        echo "  User: {$assignment['username']}, Role: {$assignment['role_name']}, Assigned: {$assignment['assigned_at']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
