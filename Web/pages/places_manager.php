<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file

// Check if user is admin
if (!isset($_SESSION['user']) || !isset($user_roles) || !in_array('admin', $user_roles)) {
    header('Location: login.php');
    exit;
}

require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>

<div id="mainText">
    <div class="map-admin-container">
        <h1 class="map-admin-title">📁 Places Folder Management</h1>
        <p style="color: #f4cf47; margin-bottom: 20px;">
            Manage all place folders and their link status with interest points. 
            Green icons indicate folders linked to active points, gray icons indicate orphaned folders.
        </p>
        
        <div id="folders-container" style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #d4af37;">📂 All Place Folders</h3>
                <button class="map-admin-button" onclick="refreshFoldersList()">🔄 Refresh</button>
            </div>
            
            <div id="folders-list" style="background: rgba(0, 0, 0, 0.3); border-radius: 8px; padding: 20px;">
                <p style="text-align: center; color: #ccc;">Loading folders...</p>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: rgba(212, 175, 55, 0.1); border-radius: 8px; border: 1px solid rgba(212, 175, 55, 0.3);">
            <h4 style="color: #d4af37; margin-bottom: 15px;">📋 Legend</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: #00aa00; font-size: 18px;">🟢</span>
                    <span style="color: #f4cf47;">Linked to active interest point</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: #888; font-size: 18px;">⚫</span>
                    <span style="color: #f4cf47;">Orphaned folder (no linked point)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: #ff6666; font-size: 18px;">🖼️</span>
                    <span style="color: #f4cf47;">Contains images</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: #888; font-size: 18px;">📁</span>
                    <span style="color: #f4cf47;">Empty folder</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Folder Details Modal -->
<div id="folder-modal" class="point-edit-modal" style="display: none;">
    <div class="point-edit-modal-content">
        <div class="point-edit-modal-header">
            <h2 id="modal-title">📁 Folder Details</h2>
            <button type="button" class="point-edit-modal-close" onclick="closeFolderModal()">&times;</button>
        </div>
        <div class="point-edit-modal-body">
            <div id="modal-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
        <div class="point-edit-modal-footer">
            <button type="button" class="point-edit-button delete" id="delete-folder-btn" onclick="deleteFolderConfirm()" style="display: none;">
                🗑️ Delete Folder
            </button>
            <button type="button" class="point-edit-button cancel" onclick="closeFolderModal()">❌ Close</button>
        </div>
    </div>
</div>

