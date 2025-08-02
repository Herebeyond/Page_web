<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file

// Get place ID from URL
$placeId = $_GET['id'] ?? null;

if (!$placeId || !is_numeric($placeId)) {
    header('Location: map_view.php');
    exit;
}

// TODO: Load place details from database
// For now, we'll create a placeholder

require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>

<div id="mainText">
    <div class="place-detail-container">
        <!-- Navigation -->
        <div style="margin-bottom: 20px;">
            <a href="map_view.php" style="
                color: #d4af37; 
                text-decoration: none; 
                display: inline-flex; 
                align-items: center; 
                gap: 8px;
                padding: 8px 16px;
                background: rgba(212, 175, 55, 0.1);
                border-radius: 5px;
                border: 1px solid rgba(212, 175, 55, 0.3);
                transition: all 0.3s ease;
            " onmouseover="this.style.background='rgba(212, 175, 55, 0.2)'" onmouseout="this.style.background='rgba(212, 175, 55, 0.1)'">
                ← Back to Map
            </a>
        </div>
        
        <!-- Place Content -->
        <div id="place-content">
            <div style="text-align: center; padding: 50px; color: #ccc;">
                <h2>🏗️ Place Details Page</h2>
                <p>Loading place information for ID: <?php echo htmlspecialchars($placeId); ?></p>
                <p><em>This page is under construction. The full place detail system will be implemented next.</em></p>
            </div>
        </div>
    </div>
</div>

<script>
    // TODO: Load place details via AJAX
    console.log('Place ID:', <?php echo json_encode($placeId); ?>);
</script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>
