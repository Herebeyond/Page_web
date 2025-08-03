<?php
session_start();
require_once '../../login/db.php';

// Check if user is admin
if (!isset($_SESSION['user']) || !isset($_SESSION['user_roles']) || !in_array('admin', $_SESSION['user_roles'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

try {
    // Check current structure of interest_points table
    $stmt = $pdo->query("DESCRIBE interest_points");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current interest_points table structure:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $hasOtherNames = false;
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
        
        if ($column['Field'] === 'other_names_IP') {
            $hasOtherNames = true;
        }
    }
    echo "</table>";
    
    if (!$hasOtherNames) {
        echo "<h3 style='color: red;'>❌ Column 'other_names_IP' is missing!</h3>";
        echo "<p>This explains why the 'Other Names' edit feature is not working.</p>";
        
        // Add the missing column
        echo "<h3>Adding missing column...</h3>";
        $addColumnSQL = "ALTER TABLE interest_points ADD COLUMN other_names_IP TEXT NULL AFTER name_IP";
        $pdo->exec($addColumnSQL);
        echo "<p style='color: green;'>✅ Column 'other_names_IP' has been added successfully!</p>";
        
        // Verify the column was added
        $stmt = $pdo->query("DESCRIBE interest_points");
        $newColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Updated table structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($newColumns as $column) {
            $style = ($column['Field'] === 'other_names_IP') ? " style='background-color: #d4edda;'" : "";
            echo "<tr$style>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<h3 style='color: green;'>✅ Column 'other_names_IP' exists!</h3>";
        echo "<p>The column exists, so the issue might be elsewhere.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Database Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
