<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file

// Get place ID from URL
$placeId = $_GET['id'] ?? null;

if (!$placeId || !is_numeric($placeId)) {
    header(LOCATION_MAP_VIEW);
    exit;
}

// Load place details from database
try {
    // Database connection is already included in page_init.php
    // require_once '../login/db.php'; // REMOVED - already included
    
    $stmt = $pdo->prepare("
        SELECT ip.*, ipt.name_IPT as type_name, ipt.color_IPT as type_color 
        FROM interest_points ip 
        LEFT JOIN IP_types ipt ON ip.type_IP = ipt.id_IPT 
        WHERE ip.id_IP = ?
    ");
    $stmt->execute([$placeId]);
    $place = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$place) {
        header(LOCATION_MAP_VIEW);
        exit;
    }
    
    // Parse coordinates
    $coordinates = json_decode($place['coordinates_IP'], true);
    $place['x'] = $coordinates['x'] ?? 0;
    $place['y'] = $coordinates['y'] ?? 0;
    
    // Create slug for folder path
    $slug = strtolower(trim($place['name_IP']));
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    $place['slug'] = $slug;
    $place['folder_path'] = "../images/places/{$slug}/";
    
} catch (Exception $e) {
    header(LOCATION_MAP_VIEW);
    exit;
}

require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>

