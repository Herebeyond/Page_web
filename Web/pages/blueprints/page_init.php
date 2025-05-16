<?php


// Start the session securely
session_start([
    'cookie_lifetime' => 86400, // 1 day
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

$_SESSION['last_page'] = $_SERVER['REQUEST_URI']; // Save the current page URL

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Implement session timeout
$timeout_duration = 3600; // 60 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'use_only_cookies' => true,
    ]);
}
$_SESSION['LAST_ACTIVITY'] = time();

require_once __DIR__ . '/../../login/db.php';
require_once __DIR__ . '/../scriptes/authorisation.php'; // includes the authorisation.php file

// store the name of the current page
$current_page = htmlspecialchars(pathinfo(basename($_SERVER['PHP_SELF']), PATHINFO_FILENAME));

/// BLOCKED USER VERIFICATION
if (isset($_SESSION['user'])) {
    // Retrieve the user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();

    // Fetch user roles
    $stmt = $pdo->prepare("SELECT r.name FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
    $stmt->execute([$user['id']]);
    $user_roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
        // If the page authorisation is admin, block the page
        if ($authorisation[$page] == 'admin' && $page == $current_page) {
            echo "<p>You need to be logged and have a certain role to access this page</p><br>";
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
    echo "Error in the roles check<br>";
}

?>
