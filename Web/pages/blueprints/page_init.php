<?php
/**
 * Page Initialization Script
 * Handles session management, database connection, user authentication, and authorization
 * 
 * @global PDO $pdo Database connection object (initialized from ../database/db.php)
 * @global array $user Current user data
 * @global array $user_roles Current user's roles
 */

// Temporary debugging - enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session securely (temporarily simplified for debugging)
session_start();
// Note: Secure session settings disabled temporarily for local development
// session_start([
//     'cookie_lifetime' => 86400, // 1 day
//     'cookie_secure' => true,
//     'cookie_httponly' => true,
//     'use_strict_mode' => true,
//     'use_only_cookies' => true,
// ]);

$_SESSION['last_page'] = $_SERVER['REQUEST_URI']; // Save the current page URL

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Implement session timeout
$timeout_duration = 14400; // 4 hours
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    session_start(); // Simplified for debugging
}
$_SESSION['LAST_ACTIVITY'] = time();

require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../scriptes/functions.php'; // Include shared functions for role system
require_once __DIR__ . '/../scriptes/authorisation.php'; // includes the authorisation.php file

// store the name of the current page
$current_page = htmlspecialchars(pathinfo(basename($_SERVER['PHP_SELF']), PATHINFO_FILENAME));

/// BLOCKED USER VERIFICATION
if (isset($_SESSION['user'])) {
    // DEBUG: Log session information
    error_log("DEBUG: User session found. User ID: " . $_SESSION['user']);
    
    // Ensure database connection is available for user verification
    if (!isset($pdo) || !$pdo) {
        error_log("Database connection not available in page_init.php for user verification");
        // For regular pages (not API), show user-friendly error and allow logout
        echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px;'>";
        echo "<h3>Database Connection Error</h3>";
        echo "<p>We're experiencing technical difficulties. Please try again later.</p>";
        echo "<a href='../login/logout.php' style='color: #721c24;'>Logout and try again</a>";
        echo "</div>";
        exit();
    }
    
    // Retrieve the user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();

    // DEBUG: Log user retrieval
    error_log("DEBUG: User data retrieved: " . ($user ? 'Found user: ' . $user['username'] : 'No user found'));

    // Check if user exists
    if (!$user) {
        // User doesn't exist in database, destroy session
        error_log("DEBUG: User not found in database, destroying session");
        session_unset();
        session_destroy();
        header('Location: ../login/login.php');
        exit();
    }

    // Parse user roles using the new role system (with backward compatibility)
    $user_roles = getUserRolesCompatibility($user, $pdo);
    
    // Store user roles in session for AJAX calls
    $_SESSION['user_roles'] = $user_roles;

    // DEBUG: Log user verification success
    error_log("DEBUG: User verification successful for: " . $user['username'] . " with roles: " . implode(',', $user_roles));

    // check if the user is blocked or not
    if ($user['blocked'] != "" || $user['blocked'] != null) {
        // User is blocked
        echo "<p>You are blocked from this site. Please contact the administrator for more information.</p><br>";
        echo "<a href='../login/logout.php'>Logout</a>";
        exit();
    } elseif ($user['blocked'] === null) {
        // User is not blocked
    } else {
        echo "Error in the blocked column<br>";
    }
}

/// Used to find the current page later
// Read file names in the pages directory
$pages = [];
$dir = "../pages";
if (is_dir($dir)) { // if the directory exists
    if ($dh = opendir($dir)) { // open the directory for reading
        while (($file = readdir($dh)) !== false) { // read files in the directory
            if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php' && $file != "Homepage.php") { // if the file is not a directory and has a .php extension and isn't Homepage.php, add it to the $pages array
                $pages[] = htmlspecialchars(pathinfo($file, PATHINFO_FILENAME));
            }
        }
        closedir($dh); // close the directory
    }
}

/// Block the page if the user is not authorised to access it
// Check if the user is admin or not
if (isset($_SESSION['user']) && in_array('admin', $user_roles)) {
    // User is admin
} elseif (!isset($_SESSION['user'])) { // If the user is not logged in
    // User is not logged in
    // Loop through the array until the current page is found
    foreach ($pages as $page) {
        // If the page authorisation is admin or user, block the page
        if (($authorisation[$page] == 'admin' || $authorisation[$page] == 'user') && $page == $current_page) {
            echo "<p>You need to be logged in to access this page</p><br>";
            echo "<a href='../login/login.php'>Log here</a><span> or </span>";
            echo "<a href='./Homepage.php'>Go back safely here</a>";
            exit();
        }
    }
} elseif (isset($_SESSION['user']) && !in_array('admin', $user_roles)) { // If the user is not an admin
    // User is not admin
    // Loop through the array until the current page is found
    foreach ($pages as $page) {
        // If the page authorisation is admin, block the page
        if ($authorisation[$page] == 'admin' && $page == $current_page) {
            echo "<p>You do not have the authorisation to access this page</p><br>";
            echo "<a href='./Homepage.php'>Go back safely here</a>";
            exit();
        }
    }
} else {
    // Log error instead of echoing (prevents headers already sent issues)
    error_log("Error in the roles check");
}

?>

