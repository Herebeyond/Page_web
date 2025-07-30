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
                        <li>Click on the map to add a point of interest</li>
                        <li>Hover over points to see their information</li>
                        <li>Double-click points to delete them</li>
                        <li>Press Escape to deactivate add mode</li>
                        <li><strong>Important:</strong> Use "Save to Database" to make changes permanent and visible to all users</li>
                    </ul>
                </div>
                <button class="tooltip-close" onclick="hideAdminHelp()">&times;</button>
            </div>
        </div>
        
        <div class="map-admin-container">
            <h1 class="map-admin-title">🔧 Administration - Interactive Map of Forgotten Worlds</h1>
            
            <div id="map-admin-controls">
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
                    <input type="text" id="poi-type" placeholder="Ex: City, Dungeon, Temple">
                </div>
                <button class="map-admin-button" onclick="toggleAddMode()">Add Mode: <span id="mode-status">Inactive</span></button>
                <button class="map-admin-button" onclick="saveAllPoints()">Save to Database</button>
                <button class="map-admin-button" onclick="loadPointsFromDB()">Load from Database</button>
                <button class="map-admin-button" onclick="clearAllPoints()">Clear all points (Local)</button>
            </div>
            
            <div id="interactive-map-container">
                <img id="interactive-map-image" src="../images/map/map_monde.png" alt="World Map">
                <div id="interactive-map-overlay"></div>
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
        
        // Add point of interest
        mapOverlay.addEventListener('click', function(e) {
            if (!addMode) return;
            
            const rect = mapOverlay.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Convert pixel coordinates to percentages
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            const name = document.getElementById('poi-name').value || `Point ${pointCounter + 1}`;
            const description = document.getElementById('poi-description').value || 'No description';
            const type = document.getElementById('poi-type').value || 'Location';
            
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
            document.getElementById('poi-type').value = '';
        });
        
        // Create point element
        function createPointElement(point) {
            const pointElement = document.createElement('div');
            pointElement.className = 'map-point-of-interest';
            pointElement.style.left = point.x + '%';
            pointElement.style.top = point.y + '%';
            pointElement.dataset.pointId = point.id;
            
            // Create tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'map-poi-tooltip';
            tooltip.innerHTML = `
                <strong>${point.name}</strong><br>
                Type: ${point.type}<br>
                ${point.description}
            `;
            
            // Add hover events
            pointElement.addEventListener('mouseenter', function() {
                tooltip.classList.add('show');
            });
            
            pointElement.addEventListener('mouseleave', function() {
                tooltip.classList.remove('show');
            });
            
            // Add click to remove
            pointElement.addEventListener('dblclick', function() {
                if (confirm(`Delete the point "${point.name}"?`)) {
                    removePoint(point.id);
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
                        alert('Error during database deletion: ' + data.message);
                    }
                });
            }
            
            // Remove from local array and DOM
            points = points.filter(p => p.id !== pointId);
            const pointElement = document.querySelector(`[data-point-id="${pointId}"]`);
            if (pointElement) {
                pointElement.remove();
            }
        }
        
        // Clear all points
        function clearAllPoints() {
            if (points.length === 0) return;
            
            if (confirm('Are you sure you want to delete all points?')) {
                points = [];
                pointCounter = 0;
                const pointElements = document.querySelectorAll('.map-point-of-interest');
                pointElements.forEach(element => element.remove());
            }
        }
        
        // Save all points to database
        function saveAllPoints() {
            if (points.length === 0) {
                alert('No points to save');
                return;
            }
            
            if (confirm('Save all points to database?')) {
                fetch('./scriptes/map_save_points.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'save_points',
                        points: points,
                        map_id: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Points saved successfully!');
                        loadPointsFromDB();
                    } else {
                        alert('Error during save: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Connection error during save');
                });
            }
        }
        
        // Load points from database
        function loadPointsFromDB() {
            fetch('./scriptes/map_save_points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'load_points',
                    map_id: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear current points
                    clearAllPoints();
                    
                    // Load points from database
                    points = data.points || [];
                    points.forEach(point => {
                        createPointElement(point);
                        if (point.id >= pointCounter) {
                            pointCounter = point.id + 1;
                        }
                    });
                    
                    console.log(`${points.length} points loaded from database`);
                } else {
                    console.log('No points in database: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Connection error:', error);
                console.log('Unable to load points from database. Page working in local mode.');
            });
        }
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (addMode) {
                    toggleAddMode();
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
        });
        
        function hideAdminHelp() {
            adminHelpContent.classList.remove('show');
        }
        
        // Load existing points on page load
        window.addEventListener('load', function() {
            loadPointsFromDB();
            // Initial positioning check after page load
            handleHelpContainerPosition();
        });
    </script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>
