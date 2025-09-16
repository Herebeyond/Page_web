<?php
/**
 * Test the updated page_init.php with new role system
 */

echo "Testing page_init.php with new role system...\n";

// Start session
session_start();

// Simulate a logged-in user (using the actual user ID)
$_SESSION['user'] = 4;

try {
    // Include page_init.php
    include '/var/www/html/test/Web/pages/blueprints/page_init.php';
    
    echo "✅ page_init.php loaded successfully\n";
    
    if (isset($user_roles)) {
        echo "✅ User roles loaded: " . implode(', ', $user_roles) . "\n";
    } else {
        echo "❌ User roles not loaded\n";
    }
    
    if (isset($user)) {
        echo "✅ User data loaded for: " . $user['username'] . "\n";
    } else {
        echo "❌ User data not loaded\n";
    }
    
    // Test if functions are available
    if (function_exists('getUserRoles')) {
        echo "✅ getUserRoles function is available\n";
    } else {
        echo "❌ getUserRoles function not available\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>
