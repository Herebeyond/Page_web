<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file

// Get place ID from URL
$placeId = $_GET['id'] ?? null;

if (!$placeId || !is_numeric($placeId)) {
    header('Location: map_view.php');
    exit;
}

// Load place details from database
try {
    require_once '../login/db.php';
    
    $stmt = $pdo->prepare("
        SELECT ip.*, ipt.name_IPT as type_name, ipt.color_IPT as type_color 
        FROM interest_points ip 
        LEFT JOIN IP_types ipt ON ip.type_IP = ipt.id_IPT 
        WHERE ip.id_IP = ?
    ");
    $stmt->execute([$placeId]);
    $place = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$place) {
        header('Location: map_view.php');
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
    header('Location: map_view.php');
    exit;
}

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
            <button type="button" onclick="closeEditModal('title')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">❌ Cancel</button>
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
            <button type="button" onclick="closeEditModal('description')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">❌ Cancel</button>
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
            <button type="button" onclick="closeEditModal('other-names')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">❌ Cancel</button>
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
            <button type="button" onclick="closeEditModal('type')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">❌ Cancel</button>
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
            <button type="button" onclick="closeEditModal('image-upload')" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">❌ Close</button>
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
    
    <?php if (isset($_SESSION['user']) && in_array('admin', $user_roles)): ?>
    // Admin editing functions
    
    // Load available types
    async function loadAvailableTypes() {
        try {
            const response = await fetch('./scriptes/map_save_points.php', {
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
    
    function closeEditModal(type) {
        let modalId;
        if (type === 'image-upload') {
            modalId = 'image-upload-modal';
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
    
    function loadManageGallery() {
        // This would load a management interface for existing gallery images
        document.getElementById('manage-gallery-list').innerHTML = 'Gallery management interface - to be implemented';
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
</script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>
