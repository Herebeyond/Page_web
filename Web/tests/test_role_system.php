<?php
/**
 * Test Script for Role System Migration
 * This script tests the new role system functions
 */

require_once '/var/www/html/test/Web/database/db.php';
require_once '/var/www/html/test/Web/pages/scriptes/functions.php';

if (!$pdo) {
    die("Database connection failed. Cannot run tests.\n");
}

echo "=== Role System Test Script ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Test 1: Check if new tables exist
    echo "Test 1: Checking if new tables exist...\n";
    
    $tables = ['roles', 'role_to_user'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' does not exist\n";
        }
    }
    
    // Test 2: Check if roles were inserted
    echo "\nTest 2: Checking default roles...\n";
    $stmt = $pdo->query("SELECT role_name, role_description FROM roles WHERE is_active = 1");
    $roles = $stmt->fetchAll();
    
    if (count($roles) > 0) {
        echo "✓ Found " . count($roles) . " active roles:\n";
        foreach ($roles as $role) {
            echo "  - {$role['role_name']}: {$role['role_description']}\n";
        }
    } else {
        echo "✗ No active roles found\n";
    }
    
    // Test 3: Test getUserRoles function
    echo "\nTest 3: Testing getUserRoles function...\n";
    $stmt = $pdo->query("SELECT id_user, username FROM users LIMIT 3");
    while ($user = $stmt->fetch()) {
        $roles = getUserRoles($user['id_user'], $pdo);
        echo "✓ User '{$user['username']}' has roles: " . implode(', ', $roles) . "\n";
    }
    
    // Test 4: Test userHasRole function
    echo "\nTest 4: Testing userHasRole function...\n";
    $stmt = $pdo->query("SELECT id_user, username FROM users LIMIT 1");
    $testUser = $stmt->fetch();
    
    if ($testUser) {
        $hasUser = userHasRole($testUser['id_user'], 'user', $pdo);
        $hasAdmin = userHasRole($testUser['id_user'], 'admin', $pdo);
        echo "✓ User '{$testUser['username']}' has 'user' role: " . ($hasUser ? 'Yes' : 'No') . "\n";
        echo "✓ User '{$testUser['username']}' has 'admin' role: " . ($hasAdmin ? 'Yes' : 'No') . "\n";
    }
    
    // Test 5: Check backward compatibility
    echo "\nTest 5: Testing backward compatibility...\n";
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $testUser = $stmt->fetch();
    
    if ($testUser) {
        $roles = getUserRolesCompatibility($testUser, $pdo);
        echo "✓ Compatibility function for '{$testUser['username']}': " . implode(', ', $roles) . "\n";
    }
    
    echo "\n=== All Tests Completed ===\n";
    
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
}
?>
