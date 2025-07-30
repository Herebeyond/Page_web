<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>

    
    <div id="mainText">
        <!-- Admin Help Icon with Tooltip -->
        <div class="map-help-container" id="admin-help-container">
            <div class="map-help-icon admin-help-icon" id="admin-help-trigger" title="Click for instructions">
                <span>?</span>
            </div>
            <div class="map-help-tooltip admin-help-tooltip" id="admin-help-content">
                <div class="notification-content">
                    <h3>🔧 Administrator Interactive Map</h3>
                    <p><strong>Instructions:</strong></p>
                    <ul>
                        <li>Click "Add Mode" to activate point addition</li>
                        <li>Select a type from the dropdown (manage types on the right)</li>
                        <li>Click on the map to add a point of interest</li>
                        <li>Hover over points to see their information</li>
                        <li><strong>Click once on any point to edit its details</strong></li>
                        <li><strong>Point colors automatically match their type</strong></li>
                        <li><strong>Tip:</strong> Deactivate "Add Mode" before clicking points to edit them</li>
                        <li>Press Escape to deactivate add mode or close edit modal</li>
                        <li><strong>Type Management:</strong> Add/delete point types and click color circles to customize their colors</li>
                        <li><strong>Important:</strong> Use "Save to Database" to make changes permanent and visible to all users</li>
                    </ul>
                </div>
                <button class="tooltip-close" onclick="hideAdminHelp()">&times;</button>
            </div>
        </div>
        
        <div class="map-admin-container">
            <h1 class="map-admin-title">🔧 Administration - Interactive Map of Forgotten Worlds</h1>
            
            <div id="map-admin-controls">
                <div style="display: flex; gap: 30px;">
                    <!-- Point Management Section -->
                    <div style="flex: 1;">
                        <h3>Add Point of Interest</h3>
                        <div class="map-control-group">
                            <label for="poi-name">Location Name:</label>
                            <input type="text" id="poi-name" placeholder="Ex: Elven Citadel">
                        </div>
                        <div class="map-control-group">
                            <label for="poi-description">Description:</label>
                            <input type="text" id="poi-description" placeholder="Ex: Ancient elven fortress">
                        </div>
                        <div class="map-control-group">
                            <label for="poi-type">Type:</label>
                            <select id="poi-type">
                                <option value="">Select a type...</option>
                            </select>
                        </div>
                        <button class="map-admin-button" onclick="toggleAddMode()">Add Mode: <span id="mode-status">Inactive</span></button>
                        <button class="map-admin-button" onclick="saveAllPoints()">Save to Database</button>
                        <button class="map-admin-button" onclick="clearAllPoints()">Clear all points (Local)</button>
                    </div>
                    
                    <!-- Type Management Section -->
                    <div style="flex: 0 0 300px;">
                        <h3>Manage Point Types</h3>
                        <div class="map-control-group">
                            <label for="new-type-name">New Type Name:</label>
                            <input type="text" id="new-type-name" placeholder="Ex: Ancient Ruins">
                        </div>
                        <div class="map-control-group">
                            <label for="new-type-color">Color (hex):</label>
                            <input type="color" id="new-type-color" value="#ff4444">
                        </div>
                        <button class="map-admin-button" onclick="addNewType()">Add Type</button>
                        
                        <div style="margin-top: 15px;">
                            <h4 style="color: #d4af37; margin-bottom: 10px;">Existing Types:</h4>
                            <div id="types-list" style="max-height: 200px; overflow-y: auto; background: rgba(0, 0, 0, 0.3); border-radius: 5px; padding: 10px;">
                                <!-- Types will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="interactive-map-container">
                <img id="interactive-map-image" src="../images/map/map_monde.png" alt="World Map">
                <div id="interactive-map-overlay"></div>
            </div>
        </div>
    </div>

    <!-- Point Edit Modal -->
    <div id="point-edit-modal" class="point-edit-modal">
        <div class="point-edit-modal-content">
            <div class="point-edit-modal-header">
                <h2>📍 Edit Point of Interest</h2>
                <button type="button" class="point-edit-modal-close" onclick="closePointEditModal()">&times;</button>
            </div>
            <div class="point-edit-modal-body">
                <div class="point-edit-form-group">
                    <label for="edit-poi-name">Location Name:</label>
                    <input type="text" id="edit-poi-name" placeholder="Ex: Elven Citadel">
                </div>
                <div class="point-edit-form-group">
                    <label for="edit-poi-description">Description:</label>
                    <textarea id="edit-poi-description" placeholder="Ex: Ancient elven fortress built on the highest peak..."></textarea>
                </div>
                <div class="point-edit-form-group">
                    <label for="edit-poi-type">Type:</label>
                    <select id="edit-poi-type">
                        <option value="">Select a type...</option>
                    </select>
                </div>
            </div>
            <div class="point-edit-modal-footer">
                <button type="button" class="point-edit-button save" onclick="savePointEdit()">💾 Save Changes</button>
                <button type="button" class="point-edit-button delete" onclick="deletePointFromModal()">🗑️ Delete Point</button>
                <button type="button" class="point-edit-button cancel" onclick="closePointEditModal()">❌ Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let addMode = false;
        let points = [];
        let pointCounter = 0;
        
        const mapOverlay = document.getElementById('interactive-map-overlay');
        const mapContainer = document.getElementById('interactive-map-container');
        
        // Toggle add mode
        function toggleAddMode() {
            addMode = !addMode;
            const statusSpan = document.getElementById('mode-status');
            const overlay = document.getElementById('interactive-map-overlay');
            
            if (addMode) {
                statusSpan.textContent = 'Active';
                statusSpan.style.color = '#00aa00';
                overlay.style.backgroundColor = 'rgba(0, 170, 0, 0.1)';
            } else {
                statusSpan.textContent = 'Inactive';
                statusSpan.style.color = '#aa0000';
                overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.1)';
            }
        }
        
        // Initialize status color on page load
        function initializeStatusColor() {
            const statusSpan = document.getElementById('mode-status');
            statusSpan.style.color = '#aa0000'; // Red for inactive
        }
        
        // Add point of interest
        mapOverlay.addEventListener('click', function(e) {
            if (!addMode) return;
            
            const rect = mapOverlay.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Convert pixel coordinates to percentages
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            const name = document.getElementById('poi-name').value.trim();
            const description = document.getElementById('poi-description').value.trim() || 'No description';
            const typeSelect = document.getElementById('poi-type');
            const type = typeSelect.value.trim();
            
            // Check if name and type are provided
            if (!name || !type) {
                showTemporaryMessage('⚠️ Location Name and Type are required!', 'error');
                return;
            }
            
            const point = {
                id: pointCounter++,
                x: xPercent,
                y: yPercent,
                name: name,
                description: description,
                type: type
            };
            
            points.push(point);
            createPointElement(point);
            
            // Clear inputs
            document.getElementById('poi-name').value = '';
            document.getElementById('poi-description').value = '';
            document.getElementById('poi-type').selectedIndex = 0;
            
            showTemporaryMessage('✅ Point added successfully!', 'success');
        });
        
        // Type Management Functions
        let pointTypes = [];
        
        // Get color for point type
        function getColorForType(typeName) {
            const type = pointTypes.find(t => t.name_IPT === typeName);
            const color = type ? type.color_IPT || '#ff4444' : '#ff4444';
            return color;
        }
        
        // Load point types from database
        function loadPointTypes() {
            return fetch('./scriptes/map_save_points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'load_types'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    pointTypes = data.types || [];
                    updateTypeDropdown();
                    updateTypesList();
                    return true;
                } else {
                    console.log('No types found: ' + data.message);
                    return false;
                }
            })
            .catch(error => {
                console.error('Error loading types:', error);
                showTemporaryMessage('❌ Unable to load point types', 'error');
                return false;
            });
        }
        
        // Generate unique ID for points
        function generateUniqueId() {
            return Date.now() + Math.random().toString(36).substr(2, 9);
        }
        
        // Draw all points on the map
        function drawAllPoints() {
            // Clear existing points from DOM
            const existingPoints = document.querySelectorAll('.map-point-of-interest');
            existingPoints.forEach(point => point.remove());
            
            // Redraw all points
            points.forEach(point => {
                createPointElement(point);
            });
        }
        
        // Load existing points from database
        function loadExistingPoints() {
            fetch('./scriptes/map_save_points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'loadPoints',
                    map_id: 1
                })
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success && data.points) {
                    // Clear existing points
                    points = [];
                    
                    // Load points from database
                    data.points.forEach(dbPoint => {
                        const point = {
                            id: generateUniqueId(),
                            database_id: dbPoint.id_IP,
                            name: dbPoint.name_IP,
                            description: dbPoint.description_IP,
                            type: dbPoint.type_IP,
                            x: parseFloat(dbPoint.x_IP),
                            y: parseFloat(dbPoint.y_IP)
                        };
                        points.push(point);
                    });
                    
                    // Redraw all points
                    drawAllPoints();
                    
                    // Force refresh colors after a small delay to ensure types are available
                    setTimeout(() => {
                        refreshAllPointColors();
                    }, 100);
                    
                    showTemporaryMessage('✅ Loaded ' + data.points.length + ' points from database', 'success');
                } else {
                    showTemporaryMessage('ℹ️ No existing points found', 'info');
                }
            })
            .catch(error => {
                console.error('Error loading points:', error);
                showTemporaryMessage('❌ Unable to load existing points', 'error');
            });
        }
        
        // Update type dropdown
        function updateTypeDropdown() {
            const typeSelect = document.getElementById('poi-type');
            typeSelect.innerHTML = '<option value="">Select a type...</option>';
            
            pointTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.name_IPT;
                option.textContent = type.name_IPT;
                typeSelect.appendChild(option);
            });
            
            // Also update the edit modal dropdown if it exists
            updateEditTypeDropdown();
        }
        
        // Update types list display
        function updateTypesList() {
            const typesList = document.getElementById('types-list');
            typesList.innerHTML = '';
            
            if (pointTypes.length === 0) {
                typesList.innerHTML = '<p style="color: #ccc; text-align: center; margin: 0;">No types available</p>';
                return;
            }
            
            pointTypes.forEach(type => {
                const typeElement = document.createElement('div');
                typeElement.style.cssText = `
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 8px;
                    margin: 5px 0;
                    background: rgba(212, 175, 55, 0.1);
                    border-radius: 4px;
                    border: 1px solid rgba(212, 175, 55, 0.3);
                `;
                
                // Create color picker input (hidden)
                const colorPickerId = `color-picker-${type.id_IPT}`;
                const colorControlsId = `color-controls-${type.id_IPT}`;
                const colorIndicator = `
                    <div style="position: relative;">
                        <div onclick="showColorControls(${type.id_IPT}, '${type.color_IPT || '#ff4444'}')" 
                             class="type-color-indicator"
                             style="background-color: ${type.color_IPT || '#ff4444'};"
                             title="Click to change color"></div>
                        <div id="${colorControlsId}" style="display: none; position: absolute; top: 25px; left: 0; z-index: 1000; background: rgba(0,0,0,0.9); padding: 10px; border-radius: 5px; border: 1px solid #d4af37;">
                            <input type="color" id="${colorPickerId}" value="${type.color_IPT || '#ff4444'}" 
                                   style="margin-bottom: 8px; width: 60px; height: 30px;">
                            <div style="display: flex; gap: 5px;">
                                <button onclick="confirmColorChange(${type.id_IPT})" 
                                        style="background: #00aa00; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 11px;">
                                    OK
                                </button>
                                <button onclick="cancelColorChange(${type.id_IPT})" 
                                        style="background: #aa0000; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 11px;">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                typeElement.innerHTML = `
                    <div style="display: flex; align-items: center;">
                        ${colorIndicator}
                        <span style="color: #f4cf47; font-weight: bold;">${type.name_IPT}</span>
                    </div>
                    <div>
                        <button onclick="deleteType(${type.id_IPT}, '${type.name_IPT}')" 
                                style="background: #ff4444; color: white; border: none; 
                                       padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 12px;">
                            Delete
                        </button>
                    </div>
                `;
                
                typesList.appendChild(typeElement);
            });
        }
        
        // Add new type
        function addNewType() {
            const newTypeName = document.getElementById('new-type-name').value.trim();
            const newTypeColor = document.getElementById('new-type-color').value;
            
            if (!newTypeName) {
                showTemporaryMessage('⚠️ Type name is required!', 'error');
                return;
            }
            
            // Check if type already exists
            if (pointTypes.some(type => type.name_IPT.toLowerCase() === newTypeName.toLowerCase())) {
                showTemporaryMessage('⚠️ This type already exists!', 'error');
                return;
            }
            
            fetch('./scriptes/map_save_points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add_type',
                    type_name: newTypeName,
                    type_color: newTypeColor
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTemporaryMessage('✅ Type added successfully!', 'success');
                    document.getElementById('new-type-name').value = '';
                    document.getElementById('new-type-color').value = '#ff4444';
                    loadPointTypes();
                } else {
                    showTemporaryMessage('❌ Error adding type: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showTemporaryMessage('❌ Connection error while adding type', 'error');
            });
        }
        
        // Delete type
        function deleteType(typeId, typeName) {
            if (!confirm(`Delete the type "${typeName}"? This action cannot be undone.`)) {
                return;
            }
            
            fetch('./scriptes/map_save_points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_type',
                    type_id: typeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTemporaryMessage('✅ Type deleted successfully!', 'success');
                    loadPointTypes();
                    // Refresh all points to update colors
                    refreshAllPointColors();
                } else {
                    showTemporaryMessage('❌ Error deleting type: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showTemporaryMessage('❌ Connection error while deleting type', 'error');
            });
        }
        
        // Update type color from color picker
        function updateTypeColorFromPicker(typeId, newColor) {
            fetch('./scriptes/map_save_points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_type_color',
                    type_id: typeId,
                    type_color: newColor
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTemporaryMessage('✅ Type color updated successfully!', 'success');
                    loadPointTypes();
                    // Refresh all points to update colors
                    refreshAllPointColors();
                } else {
                    showTemporaryMessage('❌ Error updating type color: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showTemporaryMessage('❌ Connection error while updating type color', 'error');
            });
        }
        
        // Show color controls with OK/Cancel buttons
        function showColorControls(typeId, currentColor) {
            // Hide all other color controls first
            document.querySelectorAll('[id^="color-controls-"]').forEach(control => {
                control.style.display = 'none';
            });
            
            // Show the controls for this type
            const controlsElement = document.getElementById(`color-controls-${typeId}`);
            if (controlsElement) {
                controlsElement.style.display = 'block';
                // Set the color picker to current color
                const colorPicker = document.getElementById(`color-picker-${typeId}`);
                if (colorPicker) {
                    colorPicker.value = currentColor;
                }
            }
        }
        
        // Confirm color change
        function confirmColorChange(typeId) {
            const colorPicker = document.getElementById(`color-picker-${typeId}`);
            if (colorPicker) {
                const newColor = colorPicker.value;
                updateTypeColorFromPicker(typeId, newColor);
            }
            
            // Hide the controls
            const controlsElement = document.getElementById(`color-controls-${typeId}`);
            if (controlsElement) {
                controlsElement.style.display = 'none';
            }
        }
        
        // Cancel color change
        function cancelColorChange(typeId) {
            // Just hide the controls without saving
            const controlsElement = document.getElementById(`color-controls-${typeId}`);
            if (controlsElement) {
                controlsElement.style.display = 'none';
            }
        }
        
        // Refresh all point colors on the map
        function refreshAllPointColors() {
            points.forEach(point => {
                const pointElement = document.querySelector(`[data-point-id="${point.id}"]`);
                if (pointElement) {
                    const newColor = getColorForType(point.type);
                    pointElement.style.backgroundColor = newColor;
                }
            });
        }
        
        // Create point element
        function createPointElement(point) {
            const pointElement = document.createElement('div');
            pointElement.className = 'map-point-of-interest';
            pointElement.style.left = point.x + '%';
            pointElement.style.top = point.y + '%';
            pointElement.dataset.pointId = point.id;
            
            // Set point color based on type
            const pointColor = getColorForType(point.type);
            pointElement.style.backgroundColor = pointColor;
            pointElement.style.borderColor = '#ffffff';
            
            // Create tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'map-poi-tooltip';
            tooltip.innerHTML = `
                <strong>${point.name}</strong><br>
                Type: ${point.type}<br>
                <p style="max-width: 200px; margin: 5px 0 0 0; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.3;">${point.description}</p>
            `;
            
            // Add hover events
            pointElement.addEventListener('mouseenter', function() {
                tooltip.classList.add('show');
            });
            
            pointElement.addEventListener('mouseleave', function() {
                tooltip.classList.remove('show');
            });
            
            // Add click to edit (single click only)
            pointElement.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent map click when clicking on point
                if (!addMode) { // Only allow editing when not in add mode
                    openPointEditModal(point);
                }
            });
            
            pointElement.appendChild(tooltip);
            mapOverlay.appendChild(pointElement);
        }
        
        // Remove point
        function removePoint(pointId) {
            const point = points.find(p => p.id === pointId);
            if (!point) return;
            
            // If point has database_id, remove from database too
            if (point.database_id) {
                fetch('./scriptes/map_save_points.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_point',
                        database_id: point.database_id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        showTemporaryMessage('❌ Error during database deletion: ' + data.message, 'error');
                    } else {
                        showTemporaryMessage('✅ Point deleted from database', 'success');
                    }
                });
            } else {
                showTemporaryMessage('✅ Local point deleted', 'success');
            }
            
            // Remove from local array and DOM
            points = points.filter(p => p.id !== pointId);
            const pointElement = document.querySelector(`[data-point-id="${pointId}"]`);
            if (pointElement) {
                pointElement.remove();
            }
        }
        
        // Clear all points (only local points, not database points)
        function clearAllPoints() {
            const localPoints = points.filter(p => !p.database_id);
            
            if (localPoints.length === 0) {
                showTemporaryMessage('ℹ️ No local points to clear', 'info');
                return;
            }
            
            if (confirm(`Are you sure you want to delete ${localPoints.length} local point(s)? Points saved to database will remain.`)) {
                // Remove local points from array and DOM
                localPoints.forEach(point => {
                    const pointElement = document.querySelector(`[data-point-id="${point.id}"]`);
                    if (pointElement) {
                        pointElement.remove();
                    }
                });
                
                // Keep only database points
                points = points.filter(p => p.database_id);
                
                showTemporaryMessage('✅ Local points cleared successfully!', 'success');
            }
        }
        
        // Save all points to database
        function saveAllPoints() {
            if (points.length === 0) {
                showTemporaryMessage('ℹ️ No points to save', 'info');
                return;
            }
            
            if (confirm('Save all points to database?')) {
                fetch('./scriptes/map_save_points.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'savePoints',
                        points: points,
                        map_id: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update points with database IDs if provided
                        if (data.saved_points) {
                            data.saved_points.forEach(savedPoint => {
                                const localPoint = points.find(p => p.id === savedPoint.local_id);
                                if (localPoint) {
                                    localPoint.database_id = savedPoint.database_id;
                                }
                            });
                        }
                        showTemporaryMessage('✅ Points saved successfully!', 'success');
                    } else {
                        showTemporaryMessage('❌ Error during save: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showTemporaryMessage('❌ Connection error during save', 'error');
                });
            }
        }
        
        // Show temporary message function
        function showTemporaryMessage(message, type = 'info') {
            // Remove existing temporary message if any
            const existingMessage = document.getElementById('temp-message');
            if (existingMessage) {
                existingMessage.remove();
            }
            
            // Create message element
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
            
            // Set colors based on type
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
                case 'info':
                default:
                    messageElement.style.background = 'linear-gradient(135deg, #d4af37, #f4cf47)';
                    messageElement.style.color = '#000';
                    messageElement.style.border = '2px solid #b8941f';
                    break;
            }
            
            messageElement.textContent = message;
            document.body.appendChild(messageElement);
            
            // Animate in
            setTimeout(() => {
                messageElement.style.opacity = '1';
                messageElement.style.transform = 'translateX(-50%) translateY(0)';
            }, 10);
            
            // Remove after 3 seconds
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
        
        // Point Edit Modal Functions
        let currentEditingPoint = null;
        
        // Open point edit modal
        function openPointEditModal(point) {
            currentEditingPoint = point;
            
            // Populate the form with current point data
            document.getElementById('edit-poi-name').value = point.name || '';
            document.getElementById('edit-poi-description').value = point.description || '';
            
            // Update the type dropdown in modal with current types
            updateEditTypeDropdown();
            
            // Set the current type
            const editTypeSelect = document.getElementById('edit-poi-type');
            editTypeSelect.value = point.type || '';
            
            // Show the modal
            document.getElementById('point-edit-modal').style.display = 'block';
        }
        
        // Close point edit modal
        function closePointEditModal() {
            document.getElementById('point-edit-modal').style.display = 'none';
            currentEditingPoint = null;
        }
        
        // Update type dropdown in edit modal
        function updateEditTypeDropdown() {
            const editTypeSelect = document.getElementById('edit-poi-type');
            editTypeSelect.innerHTML = '<option value="">Select a type...</option>';
            
            pointTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.name_IPT;
                option.textContent = type.name_IPT;
                editTypeSelect.appendChild(option);
            });
        }
        
        // Save point edit
        function savePointEdit() {
            if (!currentEditingPoint) return;
            
            const newName = document.getElementById('edit-poi-name').value.trim();
            const newDescription = document.getElementById('edit-poi-description').value.trim();
            const newType = document.getElementById('edit-poi-type').value.trim();
            
            // Validation
            if (!newName || !newType) {
                showTemporaryMessage('⚠️ Name and Type are required!', 'error');
                return;
            }
            
            // Update point in local array
            const pointIndex = points.findIndex(p => p.id === currentEditingPoint.id);
            if (pointIndex !== -1) {
                points[pointIndex].name = newName;
                points[pointIndex].description = newDescription || 'No description';
                points[pointIndex].type = newType;
                
                // Update the point element on the map
                updatePointElement(points[pointIndex]);
                
                // If point has database_id, update in database
                if (points[pointIndex].database_id) {
                    updatePointInDatabase(points[pointIndex]);
                } else {
                    showTemporaryMessage('✅ Point updated locally. Use "Save to Database" to make it permanent.', 'success');
                }
                
                closePointEditModal();
            }
        }
        
        // Update point element on map
        function updatePointElement(point) {
            const pointElement = document.querySelector(`[data-point-id="${point.id}"]`);
            if (pointElement) {
                // Update color based on type
                const newColor = getColorForType(point.type);
                pointElement.style.backgroundColor = newColor;
                
                // Update tooltip
                const tooltip = pointElement.querySelector('.map-poi-tooltip');
                if (tooltip) {
                    tooltip.innerHTML = `
                        <strong>${point.name}</strong><br>
                        Type: ${point.type}<br>
                        <p style="max-width: 200px; margin: 5px 0 0 0; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.3;">${point.description}</p>
                    `;
                }
            }
        }
        
        // Update point in database
        function updatePointInDatabase(point) {
            fetch('./scriptes/map_save_points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_point',
                    database_id: point.database_id,
                    name: point.name,
                    description: point.description,
                    type: point.type,
                    x: point.x,
                    y: point.y
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTemporaryMessage('✅ Point updated in database successfully!', 'success');
                } else {
                    showTemporaryMessage('❌ Error updating point in database: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showTemporaryMessage('❌ Connection error while updating point', 'error');
            });
        }
        
        // Delete point from modal
        function deletePointFromModal() {
            if (!currentEditingPoint) return;
            
            if (confirm(`Delete the point "${currentEditingPoint.name}"? This action cannot be undone.`)) {
                removePoint(currentEditingPoint.id);
                closePointEditModal();
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('point-edit-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePointEditModal();
            }
        });
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (addMode) {
                    toggleAddMode();
                }
                // Close edit modal if open
                const modal = document.getElementById('point-edit-modal');
                if (modal.style.display === 'block') {
                    closePointEditModal();
                }
            }
        });
        
        // Close notification function
        function closeNotification(id) {
            const notification = document.getElementById(id);
            if (notification) {
                notification.style.display = 'none';
            }
        }
        
        // Admin help system functionality
        let adminHelpTimeout;
        const adminHelpTrigger = document.getElementById('admin-help-trigger');
        const adminHelpContent = document.getElementById('admin-help-content');
        
        // Handle help container positioning based on scroll
        function handleHelpContainerPosition() {
            const helpContainer = document.getElementById('admin-help-container');
            const mainText = document.getElementById('mainText');
            
            if (!helpContainer || !mainText) return;
            
            const mainTextRect = mainText.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // If mainText's top is below viewport (user scrolled past it)
            if (mainTextRect.top < 0) {
                helpContainer.className = 'map-help-container fixed';
            } else {
                helpContainer.className = 'map-help-container';
            }
        }
        
        // Add scroll event listener for help positioning
        window.addEventListener('scroll', handleHelpContainerPosition);
        
        // Show help on hover (with delay) or click
        adminHelpTrigger.addEventListener('mouseenter', function() {
            adminHelpTimeout = setTimeout(() => {
                adminHelpContent.classList.add('show');
            }, 800); // 800ms delay
        });
        
        adminHelpTrigger.addEventListener('mouseleave', function() {
            clearTimeout(adminHelpTimeout);
        });
        
        adminHelpTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            clearTimeout(adminHelpTimeout);
            adminHelpContent.classList.toggle('show');
        });
        
        // Hide help when clicking outside
        document.addEventListener('click', function(e) {
            if (!adminHelpContent.contains(e.target) && !adminHelpTrigger.contains(e.target)) {
                adminHelpContent.classList.remove('show');
            }
            
            // Also hide color controls when clicking outside
            if (!e.target.closest('[id^="color-controls-"]') && !e.target.classList.contains('type-color-indicator')) {
                document.querySelectorAll('[id^="color-controls-"]').forEach(control => {
                    control.style.display = 'none';
                });
            }
        });
        
        function hideAdminHelp() {
            adminHelpContent.classList.remove('show');
        }
        
        // Load existing points on page load
        window.addEventListener('load', function() {
            // Load point types first, then load existing points
            loadPointTypes().then(function(typesLoaded) {
                loadExistingPoints(); // Load existing points after types are loaded
            });
            
            // Initial positioning check after page load
            handleHelpContainerPosition();
            // Initialize status color
            initializeStatusColor();
        });
    </script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>
