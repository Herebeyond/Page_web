<?php
// Load environment variables from the .env file
if (file_exists(__DIR__ . '/../BDD.env')) {
    $dotenv = parse_ini_file(__DIR__ . '/../BDD.env');
    foreach ($dotenv as $key => $value) {
        putenv("$key=$value");
    }
}

// Get database credentials from environment variables
$host = getenv('DB_HOST') ?: '';
$dbname = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASS') ?: '';

// Try multiple hosts for compatibility between Docker and local environments
$hosts_to_try = [];
if ($host) {
    $hosts_to_try[] = $host; // Try configured host first
}
if ($host !== 'localhost') {
    $hosts_to_try[] = 'localhost'; // Try localhost for local development
}
if ($host !== '127.0.0.1') {
    $hosts_to_try[] = '127.0.0.1'; // Alternative local host
}

$pdo = null;
$last_error = null;

foreach ($hosts_to_try as $current_host) {
    try {
        $pdo = new PDO("mysql:host=$current_host;dbname=$dbname;charset=utf8", $username, $password, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        // If successful, break out of loop
        break;
    } catch (PDOException $e) {
        $last_error = $e;
        // Continue to next host
        error_log("Failed to connect to host '$current_host': " . $e->getMessage());
    }
}

// If no connection was successful, exit with error
if (!$pdo) {
    $error_msg = $last_error ? $last_error->getMessage() : 'Unknown database connection error';
    error_log("All database connection attempts failed. Last error: " . $error_msg);
    exit('Erreur de connexion à la base de données');
}
?>