<script>
    let allFolders = [];
    let allPoints = [];
    let currentFolderSlug = null;
    
    // Load all folders and points
    function loadAllData() {
        Promise.all([
            loadAllFolders(),
            loadAllPoints()
        ]).then(() => {
            displayFoldersList();
        });
    }
    
    // Load all folders from server
    function loadAllFolders() {
        return fetch('./scriptes/folder_manager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'list_all_folders'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allFolders = data.folders || [];
                console.log(`Loaded ${allFolders.length} folders`);
            } else {
                console.error('Failed to load folders:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading folders:', error);
        });
    }
    
    // Load all points from database
    function loadAllPoints() {
        return fetch('./scriptes/map_save_points.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'loadPoints',
                map_id: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.points) {
                allPoints = data.points || [];
                console.log(`Loaded ${allPoints.length} points`);
            } else {
                console.log('No points found');
                allPoints = [];
            }
        })
        .catch(error => {
            console.error('Error loading points:', error);
            allPoints = [];
        });
    }
    
    // Create slug from point name (same logic as backend)
    function createSlug(name) {
        return name.toLowerCase()
                  .trim()
                  .replace(/[^a-z0-9\-]/g, '-')
                  .replace(/-+/g, '-')
                  .replace(/^-|-$/g, '');
    }
    
    // Check if folder is linked to a point
    function isFolderLinked(folderSlug) {
        return allPoints.some(point => createSlug(point.name_IP) === folderSlug);
    }
    
    // Get linked point for folder
    function getLinkedPoint(folderSlug) {
        return allPoints.find(point => createSlug(point.name_IP) === folderSlug);
    }
    
    // Display folders list
    function displayFoldersList() {
        const container = document.getElementById('folders-list');
        
        if (allFolders.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #ccc;">No place folders found. Create your first interest point to generate folders automatically.</p>';
            return;
        }
        
        let html = '<div style="display: grid; gap: 15px;">';
        
        allFolders.forEach(folder => {
            const isLinked = isFolderLinked(folder.slug);
            const linkedPoint = getLinkedPoint(folder.slug);
            const statusIcon = isLinked ? '🟢' : '⚫';
            const imageIcon = folder.has_images ? '🖼️' : '📁';
            
            html += `
                <div class="folder-item" style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 15px;
                    background: rgba(212, 175, 55, 0.1);
                    border-radius: 8px;
                    border: 1px solid rgba(212, 175, 55, 0.3);
                ">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 18px;">${statusIcon}</span>
                        <span style="font-size: 16px;">${imageIcon}</span>
                        <div>
                            <h4 style="margin: 0; color: #f4cf47;">${folder.name}</h4>
                            <p style="margin: 5px 0 0 0; color: #ccc; font-size: 12px;">
                                Slug: ${folder.slug}
                                ${isLinked ? `<br>Linked to: ${linkedPoint.name_IP}` : '<br>No linked point'}
                            </p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="viewFolderDetails('${folder.slug}')" 
                                style="background: #4CAF50; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                            📂 View
                        </button>
                        ${!isLinked ? `
                        <button onclick="deleteFolderPrompt('${folder.slug}')" 
                                style="background: #ff4444; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                            🗑️ Delete
                        </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        // Add summary
        const linkedCount = allFolders.filter(f => isFolderLinked(f.slug)).length;
        const orphanedCount = allFolders.length - linkedCount;
        
        html = `
            <div style="margin-bottom: 20px; padding: 15px; background: rgba(0, 0, 0, 0.5); border-radius: 8px;">
                <h4 style="color: #d4af37; margin: 0 0 10px 0;">📊 Summary</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="text-align: center;">
                        <span style="color: #00aa00; font-size: 24px; font-weight: bold;">${linkedCount}</span>
                        <p style="margin: 5px 0 0 0; color: #f4cf47;">Linked Folders</p>
                    </div>
                    <div style="text-align: center;">
                        <span style="color: #ff6666; font-size: 24px; font-weight: bold;">${orphanedCount}</span>
                        <p style="margin: 5px 0 0 0; color: #f4cf47;">Orphaned Folders</p>
                    </div>
                    <div style="text-align: center;">
                        <span style="color: #d4af37; font-size: 24px; font-weight: bold;">${allFolders.length}</span>
                        <p style="margin: 5px 0 0 0; color: #f4cf47;">Total Folders</p>
                    </div>
                </div>
            </div>
        ` + html;
        
        container.innerHTML = html;
    }
    
    // View folder details
    function viewFolderDetails(slug) {
        const folder = allFolders.find(f => f.slug === slug);
        const linkedPoint = getLinkedPoint(slug);
        
        if (!folder) return;
        
        currentFolderSlug = slug;
        
        const modal = document.getElementById('folder-modal');
        const title = document.getElementById('modal-title');
        const content = document.getElementById('modal-content');
        
        title.textContent = `📁 ${folder.name}`;
        
        let html = `
            <div style="margin-bottom: 20px;">
                <h4 style="color: #d4af37;">Folder Information</h4>
                <p><strong>Name:</strong> ${folder.name}</p>
                <p><strong>Slug:</strong> ${folder.slug}</p>
                <p><strong>Path:</strong> images/places/${folder.slug}/</p>
                <p><strong>Has Images:</strong> ${folder.has_images ? 'Yes' : 'No'}</p>
                <p><strong>Status:</strong> ${linkedPoint ? `🟢 Linked to "${linkedPoint.name_IP}"` : '⚫ Orphaned'}</p>
            </div>
        `;
        
        if (linkedPoint) {
            html += `
                <div style="margin-bottom: 20px; padding: 15px; background: rgba(0, 170, 0, 0.1); border-radius: 8px;">
                    <h4 style="color: #00aa00;">🔗 Linked Interest Point</h4>
                    <p><strong>Point ID:</strong> ${linkedPoint.id_IP}</p>
                    <p><strong>Name:</strong> ${linkedPoint.name_IP}</p>
                    <p><strong>Type:</strong> ${linkedPoint.type_IP}</p>
                    <p><strong>Description:</strong> ${linkedPoint.description_IP}</p>
                </div>
            `;
        } else {
            html += `
                <div style="margin-bottom: 20px; padding: 15px; background: rgba(170, 170, 170, 0.1); border-radius: 8px;">
                    <h4 style="color: #aa0000;">⚫ Orphaned Folder</h4>
                    <p>This folder is not linked to any active interest point. It may have been created for a point that was later deleted.</p>
                </div>
            `;
        }
        
        content.innerHTML = html;
        
        // Show delete button only for orphaned folders
        const deleteBtn = document.getElementById('delete-folder-btn');
        deleteBtn.style.display = linkedPoint ? 'none' : 'inline-block';
        
        modal.style.display = 'block';
    }
    
    // Close folder modal
    function closeFolderModal() {
        document.getElementById('folder-modal').style.display = 'none';
        currentFolderSlug = null;
    }
    
    // Delete folder prompt
    function deleteFolderPrompt(slug) {
        if (confirm(`Are you sure you want to delete the folder "${slug}" and all its contents? This action cannot be undone.`)) {
            deleteFolderConfirm();
        }
    }
    
    // Delete folder confirmation
    function deleteFolderConfirm() {
        if (!currentFolderSlug) return;
        
        if (confirm(`FINAL WARNING: Delete folder "${currentFolderSlug}" and ALL its contents permanently?`)) {
            fetch('./scriptes/folder_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_place_folder',
                    slug: currentFolderSlug
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTemporaryMessage('✅ Folder deleted successfully!', 'success');
                    closeFolderModal();
                    refreshFoldersList();
                } else {
                    showTemporaryMessage('❌ Error deleting folder: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showTemporaryMessage('❌ Connection error while deleting folder', 'error');
            });
        }
    }
    
    // Refresh folders list
    function refreshFoldersList() {
        document.getElementById('folders-list').innerHTML = '<p style="text-align: center; color: #ccc;">Loading folders...</p>';
        loadAllData();
    }
    
    // Show temporary message (reuse from map_modif.php)
    function showTemporaryMessage(message, type = 'info') {
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
    
    // Close modal when clicking outside
    document.getElementById('folder-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeFolderModal();
        }
    });
    
    // Load data on page load
    window.addEventListener('load', function() {
        loadAllData();
    });
</script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>