<div id="mainText">
    <div class="place-detail-container">
        <!-- Navigation -->
        <div style="margin-bottom: 20px;">
            <a href="map_view.php" onfocus="this.style.outline='2px solid #d4af37'" style="
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
        
        <!-- Main Content Layout -->
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; align-items: start;">
            <!-- Left Column: Main Content -->
            <div class="main-content">
                <!-- Place Title -->
                <div class="place-title-section" style="margin-bottom: 30px;">
                    <h1 id="place-title" style="color: #d4af37; margin: 0; font-size: 2.5em; font-family: 'Cinzel', serif;">
                        <?php echo htmlspecialchars($place['name_IP']); ?>
                        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                            <button class="edit-btn" onclick="editTitle()" style="margin-left: 15px; background: #4CAF50; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 0.4em;">
                                ✏️ Edit
                            </button>
                        <?php endif; ?>
                    </h1>
                </div>
                
                <!-- Description Box -->
                <div class="description-section" style="margin-bottom: 40px;">
                    <div style="background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 25px; border: 1px solid rgba(212, 175, 55, 0.3);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <h3 style="color: #d4af37; margin: 0;">📝 Description</h3>
                            <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                                <button class="edit-btn" onclick="editDescription()" style="background: #4CAF50; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                                    ✏️ Edit
                                </button>
                            <?php endif; ?>
                        </div>
                        <div id="description-content" style="color: #f4cf47; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($place['description_IP'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Place Map Section -->
                <div class="place-map-section" style="margin-bottom: 40px;">
                    <div style="background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 25px; border: 1px solid rgba(212, 175, 55, 0.3);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <h3 style="color: #d4af37; margin: 0;">🗺️ Place Map</h3>
                            <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                                <div style="display: flex; gap: 10px;">
                                    <button class="edit-btn" onclick="changeMapImage()" style="background: #2196F3; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                                        🖼️ Change Map
                                    </button>
                                    <button class="edit-btn" onclick="toggleMapEditMode()" id="map-edit-btn" style="background: #4CAF50; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                                        ✏️ Add Points
                                    </button>
                                    <button class="edit-btn" onclick="toggleMapMoveMode()" id="map-move-btn" style="background: #9C27B0; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.9em;">
                                        🔄 Move Points
                                    </button>
                                    <button class="edit-btn" onclick="saveMapPoints()" id="map-save-btn" style="background: #ff6600; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.9em; display: none;">
                                        💾 Save Points
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Map Container -->
                        <div id="place-map-container" style="position: relative; width: 100%; background: #000; border-radius: 8px; overflow: hidden;">
                            <img id="place-map-image" src="" alt="Place map" style="width: 100%; height: 400px; object-fit: contain; display: block;">
                            <div id="place-map-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: default;"></div>
                        </div>
                        
                        <!-- Map Controls (Admin only) -->
                        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                        <div id="map-controls" style="margin-top: 15px; display: none;">
                            <div style="background: rgba(0, 0, 0, 0.5); padding: 15px; border-radius: 5px; border: 1px solid #444;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr 150px auto; gap: 10px; align-items: end;">
                                    <div>
                                        <label for="map-poi-name" style="color: #d4af37; font-size: 12px; display: block; margin-bottom: 5px;">Point Name</label>
                                        <input type="text" id="map-poi-name" placeholder="Enter point name" style="width: 100%; padding: 8px; border: none; border-radius: 3px; background: #333; color: white;">
                                    </div>
                                    <div>
                                        <label for="map-poi-description" style="color: #d4af37; font-size: 12px; display: block; margin-bottom: 5px;">Description</label>
                                        <input type="text" id="map-poi-description" placeholder="Enter description" style="width: 100%; padding: 8px; border: none; border-radius: 3px; background: #333; color: white;">
                                    </div>
                                    <div>
                                        <label for="map-poi-type" style="color: #d4af37; font-size: 12px; display: block; margin-bottom: 5px;">Type</label>
                                        <select id="map-poi-type" style="width: 100%; padding: 8px; border: none; border-radius: 3px; background: #333; color: white;">
                                            <option value="">Select type...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <button onclick="clearMapPoints()" style="background: #f44336; color: white; border: none; padding: 8px 12px; border-radius: 3px; cursor: pointer; font-size: 0.9em;">
                                            🗑️ Clear
                                        </button>
                                    </div>
                                </div>
                                <div style="margin-top: 10px; text-align: center;">
                                    <div id="map-mode-indicator" style="color: #aa0000; font-size: 14px;">
                                        Add Mode: <span id="map-mode-status">Inactive</span> | 
                                        Move Mode: <span id="map-move-status">Inactive</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Info Box (Wikipedia style) -->
            <div class="info-box" style="background: rgba(0, 0, 0, 0.4); border: 2px solid #d4af37; border-radius: 8px; padding: 20px; position: sticky; top: 20px;">
                <!-- Place Name -->
                <h2 style="color: #d4af37; margin: 0 0 15px 0; text-align: center; font-size: 1.4em; border-bottom: 2px solid #d4af37; padding-bottom: 10px;">
                    <?php echo htmlspecialchars($place['name_IP']); ?>
                </h2>
                
                <!-- Other Names -->
                <div class="other-names-section" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: #f4cf47;">Other Names:</strong>
                        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                            <button class="edit-btn" onclick="editOtherNames()" style="background: #4CAF50; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 0.8em;">
                                ✏️
                            </button>
                        <?php endif; ?>
                    </div>
                    <div id="other-names-content" style="color: #ccc; font-style: italic;">
                        <?php 
                        $otherNames = $place['other_names_IP'] ?? '';
                        if (empty($otherNames)) {
                            echo 'None';
                        } else {
                            $names = explode('|', $otherNames);
                            echo implode('<br>', array_map('htmlspecialchars', $names));
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Main Image -->
                <div class="main-image-section" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                            <button class="edit-btn" onclick="editMainImage()" style="background: #4CAF50; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 0.8em;">
                                ✏️
                            </button>
                        <?php endif; ?>
                    </div>
                    <div id="main-image-container" style="text-align: center;">
                        <!-- Main image will be loaded here -->
                    </div>
                </div>
                
                <!-- Type -->
                <div class="type-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                            <button class="edit-btn" onclick="editType()" style="background: #4CAF50; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 0.8em;">
                                ✏️
                            </button>
                        <?php endif; ?>
                    </div>
                    <div id="type-content" style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 15px; height: 15px; border-radius: 50%; background-color: <?php echo htmlspecialchars($place['type_color'] ?? '#ff4444'); ?>; display: inline-block;"></span>
                        <span style="color: #ccc;"><?php echo htmlspecialchars($place['type_name'] ?? 'Unknown'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Image Gallery -->
        <div class="gallery-section" style="margin-top: 50px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2 style="color: #d4af37; margin: 0;">🖼️ Image Gallery</h2>
                <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
                    <button class="edit-btn" onclick="editGallery()" style="background: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">
                        📁 Manage Images
                    </button>
                <?php endif; ?>
            </div>
            <div id="gallery-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                <!-- Gallery images will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Modals -->
<?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
<!-- Title Edit Modal -->
<div id="title-edit-modal" class="point-edit-modal" style="display: none;">
    <div class="point-edit-modal-content">
        <div class="point-edit-modal-header">
            <h2>✏️ Edit Place Title</h2>
            <button type="button" class="point-edit-modal-close" onclick="closeEditModal('title')">&times;</button>
        </div>
        <div class="point-edit-modal-body">
            <input type="text" id="title-edit-input" style="width: 100%; padding: 10px; font-size: 16px;" value="<?php echo htmlspecialchars($place['name_IP']); ?>">
        </div>
        <div class="point-edit-modal-footer">
            <button type="button" onclick="saveTitle()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✅ Confirm</button>
            <button type="button" onclick="closeEditModal('title')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✕ Cancel</button>
        </div>
    </div>
</div>

<!-- Description Edit Modal -->
<div id="description-edit-modal" class="point-edit-modal" style="display: none;">
    <div class="point-edit-modal-content">
        <div class="point-edit-modal-header">
            <h2>✏️ Edit Description</h2>
            <button type="button" class="point-edit-modal-close" onclick="closeEditModal('description')">&times;</button>
        </div>
        <div class="point-edit-modal-body">
            <textarea id="description-edit-input" style="width: 100%; height: 200px; padding: 10px; font-size: 14px; resize: vertical;"><?php echo htmlspecialchars($place['description_IP']); ?></textarea>
        </div>
        <div class="point-edit-modal-footer">
            <button type="button" onclick="saveDescription()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✅ Confirm</button>
            <button type="button" onclick="closeEditModal('description')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✕ Cancel</button>
        </div>
    </div>
</div>

<!-- Other Names Edit Modal -->
<div id="other-names-edit-modal" class="point-edit-modal" style="display: none;">
    <div class="point-edit-modal-content">
        <div class="point-edit-modal-header">
            <h2>✏️ Edit Other Names</h2>
            <button type="button" class="point-edit-modal-close" onclick="closeEditModal('other-names')">&times;</button>
        </div>
        <div class="point-edit-modal-body">
            <p style="color: #ccc; margin-bottom: 10px;">Separate multiple names with | (pipe symbol)</p>
            <textarea id="other-names-edit-input" style="width: 100%; height: 100px; padding: 10px; font-size: 14px;" placeholder="Name 1|Name 2|Name 3"><?php echo htmlspecialchars($place['other_names_IP'] ?? ''); ?></textarea>
        </div>
        <div class="point-edit-modal-footer">
            <button type="button" onclick="saveOtherNames()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✅ Confirm</button>
            <button type="button" onclick="closeEditModal('other-names')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✕ Cancel</button>
        </div>
    </div>
</div>

<!-- Type Edit Modal -->
<div id="type-edit-modal" class="point-edit-modal" style="display: none;">
    <div class="point-edit-modal-content">
        <div class="point-edit-modal-header">
            <h2>✏️ Edit Type</h2>
            <button type="button" class="point-edit-modal-close" onclick="closeEditModal('type')">&times;</button>
        </div>
        <div class="point-edit-modal-body">
            <select id="type-edit-select" style="width: 100%; padding: 10px; font-size: 14px;">
                <!-- Types will be loaded here -->
            </select>
        </div>
        <div class="point-edit-modal-footer">
            <button type="button" onclick="saveType()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✅ Confirm</button>
            <button type="button" onclick="closeEditModal('type')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✕ Cancel</button>
        </div>
    </div>
</div>

<!-- Image Upload Modal -->
<div id="image-upload-modal" class="point-edit-modal" style="display: none;">
    <div class="point-edit-modal-content" style="max-width: 600px;">
        <div class="point-edit-modal-header">
            <h2 id="upload-modal-title">📁 Manage Images</h2>
            <button type="button" class="point-edit-modal-close" onclick="closeEditModal('image-upload')">&times;</button>
        </div>
        <div class="point-edit-modal-body">
            <div id="upload-content">
                <!-- Upload interface will be loaded here -->
            </div>
        </div>
        <div class="point-edit-modal-footer">
            <button type="button" onclick="closeEditModal('image-upload')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✕ Close</button>
        </div>
    </div>
</div>

<!-- Map Change Modal -->
<div id="map-change-modal" class="point-edit-modal" style="display: none;">
    <div class="point-edit-modal-content" style="max-width: 600px;">
        <div class="point-edit-modal-header">
            <h2>🖼️ Change Place Map</h2>
            <button type="button" class="point-edit-modal-close" onclick="closeEditModal('map-change')">&times;</button>
        </div>
        <div class="point-edit-modal-body">
            <div style="margin-bottom: 20px; padding: 15px; background: rgba(255, 165, 0, 0.1); border: 1px solid #FFA500; border-radius: 5px;">
                <h4 style="color: #FFA500; margin: 0 0 10px 0;">⚠️ Important Notice</h4>
                <p style="margin: 0; color: #ddd; font-size: 14px;">
                    Changing the map will preserve all existing points. The points will maintain their relative positions (as percentages) on the new map.
                    <br><br>
                    <strong>Recommended:</strong> Save any unsaved points before changing the map.
                </p>
            </div>
            <div style="margin-bottom: 20px;">
                <h4 style="color: #d4af37;">Upload New Map Image</h4>
                <input type="file" id="map-image-upload" accept="image/*" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                <div style="color: #aaa; font-size: 12px; margin-bottom: 15px;">
                    Supported formats: JPG, PNG, GIF, WebP. Recommended size: 800x400 pixels or similar aspect ratio.
                </div>
                <button onclick="uploadMapImage()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                    🖼️ Upload New Map
                </button>
            </div>
        </div>
        <div class="point-edit-modal-footer">
            <button type="button" onclick="closeEditModal('map-change')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✕ Cancel</button>
        </div>
    </div>
</div>

<!-- Map Point Edit Modal -->
<div id="map-point-edit-modal" class="point-edit-modal" style="display: none;">
    <div class="point-edit-modal-content">
        <div class="point-edit-modal-header">
            <h2>✏️ Edit Map Point</h2>
            <button type="button" class="point-edit-modal-close" onclick="closeMapPointEditModal()">&times;</button>
        </div>
        <div class="point-edit-modal-body">
            <div style="margin-bottom: 15px;">
                <label for="edit-map-poi-name" style="color: #d4af37; font-size: 14px; display: block; margin-bottom: 5px;">Point Name</label>
                <input type="text" id="edit-map-poi-name" style="width: 100%; padding: 10px; border: none; border-radius: 3px; background: #333; color: white;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="edit-map-poi-description" style="color: #d4af37; font-size: 14px; display: block; margin-bottom: 5px;">Description</label>
                <textarea id="edit-map-poi-description" style="width: 100%; height: 100px; padding: 10px; border: none; border-radius: 3px; background: #333; color: white; resize: vertical;"></textarea>
            </div>
            <div style="margin-bottom: 15px;">
                <label for="edit-map-poi-type" style="color: #d4af37; font-size: 14px; display: block; margin-bottom: 5px;">Type</label>
                <select id="edit-map-poi-type" style="width: 100%; padding: 10px; border: none; border-radius: 3px; background: #333; color: white;">
                    <option value="">Select type...</option>
                </select>
            </div>
        </div>
        <div class="point-edit-modal-footer">
            <button type="button" onclick="saveMapPointEdit()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✅ Save Changes</button>
            <button type="button" onclick="deleteMapPoint()" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">🗑️ Delete Point</button>
            <button type="button" onclick="closeMapPointEditModal()" style="background: #666; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">✕ Cancel</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    const placeData = {
        id: <?php echo json_encode($place['id_IP']); ?>,
        name: <?php echo json_encode($place['name_IP']); ?>,
        slug: <?php echo json_encode($place['slug']); ?>,
        folderPath: <?php echo json_encode($place['folder_path']); ?>
    };
    
    let availableTypes = [];
    
    // Load initial data
    window.addEventListener('load', function() {
        loadMainImage();
        loadGalleryImages();
        loadPlaceMap();
        <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
        loadAvailableTypes();
        <?php endif; ?>
    });
    
    // Load main image
    function loadMainImage() {
        // Check for main image in the place folder
        const mainImageContainer = document.getElementById('main-image-container');
        const possibleExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        let imageFound = false;
        
        async function checkImage(extension) {
            try {
                const response = await fetch(`${placeData.folderPath}main.${extension}`);
                if (response.ok) {
                    mainImageContainer.innerHTML = `
                        <img src="${placeData.folderPath}main.${extension}" 
                             alt="Main image of ${placeData.name}" 
                             style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                    `;
                    return true;
                }
            } catch (error) {
                return false;
            }
            return false;
        }
        
        // Try each extension
        (async () => {
            for (const ext of possibleExtensions) {
                if (await checkImage(ext)) {
                    imageFound = true;
                    break;
                }
            }
            
            if (!imageFound) {
                mainImageContainer.innerHTML = `
                    <div style="padding: 30px; background: rgba(0,0,0,0.2); border-radius: 8px; text-align: center; color: #888;">
                        <p>📷</p>
                        <p style="font-size: 12px;">No main image</p>
                    </div>
                `;
            }
        })();
    }
    
    // Load gallery images
    async function loadGalleryImages() {
        try {
            const response = await fetch('./scriptes/place_image_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'list_images',
                    slug: placeData.slug
                })
            });
            
            const data = await response.json();
            const galleryContainer = document.getElementById('gallery-container');
            
            if (data.success && data.images && data.images.length > 0) {
                let galleryHtml = '';
                
                data.images.forEach((image, index) => {
                    galleryHtml += `
                        <div class="gallery-item" style="background: rgba(0,0,0,0.3); border-radius: 8px; overflow: hidden; transition: transform 0.3s ease;">
                            <img src="${image.thumb_path}" 
                                 alt="${image.name}" 
                                 onclick="openImageModal('${image.full_path}')"
                                 style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                 onmouseover="this.parentElement.style.transform='scale(1.02)'"
                                 onmouseout="this.parentElement.style.transform='scale(1)'">
                            <div style="padding: 10px;">
                                <p style="margin: 0; color: #f4cf47; font-size: 12px; text-align: center;">${image.name}</p>
                            </div>
                        </div>
                    `;
                });
                
                galleryContainer.innerHTML = galleryHtml;
            } else {
                galleryContainer.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: #888;">
                        <p style="font-size: 18px;">📷</p>
                        <p>No images in gallery yet</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading gallery:', error);
            document.getElementById('gallery-container').innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: #888;">
                    <p>Error loading gallery</p>
                </div>
            `;
        }
    }
    
    // Image modal for full size viewing
    function openImageModal(imagePath) {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 3000;
            cursor: pointer;
        `;
        
        modal.innerHTML = `
            <img src="${imagePath}" style="max-width: 90%; max-height: 90%; object-fit: contain;">
        `;
        
        modal.onclick = () => modal.remove();
        document.body.appendChild(modal);
    }
    
    // Place Map Functionality
    let mapData = null;
    let mapPoints = [];
    let mapEditMode = false;
    let mapMoveMode = false;
    let mapPointCounter = 0;
    let hasUnsavedMapChanges = false;
    let draggedPoint = null;
    let dragOffset = { x: 0, y: 0 };
    
    // Load place map
    async function loadPlaceMap() {
        try {
            const response = await fetch('./scriptes/place_map_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_map_data',
                    place_id: placeData.id
                })
            });
            
            const data = await response.json();
            if (data.success && data.map) {
                mapData = data.map;
                loadMapImage();
                loadMapPoints();
            } else {
                // No map data found, load default map
                loadDefaultMapImage();
            }
        } catch (error) {
            console.error('Error loading map:', error);
            // On error, load default map
            loadDefaultMapImage();
        }
    }
    
    // Load map image
    function loadMapImage() {
        const mapImage = document.getElementById('place-map-image');
        // First try to load from place folder
        const placeMapPath = `${placeData.folderPath}map/${mapData.image_map}`;
        
        mapImage.onerror = function() {
            // If place-specific map doesn't exist, try default
            mapImage.onerror = function() {
                // If default doesn't exist, show placeholder
                mapImage.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMzMzIi8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIE1hcCBBdmFpbGFibGU8L3RleHQ+Cjwvc3ZnPg==';
            };
            mapImage.src = '../images/maps/default-map.jpg';
        };
        
        mapImage.src = placeMapPath;
    }
    
    // Load default map image when no map data is available
    function loadDefaultMapImage() {
        const mapImage = document.getElementById('place-map-image');
        
        // List of possible paths for default map
        const defaultMapPaths = [
            '../images/maps/default-map.jpg',
            '../images/maps/map_monde.png',
            './images/maps/default-map.jpg',
            './images/maps/map_monde.png'
        ];
        
        let currentPathIndex = 0;
        
        function tryNextPath() {
            if (currentPathIndex < defaultMapPaths.length) {
                const currentPath = defaultMapPaths[currentPathIndex];
                console.log('Trying default map path:', currentPath);
                
                mapImage.onerror = function() {
                    currentPathIndex++;
                    tryNextPath();
                };
                
                mapImage.onload = function() {
                    console.log('Successfully loaded default map from:', currentPath);
                };
                
                mapImage.src = currentPath;
            } else {
                // All paths failed, show placeholder
                console.log('All default map paths failed, showing placeholder');
                mapImage.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMzMzIi8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkRlZmF1bHQgTWFwPC90ZXh0Pgo8L3N2Zz4=';
            }
        }
        
        tryNextPath();
    }
    
    // Load map points
    async function loadMapPoints() {
        if (!mapData) return;
        
        try {
            const response = await fetch('./scriptes/place_map_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'load_map_points',
                    map_id: mapData.id_map
                })
            });
            
            const data = await response.json();
            if (data.success && data.points) {
                mapPoints = [];
                
                data.points.forEach(dbPoint => {
                    const point = {
                        id: generateMapPointId(),
                        database_id: dbPoint.id_IP,
                        name: dbPoint.name_IP,
                        description: dbPoint.description_IP,
                        type: dbPoint.type_name || dbPoint.type_IP,
                        x: parseFloat(dbPoint.x_IP),
                        y: parseFloat(dbPoint.y_IP),
                        type_color: dbPoint.type_color || '#ff4444'
                    };
                    mapPoints.push(point);
                });
                
                drawMapPoints();
            }
        } catch (error) {
            console.error('Error loading map points:', error);
        }
    }
    
    // Generate unique ID for map points
    function generateMapPointId() {
        return 'map_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Draw all map points
    function drawMapPoints() {
        const overlay = document.getElementById('place-map-overlay');
        // Clear existing points
        overlay.querySelectorAll('.map-point').forEach(point => point.remove());
        
        mapPoints.forEach(point => {
            createMapPointElement(point);
        });
    }
    
    // Create map point element
    function createMapPointElement(point) {
        const overlay = document.getElementById('place-map-overlay');
        const pointElement = document.createElement('div');
        pointElement.className = 'map-point';
        pointElement.dataset.pointId = point.id;
        pointElement.style.cssText = `
            position: absolute;
            left: ${point.x}%;
            top: ${point.y}%;
            width: 12px;
            height: 12px;
            background-color: ${point.type_color};
            border: 2px solid white;
            border-radius: 50%;
            cursor: pointer;
            transform: translate(-50%, -50%);
            z-index: 10;
            transition: transform 0.2s ease;
        `;
        
        // Add hover effect
        pointElement.addEventListener('mouseenter', function() {
            this.style.transform = 'translate(-50%, -50%) scale(1.3)';
        });
        
        pointElement.addEventListener('mouseleave', function() {
            this.style.transform = 'translate(-50%, -50%) scale(1)';
        });
        
        // Add tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'map-point-tooltip';
        tooltip.innerHTML = `
            <strong>${point.name}</strong><br>
            Type: ${point.type}<br>
            <p style="margin: 5px 0 0 0; font-size: 12px;">${point.description}</p>
        `;
        tooltip.style.cssText = `
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 20;
            border: 1px solid #d4af37;
        `;
        
        pointElement.appendChild(tooltip);
        
        // Show tooltip on hover
        pointElement.addEventListener('mouseenter', function() {
            tooltip.style.opacity = '1';
        });
        
        pointElement.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
        });
        
        // Enable dragging if move mode is active
        if (mapMoveMode) {
            pointElement.style.cursor = 'grab';
            pointElement.addEventListener('mousedown', startDragging);
        }
        
        // Add click to edit (when no modes are active)
        pointElement.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (!mapEditMode && !mapMoveMode) {
                openMapPointEditModal(pointElement);
            }
        });
        
        overlay.appendChild(pointElement);
    }
    
    <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
    // Map editing functions (admin only)
    
    // Toggle map edit mode
    function toggleMapEditMode() {
        // If move mode is active, deactivate it first
        if (mapMoveMode) {
            toggleMapMoveMode();
        }
        
        mapEditMode = !mapEditMode;
        const overlay = document.getElementById('place-map-overlay');
        const controls = document.getElementById('map-controls');
        const editBtn = document.getElementById('map-edit-btn');
        const saveBtn = document.getElementById('map-save-btn');
        const statusSpan = document.getElementById('map-mode-status');
        
        if (mapEditMode) {
            overlay.style.cursor = 'crosshair';
            overlay.style.backgroundColor = 'rgba(0, 170, 0, 0.1)';
            controls.style.display = 'block';
            editBtn.textContent = '❌ Exit Add';
            editBtn.style.background = '#f44336';
            saveBtn.style.display = hasUnsavedMapChanges ? 'inline-block' : 'none';
            statusSpan.textContent = 'Active';
            statusSpan.style.color = '#00aa00';
            
            // Add click listener for adding points
            overlay.addEventListener('click', addMapPoint);
        } else {
            overlay.style.cursor = 'default';
            overlay.style.backgroundColor = 'transparent';
            controls.style.display = 'none';
            editBtn.textContent = '✏️ Add Points';
            editBtn.style.background = '#4CAF50';
            saveBtn.style.display = 'none';
            statusSpan.textContent = 'Inactive';
            statusSpan.style.color = '#aa0000';
            
            // Remove click listener
            overlay.removeEventListener('click', addMapPoint);
        }
    }
    
    // Toggle map move mode
    function toggleMapMoveMode() {
        // If edit mode is active, deactivate it first
        if (mapEditMode) {
            toggleMapEditMode();
        }
        
        mapMoveMode = !mapMoveMode;
        const overlay = document.getElementById('place-map-overlay');
        const moveBtn = document.getElementById('map-move-btn');
        const saveBtn = document.getElementById('map-save-btn');
        const moveStatusSpan = document.getElementById('map-move-status');
        
        if (mapMoveMode) {
            overlay.style.cursor = 'move';
            overlay.style.backgroundColor = 'rgba(156, 39, 176, 0.1)';
            moveBtn.textContent = '❌ Exit Move';
            moveBtn.style.background = '#f44336';
            saveBtn.style.display = hasUnsavedMapChanges ? 'inline-block' : 'none';
            moveStatusSpan.textContent = 'Active';
            moveStatusSpan.style.color = '#9C27B0';
            
            // Enable dragging for all points
            enablePointDragging();
        } else {
            overlay.style.cursor = 'default';
            overlay.style.backgroundColor = 'transparent';
            moveBtn.textContent = '🔄 Move Points';
            moveBtn.style.background = '#9C27B0';
            saveBtn.style.display = 'none';
            moveStatusSpan.textContent = 'Inactive';
            moveStatusSpan.style.color = '#aa0000';
            
            // Disable dragging
            disablePointDragging();
        }
    }
    
    // Add map point on click
    function addMapPoint(e) {
        if (!mapEditMode) return;
        
        // Don't add point if clicking on an existing point
        if (e.target.classList.contains('map-point') || e.target.closest('.map-point')) {
            return;
        }
        
        const name = document.getElementById('map-poi-name').value.trim();
        const description = document.getElementById('map-poi-description').value.trim() || 'No description';
        const type = document.getElementById('map-poi-type').value.trim();
        
        if (!name) {
            showMessage('⚠️ Point name is required!', 'error');
            return;
        }
        
        if (!type) {
            showMessage('⚠️ Point type is required!', 'error');
            return;
        }
        
        const rect = e.target.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;
        
        // Get type color
        const selectedType = availableTypes.find(t => t.name_IPT === type);
        const typeColor = selectedType ? selectedType.color_IPT : '#ff4444';
        
        const point = {
            id: generateMapPointId(),
            name: name,
            description: description,
            type: type,
            x: xPercent,
            y: yPercent,
            type_color: typeColor
        };
        
        mapPoints.push(point);
        createMapPointElement(point);
        
        // Clear inputs
        document.getElementById('map-poi-name').value = '';
        document.getElementById('map-poi-description').value = '';
        document.getElementById('map-poi-type').selectedIndex = 0;
        
        markMapAsUnsaved();
        showMessage('✅ Point added to map! Use "Save Points" to make it permanent.', 'success');
    }
    
    // Save map points
    async function saveMapPoints() {
        if (mapPoints.length === 0) {
            showMessage('ℹ️ No points to save', 'info');
            return;
        }
        
        try {
            const response = await fetch('./scriptes/place_map_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'save_points',
                    points: mapPoints,
                    map_id: mapData ? mapData.id_map : null,
                    place_id: placeData.id
                })
            });
            
            const data = await response.json();
            if (data.success) {
                // Update mapData if a new map was created
                if (data.map_id && (!mapData || !mapData.id_map)) {
                    mapData = {
                        id_map: data.map_id,
                        image_map: 'default-map.jpg',
                        name_map: placeData.name + ' Map',
                        place_id: placeData.id
                    };
                }
                
                // Update points with database IDs
                if (data.saved_points) {
                    data.saved_points.forEach(savedPoint => {
                        const localPoint = mapPoints.find(p => p.id === savedPoint.local_id);
                        if (localPoint) {
                            localPoint.database_id = savedPoint.database_id;
                        }
                    });
                }
                
                markMapAsSaved();
                showMessage('✅ Map points saved successfully!', 'success');
            } else {
                showMessage('❌ Error saving points: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error saving map points:', error);
            showMessage('❌ Connection error while saving points', 'error');
        }
    }
    
    // Clear map points
    function clearMapPoints() {
        const localPoints = mapPoints.filter(p => !p.database_id);
        
        if (localPoints.length === 0) {
            showMessage('ℹ️ No local points to clear', 'info');
            return;
        }
        
        if (confirm(`Clear ${localPoints.length} unsaved point(s)?`)) {
            // Remove local points
            mapPoints = mapPoints.filter(p => p.database_id);
            drawMapPoints();
            markMapAsUnsaved();
            showMessage('✅ Local points cleared', 'success');
        }
    }
    
    // Mark map as unsaved
    function markMapAsUnsaved() {
        hasUnsavedMapChanges = true;
        const saveBtn = document.getElementById('map-save-btn');
        if (saveBtn && (mapEditMode || mapMoveMode)) {
            saveBtn.style.display = 'inline-block';
            saveBtn.style.animation = 'pulse 2s infinite';
        }
    }
    
    // Mark map as saved
    function markMapAsSaved() {
        hasUnsavedMapChanges = false;
        const saveBtn = document.getElementById('map-save-btn');
        if (saveBtn) {
            saveBtn.style.display = 'none';
            saveBtn.style.animation = '';
        }
    }
    
    // Enable point dragging
    function enablePointDragging() {
        const points = document.querySelectorAll('.map-point');
        points.forEach(point => {
            point.style.cursor = 'grab';
            point.addEventListener('mousedown', startDragging);
        });
    }
    
    // Disable point dragging
    function disablePointDragging() {
        const points = document.querySelectorAll('.map-point');
        points.forEach(point => {
            point.style.cursor = 'pointer';
            point.removeEventListener('mousedown', startDragging);
        });
    }
    
    // Start dragging a point
    function startDragging(e) {
        if (!mapMoveMode) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        draggedPoint = e.target;
        draggedPoint.style.cursor = 'grabbing';
        draggedPoint.style.zIndex = '1000';
        
        const rect = document.getElementById('place-map-overlay').getBoundingClientRect();
        const pointRect = draggedPoint.getBoundingClientRect();
        
        dragOffset.x = e.clientX - pointRect.left - pointRect.width / 2;
        dragOffset.y = e.clientY - pointRect.top - pointRect.height / 2;
        
        document.addEventListener('mousemove', dragPoint);
        document.addEventListener('mouseup', stopDragging);
    }
    
    // Drag point to new position
    function dragPoint(e) {
        if (!draggedPoint || !mapMoveMode) return;
        
        const overlay = document.getElementById('place-map-overlay');
        const rect = overlay.getBoundingClientRect();
        
        const x = e.clientX - rect.left - dragOffset.x;
        const y = e.clientY - rect.top - dragOffset.y;
        
        // Convert to percentages
        const xPercent = Math.max(0, Math.min(100, (x / rect.width) * 100));
        const yPercent = Math.max(0, Math.min(100, (y / rect.height) * 100));
        
        // Update point position
        draggedPoint.style.left = xPercent + '%';
        draggedPoint.style.top = yPercent + '%';
        
        // Update point data
        const pointId = draggedPoint.dataset.pointId;
        const point = mapPoints.find(p => p.id === pointId);
        if (point) {
            point.x = xPercent;
            point.y = yPercent;
            markMapAsUnsaved();
        }
    }
    
    // Stop dragging
    function stopDragging(e) {
        if (draggedPoint) {
            draggedPoint.style.cursor = 'grab';
            draggedPoint.style.zIndex = '10';
            draggedPoint = null;
        }
        
        document.removeEventListener('mousemove', dragPoint);
        document.removeEventListener('mouseup', stopDragging);
    }
    
    // Point editing functions
    function openMapPointEditModal(pointElement) {
        const modal = document.getElementById('map-point-edit-modal');
        const pointId = pointElement.dataset.pointId;
        
        // Find the point data
        const point = mapPoints.find(p => p.id === pointId);
        if (!point) return;
        
        document.getElementById('edit-map-poi-name').value = point.name || '';
        document.getElementById('edit-map-poi-description').value = point.description || '';
        
        // Store the point ID for saving
        modal.dataset.editingPointId = pointId;
        
        // Load types and then set the selected value
        loadMapEditPointTypes().then(() => {
            document.getElementById('edit-map-poi-type').value = point.type || '';
        });
        
        modal.style.display = 'block';
    }
    
    function closeMapPointEditModal() {
        document.getElementById('map-point-edit-modal').style.display = 'none';
    }
    
    async function saveMapPointEdit() {
        const modal = document.getElementById('map-point-edit-modal');
        const pointId = modal.dataset.editingPointId;
        const newName = document.getElementById('edit-map-poi-name').value.trim();
        const newDescription = document.getElementById('edit-map-poi-description').value.trim();
        const newType = document.getElementById('edit-map-poi-type').value.trim();
        
        if (!newName) {
            showMessage('⚠️ Please enter a name for the point', 'error');
            return;
        }
        
        if (!newType) {
            showMessage('⚠️ Please select a type for the point', 'error');
            return;
        }
        
        // Find the point data
        const mapPoint = mapPoints.find(p => p.id === pointId);
        if (!mapPoint) {
            showMessage('❌ Point data not found', 'error');
            return;
        }
        
        try {
            // If point has database ID, update in database
            if (mapPoint.database_id) {
                const response = await fetch('./scriptes/place_map_manager.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_point',
                        database_id: mapPoint.database_id,
                        name: newName,
                        description: newDescription,
                        type: newType,
                        x: mapPoint.x,
                        y: mapPoint.y
                    })
                });
                
                const data = await response.json();
                if (!data.success) {
                    showMessage('❌ Database error: ' + (data.message || 'Unknown error'), 'error');
                    return;
                }
            }
            
            // Update local data
            const typeInfo = availableTypes.find(t => t.name_IPT === newType);
            
            mapPoint.name = newName;
            mapPoint.description = newDescription;
            mapPoint.type = newType;
            if (typeInfo) {
                mapPoint.type_color = typeInfo.color_IPT;
            }
            
            // Update point element appearance
            const pointElement = document.querySelector(`[data-point-id="${pointId}"]`);
            if (pointElement) {
                // Update point color
                if (typeInfo) {
                    pointElement.style.backgroundColor = typeInfo.color_IPT;
                }
                
                // Update tooltip
                const tooltip = pointElement.querySelector('.map-point-tooltip');
                if (tooltip) {
                    tooltip.innerHTML = `
                        <strong>${newName}</strong><br>
                        Type: ${newType}<br>
                        <p style="margin: 5px 0 0 0; font-size: 12px;">${newDescription}</p>
                    `;
                }
            }
            
            // Mark as unsaved if it's a local point (no database_id yet)
            if (!mapPoint.database_id) {
                markMapAsUnsaved();
            }
            
            closeMapPointEditModal();
            showMessage('✅ Point updated successfully!', 'success');
            
        } catch (error) {
            console.error('Error updating point:', error);
            showMessage('❌ Connection error while updating point', 'error');
        }
    }
    
    async function deleteMapPoint() {
        const modal = document.getElementById('map-point-edit-modal');
        const pointId = modal.dataset.editingPointId;
        
        if (confirm('Are you sure you want to delete this point?')) {
            try {
                // Find the point data
                const mapPoint = mapPoints.find(p => p.id === pointId);
                
                // If point has database ID, delete from database
                if (mapPoint && mapPoint.database_id) {
                    const response = await fetch('./scriptes/place_map_manager.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'delete_point',
                            database_id: mapPoint.database_id
                        })
                    });
                    
                    const data = await response.json();
                    if (!data.success) {
                        showMessage('❌ Database error: ' + (data.message || 'Unknown error'), 'error');
                        return;
                    }
                }
                
                // Remove from DOM
                const pointElement = document.querySelector(`[data-point-id="${pointId}"]`);
                if (pointElement) {
                    pointElement.remove();
                }
                
                // Remove from mapPoints array
                const index = mapPoints.findIndex(p => p.id === pointId);
                if (index > -1) {
                    mapPoints.splice(index, 1);
                }
                
                closeMapPointEditModal();
                
                // Mark as unsaved only if it was a local point (no database_id)
                if (!mapPoint || !mapPoint.database_id) {
                    markMapAsUnsaved();
                }
                
                showMessage('✅ Point deleted successfully!', 'success');
                
            } catch (error) {
                console.error('Error deleting point:', error);
                showMessage('❌ Connection error while deleting point', 'error');
            }
        }
    }
    
    function loadMapEditPointTypes() {
        return new Promise((resolve) => {
            const typeSelect = document.getElementById('edit-map-poi-type');
            if (!typeSelect) {
                console.error('Edit map type select element not found');
                resolve();
                return;
            }
            
            typeSelect.innerHTML = '<option value="">Select type...</option>';
            
            availableTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.name_IPT;
                option.textContent = type.name_IPT;
                typeSelect.appendChild(option);
            });
            
            resolve();
        });
    }
    
    // Load map point types in dropdown
    function loadMapPointTypes() {
        const typeSelect = document.getElementById('map-poi-type');
        typeSelect.innerHTML = '<option value="">Select type...</option>';
        
        availableTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = type.name_IPT;
            option.textContent = type.name_IPT;
            typeSelect.appendChild(option);
        });
    }
    
    // Update map point types when available types are loaded
    const originalLoadAvailableTypes = loadAvailableTypes;
    loadAvailableTypes = async function() {
        await originalLoadAvailableTypes();
        loadMapPointTypes();
    };
    
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
    // Admin editing functions
    
    // Load available types
    async function loadAvailableTypes() {
        try {
            const response = await fetch('./scriptes/place_map_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'load_types'
                })
            });
            
            const data = await response.json();
            if (data.success) {
                availableTypes = data.types || [];
            }
        } catch (error) {
            console.error('Error loading types:', error);
        }
    }
    
    // Edit functions
    function editTitle() {
        document.getElementById('title-edit-modal').style.display = 'block';
    }
    
    function editDescription() {
        document.getElementById('description-edit-modal').style.display = 'block';
    }
    
    function editOtherNames() {
        document.getElementById('other-names-edit-modal').style.display = 'block';
    }
    
    function editType() {
        const select = document.getElementById('type-edit-select');
        select.innerHTML = '';
        
        availableTypes.forEach(type => {
            const option = document.createElement('option');
            option.value = type.id_IPT;
            option.textContent = type.name_IPT;
            if (type.id_IPT == <?php echo json_encode($place['type_IP']); ?>) {
                option.selected = true;
            }
            select.appendChild(option);
        });
        
        document.getElementById('type-edit-modal').style.display = 'block';
    }
    
    function editMainImage() {
        document.getElementById('upload-modal-title').textContent = '📷 Edit Main Image';
        document.getElementById('upload-content').innerHTML = `
            <div style="margin-bottom: 20px;">
                <h4 style="color: #d4af37;">Upload New Main Image</h4>
                <input type="file" id="main-image-upload" accept="image/*" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                <button onclick="uploadMainImage()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                    📤 Upload Main Image
                </button>
            </div>
        `;
        document.getElementById('image-upload-modal').style.display = 'block';
    }
    
    function editGallery() {
        document.getElementById('upload-modal-title').textContent = '📁 Manage Gallery Images';
        document.getElementById('upload-content').innerHTML = `
            <div style="margin-bottom: 20px;">
                <h4 style="color: #d4af37;">Upload Gallery Images</h4>
                <input type="file" id="gallery-images-upload" accept="image/*" multiple style="width: 100%; padding: 10px; margin-bottom: 10px;">
                <button onclick="uploadGalleryImages()" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                    📤 Upload Images
                </button>
            </div>
            <div id="current-images-list">
                <h4 style="color: #d4af37;">Current Images</h4>
                <div id="manage-gallery-list">Loading...</div>
            </div>
        `;
        document.getElementById('image-upload-modal').style.display = 'block';
        loadManageGallery();
    }
    
    function changeMapImage() {
        document.getElementById('map-change-modal').style.display = 'block';
    }
    
    function closeEditModal(type) {
        let modalId;
        if (type === 'image-upload') {
            modalId = 'image-upload-modal';
        } else if (type === 'map-change') {
            modalId = 'map-change-modal';
        } else {
            modalId = type + '-edit-modal';
        }
        
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Save functions
    async function saveTitle() {
        const newTitle = document.getElementById('title-edit-input').value.trim();
        if (!newTitle) return;
        
        try {
            await updatePlaceData({name_IP: newTitle});
            document.getElementById('place-title').firstChild.textContent = newTitle;
            document.querySelector('.info-box h2').textContent = newTitle;
            closeEditModal('title');
            showMessage('✅ Title updated successfully!', 'success');
        } catch (error) {
            showMessage('❌ Error updating title', 'error');
        }
    }
    
    async function saveDescription() {
        const newDescription = document.getElementById('description-edit-input').value.trim();
        
        try {
            await updatePlaceData({description_IP: newDescription});
            document.getElementById('description-content').innerHTML = newDescription.replace(/\n/g, '<br>');
            closeEditModal('description');
            showMessage('✅ Description updated successfully!', 'success');
        } catch (error) {
            showMessage('❌ Error updating description', 'error');
        }
    }
    
    async function saveOtherNames() {
        const newOtherNames = document.getElementById('other-names-edit-input').value.trim();
        
        try {
            await updatePlaceData({other_names_IP: newOtherNames});
            const content = document.getElementById('other-names-content');
            if (newOtherNames) {
                const names = newOtherNames.split('|').map(name => name.trim()).filter(name => name);
                content.innerHTML = names.join('<br>');
            } else {
                content.innerHTML = 'None';
            }
            closeEditModal('other-names');
            showMessage('✅ Other names updated successfully!', 'success');
        } catch (error) {
            showMessage('❌ Error updating other names: ' + error.message, 'error');
        }
    }
    
    async function saveType() {
        const newType = document.getElementById('type-edit-select').value;
        if (!newType) return;
        
        try {
            await updatePlaceData({type_IP: newType});
            const typeData = availableTypes.find(t => t.id_IPT == newType);
            const typeContent = document.getElementById('type-content');
            typeContent.innerHTML = `
                <span style="width: 15px; height: 15px; border-radius: 50%; background-color: ${typeData?.color_IPT || '#ff4444'}; display: inline-block;"></span>
                <span style="color: #ccc;">${typeData?.name_IPT || 'Unknown'}</span>
            `;
            closeEditModal('type');
            showMessage('✅ Type updated successfully!', 'success');
        } catch (error) {
            showMessage('❌ Error updating type', 'error');
        }
    }
    
    // Update place data in database
    async function updatePlaceData(data) {
        const response = await fetch('./scriptes/place_manager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_place',
                place_id: placeData.id,
                ...data
            })
        });
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Update failed');
        }
        return result;
    }
    
    // Image upload functions
    async function uploadMainImage() {
        const fileInput = document.getElementById('main-image-upload');
        const file = fileInput.files[0];
        if (!file) return;
        
        const formData = new FormData();
        formData.append('action', 'upload_main_image');
        formData.append('slug', placeData.slug);
        formData.append('image', file);
        
        try {
            const response = await fetch('./scriptes/place_image_manager.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                loadMainImage();
                loadGalleryImages();
                closeEditModal('image-upload');
                showMessage('✅ Main image uploaded successfully!', 'success');
            } else {
                showMessage('❌ Error uploading image: ' + data.message, 'error');
            }
        } catch (error) {
            showMessage('❌ Error uploading image', 'error');
        }
    }
    
    async function uploadGalleryImages() {
        const fileInput = document.getElementById('gallery-images-upload');
        const files = fileInput.files;
        if (files.length === 0) return;
        
        for (let file of files) {
            const formData = new FormData();
            formData.append('action', 'upload_gallery_image');
            formData.append('slug', placeData.slug);
            formData.append('image', file);
            
            try {
                const response = await fetch('./scriptes/place_image_manager.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (!data.success) {
                    showMessage(`❌ Error uploading ${file.name}: ${data.message}`, 'error');
                }
            } catch (error) {
                showMessage(`❌ Error uploading ${file.name}`, 'error');
            }
        }
        
        loadGalleryImages();
        loadManageGallery();
        showMessage('✅ Images uploaded successfully!', 'success');
    }
    
    async function uploadMapImage() {
        const fileInput = document.getElementById('map-image-upload');
        const file = fileInput.files[0];
        if (!file) {
            showMessage('⚠️ Please select an image file first!', 'error');
            return;
        }
        
        // Check file type
        if (!file.type.startsWith('image/')) {
            showMessage('⚠️ Please select a valid image file!', 'error');
            return;
        }
        
        // Check file size (max 5MB to be safe)
        if (file.size > 5 * 1024 * 1024) {
            showMessage('⚠️ Image file is too large. Maximum size is 5MB.', 'error');
            return;
        }
        
        // Check if there are unsaved points
        const unsavedPoints = mapPoints.filter(p => !p.database_id);
        if (unsavedPoints.length > 0) {
            if (!confirm(`You have ${unsavedPoints.length} unsaved point(s). They will be lost if you change the map now. Continue anyway?`)) {
                return;
            }
        }
        
        try {
            showMessage('⏳ Uploading map image...', 'info');
            
            console.log('Converting file to base64...', file.name, file.size, 'bytes');
            const base64Data = await fileToBase64(file);
            console.log('Base64 conversion complete, size:', base64Data.length, 'characters');
            
            const requestData = {
                action: 'change_map_image',
                place_id: placeData.id,
                file_data: base64Data,
                file_name: file.name,
                file_type: file.type
            };
            
            console.log('Sending request to server...', requestData);
            
            const response = await fetch('./scriptes/place_map_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', [...response.headers.entries()]);
            
            let data;
            let responseText;
            
            // First, read the response as text
            try {
                responseText = await response.text();
                console.log('Raw response text:', responseText);
            } catch (textError) {
                console.error('Failed to read response as text:', textError);
                showMessage(`❌ Failed to read server response. Status: ${response.status}`, 'error');
                return;
            }
            
            // Then try to parse it as JSON
            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                console.error('JSON parse error:', jsonError);
                console.error('Response was not valid JSON:', responseText);
                showMessage(`❌ Server returned invalid JSON. Status: ${response.status}. Check console.`, 'error');
                return;
            }
            
            if (data.success) {
                // Update mapData if it exists
                if (mapData) {
                    mapData.image_map = data.new_filename;
                } else {
                    // Create new mapData object
                    mapData = {
                        id_map: data.map_id,
                        image_map: data.new_filename,
                        name_map: placeData.name + ' Map',
                        place_id: placeData.id
                    };
                }
                
                // Reload the map image
                loadMapImage();
                
                // Redraw existing saved points (unsaved points are already filtered out)
                const savedPoints = mapPoints.filter(p => p.database_id);
                mapPoints = savedPoints;
                drawMapPoints();
                
                closeEditModal('map-change');
                showMessage('✅ Map image updated successfully! Points preserved.', 'success');
            } else {
                showMessage('❌ Error updating map: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error uploading map:', error);
            showMessage('❌ Error uploading map image', 'error');
        }
    }
    
    // Helper function to convert file to base64
    function fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result.split(',')[1]); // Remove data:image/...;base64, prefix
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
    
    function loadManageGallery() {
        // Load detailed gallery management interface
        fetch('./scriptes/place_image_manager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'list_images',
                slug: placeData.slug
            })
        })
        .then(response => response.json())
        .then(data => {
            const manageList = document.getElementById('manage-gallery-list');
            
            if (data.success && data.images && data.images.length > 0) {
                let html = '<div style="max-height: 400px; overflow-y: auto;">';
                
                data.images.forEach((image, index) => {
                    html += `
                        <div class="manage-image-item" style="
                            display: flex; 
                            align-items: center; 
                            gap: 15px; 
                            padding: 10px; 
                            border: 1px solid #444; 
                            border-radius: 5px; 
                            margin-bottom: 10px;
                            background: rgba(0,0,0,0.2);
                        ">
                            <img src="${image.thumb_path}" 
                                 alt="${image.name}" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                            <div style="flex: 1;">
                                <div style="margin-bottom: 5px;">
                                    <strong style="color: #d4af37;">${image.name}</strong>
                                </div>
                                <div style="color: #888; font-size: 0.9em;">
                                    Size: ${Math.round(image.size / 1024)}KB
                                </div>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <button onclick="renameImagePrompt('${image.name}')" 
                                        style="background: #2196F3; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 0.8em;">
                                    ✏️ Rename
                                </button>
                                <button onclick="deleteImageConfirm('${image.name}')" 
                                        style="background: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 0.8em;">
                                    🗑️ Delete
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                manageList.innerHTML = html;
            } else {
                manageList.innerHTML = '<p style="color: #888; text-align: center; padding: 20px;">No gallery images found</p>';
            }
        })
        .catch(error => {
            console.error('Error loading manage gallery:', error);
            document.getElementById('manage-gallery-list').innerHTML = '<p style="color: #f44336;">Error loading images</p>';
        });
    }
    
    // Image management functions
    function renameImagePrompt(currentName) {
        const newName = prompt(`Rename image "${currentName}" to:`, currentName);
        if (newName && newName !== currentName && newName.trim()) {
            renameImage(currentName, newName.trim());
        }
    }
    
    async function renameImage(oldName, newName) {
        try {
            const response = await fetch('./scriptes/place_image_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'rename_image',
                    slug: placeData.slug,
                    old_name: oldName,
                    new_name: newName
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showMessage('✅ Image renamed successfully!', 'success');
                loadGalleryImages(); // Refresh main gallery
                loadManageGallery(); // Refresh manage gallery
            } else {
                showMessage('❌ Error renaming image: ' + data.message, 'error');
            }
        } catch (error) {
            showMessage('❌ Error renaming image', 'error');
        }
    }
    
    function deleteImageConfirm(imageName) {
        if (confirm(`Are you sure you want to delete "${imageName}"? This action cannot be undone.`)) {
            deleteImage(imageName);
        }
    }
    
    async function deleteImage(imageName) {
        try {
            const response = await fetch('./scriptes/place_image_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_image',
                    slug: placeData.slug,
                    image_name: imageName
                })
            });
            
            const data = await response.json();
            if (data.success) {
                showMessage('✅ Image deleted successfully!', 'success');
                loadGalleryImages(); // Refresh main gallery
                loadManageGallery(); // Refresh manage gallery
            } else {
                showMessage('❌ Error deleting image: ' + data.message, 'error');
            }
        } catch (error) {
            showMessage('❌ Error deleting image', 'error');
        }
    }
    
    <?php endif; ?>
    
    // Show message function
    function showMessage(message, type = 'info') {
        const existingMessage = document.getElementById('temp-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        const messageElement = document.createElement('div');
        messageElement.id = 'temp-message';
        messageElement.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-family: 'Cinzel', serif;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        `;
        
        switch(type) {
            case 'success':
                messageElement.style.background = 'linear-gradient(135deg, #00aa00, #00cc00)';
                messageElement.style.color = 'white';
                messageElement.style.border = '2px solid #00ff00';
                break;
            case 'error':
                messageElement.style.background = 'linear-gradient(135deg, #ff4444, #ff6666)';
                messageElement.style.color = 'white';
                messageElement.style.border = '2px solid #ff0000';
                break;
            default:
                messageElement.style.background = 'linear-gradient(135deg, #d4af37, #f4cf47)';
                messageElement.style.color = '#000';
                messageElement.style.border = '2px solid #b8941f';
                break;
        }
        
        messageElement.textContent = message;
        document.body.appendChild(messageElement);
        
        setTimeout(() => {
            messageElement.style.opacity = '1';
            messageElement.style.transform = 'translateX(-50%) translateY(0)';
        }, 10);
        
        setTimeout(() => {
            messageElement.style.opacity = '0';
            messageElement.style.transform = 'translateX(-50%) translateY(-20px)';
            setTimeout(() => {
                if (messageElement.parentNode) {
                    messageElement.remove();
                }
            }, 300);
        }, 3000);
    }
    
    // Add event listeners for map point edit modal when page loads
    window.addEventListener('load', function() {
        // Close modal when clicking outside
        const modal = document.getElementById('map-point-edit-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeMapPointEditModal();
                }
            });
        }
    });
</script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>

