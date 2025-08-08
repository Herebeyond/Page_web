<?php
// Simple database connection test
echo "Testing database connection...\n";

// Load environment variables
if (file_exists(__DIR__ . '/../../BDD.env')) {
    $dotenv = parse_ini_file(__DIR__ . '/../../BDD.env');
    foreach ($dotenv as $key => $value) {
        putenv("$key=$value");
    }
    echo "Environment file loaded.\n";
} else {
    echo "Environment file not found!\n";
}

$host = getenv('DB_HOST') ?: '';
$dbname = getenv('DB_NAME') ?: '';
$username = getenv('DB_USER') ?: '';
$password = getenv('DB_PASS') ?: '';

echo "DB_HOST: $host\n";
echo "DB_NAME: $dbname\n";
echo "DB_USER: $username\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->prepare("SELECT 1");
    $stmt->execute();
    echo "Test query successful!\n";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
?>
