<?php
/**
 * Debug Version of Page Initialization Script
 * Shows detailed information about session management and user authentication
 */

// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_debug = [];
$page_debug[] = "=== PAGE INIT DEBUG START ===";
$page_debug[] = "Current time: " . date('Y-m-d H:i:s');
$page_debug[] = "Current page: " . basename($_SERVER['PHP_SELF']);

// Start the session
session_start();
$page_debug[] = "Session ID: " . session_id();

// Check current session data
$page_debug[] = "Current session data: " . json_encode($_SESSION);

$_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
$page_debug[] = "Last page set to: " . $_SESSION['last_page'];

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
    $page_debug[] = "Session regenerated (first time)";
} else {
    $page_debug[] = "Session already initiated";
}

// Implement session timeout
$timeout_duration = 14400; // 4 hours
$page_debug[] = "Session timeout duration: " . $timeout_duration . " seconds";

if (isset($_SESSION['LAST_ACTIVITY'])) {
    $time_since_activity = time() - $_SESSION['LAST_ACTIVITY'];
    $page_debug[] = "Time since last activity: " . $time_since_activity . " seconds";
    
    if ($time_since_activity > $timeout_duration) {
        $page_debug[] = "❌ SESSION TIMEOUT - clearing session";
        
        // Session has expired
        session_unset();
        session_destroy();
        session_start();
        
        // Store debug info and redirect
        $_SESSION['page_debug'] = $page_debug;
        header('Location: ../login/login_debug.php');
        exit();
    } else {
        $remaining_time = $timeout_duration - $time_since_activity;
        $page_debug[] = "✅ Session timeout OK - " . $remaining_time . " seconds remaining";
    }
} else {
    $page_debug[] = "No previous activity timestamp found";
}
$_SESSION['LAST_ACTIVITY'] = time();

// Include database connection
require_once __DIR__ . '/../../login/db.php';
$page_debug[] = "Database connection: " . ($pdo ? "✅ OK" : "❌ FAILED");

// Include authorization
require_once __DIR__ . '/../scriptes/authorisation.php';
$page_debug[] = "Authorization file included";

// Store the name of the current page
$current_page = htmlspecialchars(pathinfo(basename($_SERVER['PHP_SELF']), PATHINFO_FILENAME));
$page_debug[] = "Current page filename: " . $current_page;

/// BLOCKED USER VERIFICATION
if (isset($_SESSION['user'])) {
    $page_debug[] = "=== USER VERIFICATION ===";
    $page_debug[] = "Session user ID: " . $_SESSION['user'];
    
    // Check database connection
    if (!isset($pdo) || !$pdo) {
        $page_debug[] = "❌ Database connection not available for user verification";
        echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px;'>";
        echo "<h3>Database Connection Error</h3>";
        echo "<p>We're experiencing technical difficulties. Please try again later.</p>";
        echo "<pre>" . implode("\n", $page_debug) . "</pre>";
        echo "<a href='../login/login_debug.php' style='color: #721c24;'>Go to Debug Login</a>";
        echo "</div>";
        exit();
    }
    
    // Retrieve the user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();

    if (!$user) {
        $page_debug[] = "❌ User ID not found in database - destroying session";
        
        // User doesn't exist in database, destroy session
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['page_debug'] = $page_debug;
        header('Location: ../login/login_debug.php');
        exit();
    } else {
        $user_id = $user['id_user'];
        $page_debug[] = "✅ User found: " . $user['username'] . " (ID: " . $user_id . ")";
    }

    // Fetch user roles
    try {
        $user_id = $user['id_user'];
        $stmt = $pdo->prepare("SELECT r.role_name FROM role_to_user rtu JOIN roles r ON rtu.id_role = r.id_role WHERE rtu.id_user = ?");
        $stmt->execute([$user_id]);
        $user_roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $page_debug[] = "User roles: " . json_encode($user_roles);
        
        // Store user roles in session for AJAX calls
        $_SESSION['user_roles'] = $user_roles;
        
    } catch (Exception $e) {
        $page_debug[] = "⚠️ Error fetching user roles: " . $e->getMessage();
        $user_roles = []; // Default to no roles
    }

    // Check if the user is blocked
    if ($user['blocked'] != "" && $user['blocked'] != null) {
        $page_debug[] = "❌ User is blocked: " . $user['blocked'];
        echo "<p>You are blocked from this site. Please contact the administrator for more information.</p><br>";
        echo "<a href='../login/logout.php'>Logout</a>";
        echo "<hr><h3>Debug Info:</h3><pre>" . implode("\n", $page_debug) . "</pre>";
        exit();
    } else {
        $page_debug[] = "✅ User is not blocked";
    }
} else {
    $page_debug[] = "No user session found - user is not logged in";
}

// Authorization check
$page_debug[] = "=== AUTHORIZATION CHECK ===";
$pages = [];
$dir = "../pages";
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php' && $file != "Homepage.php") {
                $pages[] = htmlspecialchars(pathinfo($file, PATHINFO_FILENAME));
            }
        }
        closedir($dh);
    }
}

$page_debug[] = "Found " . count($pages) . " pages";

// Check authorization for current page
if (isset($authorisation[$current_page])) {
    $page_debug[] = "Current page authorization: " . $authorisation[$current_page];
    
    if ($authorisation[$current_page] == 'admin') {
        if (!isset($_SESSION['user'])) {
            $page_debug[] = "❌ Admin page requires login";
            $_SESSION['page_debug'] = $page_debug;
            echo "<div style='padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px;'>";
            echo "<h3>Authorization Required</h3>";
            echo "<p>You need to be logged in with admin privileges to access this page.</p>";
            echo "<pre style='background: #f8f9fa; padding: 10px;'>" . implode("\n", $page_debug) . "</pre>";
            echo "<a href='../login/login_debug.php'>Go to Debug Login</a>";
            echo "</div>";
            exit();
        } elseif (!in_array('admin', $user_roles ?? [])) {
            $page_debug[] = "❌ User does not have admin role";
            echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px;'>";
            echo "<h3>Access Denied</h3>";
            echo "<p>You do not have the authorization to access this page.</p>";
            echo "<pre style='background: #f8f9fa; padding: 10px; color: #000;'>" . implode("\n", $page_debug) . "</pre>";
            echo "<a href='./Homepage.php'>Go back safely here</a>";
            echo "</div>";
            exit();
        } else {
            $page_debug[] = "✅ Admin authorization passed";
        }
    } else {
        $page_debug[] = "✅ Page is public or user has access";
    }
} else {
    $page_debug[] = "Page not in authorization array - assuming public";
}

// Store debug info in session for display
$_SESSION['page_init_debug'] = $page_debug;

// Debug display function
function show_debug_info() {
    if (isset($_SESSION['page_init_debug'])) {
        echo "<div style='position: fixed; top: 10px; right: 10px; width: 400px; max-height: 300px; overflow-y: auto; background: rgba(0,0,0,0.8); color: white; padding: 10px; font-family: monospace; font-size: 10px; z-index: 9999; border-radius: 5px;'>";
        echo "<h4 style='margin: 0 0 10px 0; color: #4CAF50;'>🔍 Page Init Debug</h4>";
        echo "<pre style='margin: 0; white-space: pre-wrap;'>";
        foreach ($_SESSION['page_init_debug'] as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo "</pre>";
        echo "<button onclick='this.parentElement.style.display=\"none\"' style='position: absolute; top: 5px; right: 5px; background: #f44336; color: white; border: none; border-radius: 3px; cursor: pointer;'>×</button>";
        echo "</div>";
    }
}
?>