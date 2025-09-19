<?php
// Debug script to check point positioning
require_once '../../login/db.php';

try {
    // Get the interest point data
    $stmt = $conn->prepare("
        SELECT 
            ip.id_IP,
            ip.name_IP, 
            ip.coordinates_IP,
            ip.map_IP,
            m.name_map,
            m.image_map
        FROM interest_points ip
        JOIN maps m ON ip.map_IP = m.id_map
        WHERE ip.id_IP = 24
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<h3>Interest Point Debug Information</h3>";
        echo "<p><strong>Point ID:</strong> " . $result['id_IP'] . "</p>";
        echo "<p><strong>Point Name:</strong> " . $result['name_IP'] . "</p>";
        echo "<p><strong>Map ID:</strong> " . $result['map_IP'] . "</p>";
        echo "<p><strong>Map Name:</strong> " . $result['name_map'] . "</p>";
        echo "<p><strong>Map Image:</strong> " . $result['image_map'] . "</p>";
        echo "<p><strong>Raw Coordinates:</strong> " . $result['coordinates_IP'] . "</p>";
        
        // Parse coordinates
        $coords = json_decode($result['coordinates_IP'], true);
        if ($coords) {
            echo "<p><strong>Parsed X:</strong> " . $coords['x'] . "</p>";
            echo "<p><strong>Parsed Y:</strong> " . $coords['y'] . "</p>";
            
            echo "<h4>CSS Positioning Test</h4>";
            echo "<div style='position: relative; width: 300px; height: 200px; border: 2px solid #ccc; background: #f0f0f0; margin: 20px 0;'>";
            echo "<div style='position: absolute; left: " . $coords['x'] . "%; top: " . $coords['y'] . "%; width: 12px; height: 12px; background-color: #ff4444; border: 2px solid #ffffff; border-radius: 50%; transform: translate(-50%, -50%); z-index: 10;'></div>";
            echo "<div style='position: absolute; left: 0; top: 0; color: #666; font-size: 10px; padding: 2px;'>0,0</div>";
            echo "<div style='position: absolute; right: 0; top: 0; color: #666; font-size: 10px; padding: 2px;'>100,0</div>";
            echo "<div style='position: absolute; left: 0; bottom: 0; color: #666; font-size: 10px; padding: 2px;'>0,100</div>";
            echo "<div style='position: absolute; right: 0; bottom: 0; color: #666; font-size: 10px; padding: 2px;'>100,100</div>";
            echo "<div style='position: absolute; left: 50%; top: 50%; color: #666; font-size: 10px; padding: 2px; transform: translate(-50%, -50%);'>Center (50,50)</div>";
            echo "</div>";
            echo "<p><em>Red dot should be at the center of the test box above if coordinates are correct.</em></p>";
            
        } else {
            echo "<p><strong>Error:</strong> Could not parse coordinates JSON</p>";
        }
    } else {
        echo "<p><strong>Error:</strong> Interest point not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
}
?>