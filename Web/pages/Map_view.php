<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>

    <div id="mainText">
        <!-- Help Icon with Tooltip -->
        <div class="map-help-container" id="map-help-container">
            <div class="map-help-icon" id="map-help-trigger" title="Click for help">
                <span>?</span>
            </div>
            <div class="map-help-tooltip" id="map-help-content">
                <div class="notification-content">
                    <h3>🗺️ Welcome to the Interactive Map of the Forgotten Worlds!</h3>
                    <p id="dynamic-welcome-text">Explore the vast lands and discover locations, cities, dungeons, and other important places in this mystical realm.</p>
                    <p><strong>How to explore:</strong> Hover over the red points to discover information about each location.</p>
                </div>
                <button class="tooltip-close" onclick="hideHelp()">&times;</button>
            </div>
        </div>
        
        <div class="map-view-container">
            <h1 class="map-view-title">🗺️ Interactive Map of the Forgotten Worlds</h1>
            <p class="map-view-description">
                Explore the vast lands of the Forgotten Worlds. Hover over the points of interest to discover locations, 
                cities, dungeons, and other important places in this mystical realm.
            </p>
            
            <!-- Map Selection Section -->
            <div style="margin-bottom: 30px; padding: 20px; background: rgba(34, 32, 136, 0.1); border-radius: 8px; border: 1px solid rgba(34, 32, 136, 0.3);">
                <h3 style="color: #222088; margin-bottom: 15px;">🗺️ Choose Your View</h3>
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label for="map-selector" style="color: #222088; white-space: nowrap; font-weight: bold;">Map Layer:</label>
                        <select id="map-selector" style="padding: 8px 12px; background: white; border: 2px solid #222088; border-radius: 4px; color: #222088; min-width: 200px;">
                            <option value="">Loading maps...</option>
                        </select>
                    </div>
                    <div id="map-info" style="color: #666; font-size: 14px;">
                        <!-- Map info will be displayed here -->
                    </div>
                </div>
            </div>
            
            <div id="interactive-map-container">
                <img id="interactive-map-image" src="../images/maps/map_world.png" alt="World Map">
                <div id="interactive-map-overlay"></div>
            </div>
            
            <div id="map-legend">
                <h3>🔍 How to explore:</h3>
                <ul>
                    <li>🖱️ <strong>Hover</strong> over red points to see location details</li>
                    <li>🏰 <strong>Cities:</strong> Major settlements and capitals</li>
                    <li>⚔️ <strong>Dungeons:</strong> Dangerous places full of treasures</li>
                    <li>🏛️ <strong>Temples:</strong> Sacred and mystical locations</li>
                    <li>📍 <strong>Locations:</strong> Other points of interest</li>
                </ul>
                
                <?php if (isset($_SESSION['user']) && isset($user_roles) && in_array('admin', $user_roles)): ?>
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #444;">
                    <p style="color: #888; font-size: 0.9em; margin-bottom: 10px;"><em>Administrator Tools:</em></p>
                    <a href="Map_modif.php" onfocus="this.style.outline='2px solid #d4af37'" onblur="this.style.outline=''" style="
                        background: #666; 
                        color: white; 
                        padding: 8px 16px; 
                        text-decoration: none; 
                        border-radius: 5px; 
                        font-size: 0.9em;
                        display: inline-block;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.background='#888'" onmouseout="this.style.background='#666'">
                        🔧 Edit Map Points
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let points = [];
        let pointTypes = [];
        
        // Multi-map system variables
        let availableMaps = [];
        let currentMapId = 1; // Default to surface map
        let currentMapData = null;
        
        const mapOverlay = document.getElementById('interactive-map-overlay');
        
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
                    return true;
                } else {
                    console.log('No types found: ' + data.message);
                    return false;
                }
            })
            .catch(error => {
                console.error('Error loading types:', error);
                return false;
            });
        }
        
        // Create point element (read-only version)
        function createPointElement(point) {
            // Check if element already exists
            const existingElement = document.querySelector(`[data-point-id="${point.id}"]`);
            if (existingElement) {
                existingElement.remove();
            }
            
            const pointElement = document.createElement('div');
            pointElement.className = 'map-point-of-interest map-point-readonly';
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
                <div id="tooltip-image-${point.id}" style="margin: 8px 0; text-align: center; min-height: 20px;">
                    <div style="color: #888; font-size: 12px;">Loading image...</div>
                </div>
                Type: ${point.type}<br>
                <p style="max-width: 200px; margin: 5px 0 0 0; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.3;">${point.description}</p>
            `;
            
            // Function to load main image with loading state management
            function loadMainImageForTooltip(pointData) {
                const imageContainer = document.getElementById(`tooltip-image-${pointData.id}`);
                if (!imageContainer) {
                    return; // Safety check
                }
                
                // Check if already loading or loaded
                if (imageContainer.dataset.loading === 'true' || imageContainer.dataset.loaded === 'true') {
                    return;
                }
                
                // Set loading flag
                imageContainer.dataset.loading = 'true';
                
                // Use server-side image checker to avoid console 404 errors
                fetch('./scriptes/image_checker.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'check_place_image',
                        name: pointData.name  // Send original name, let server generate slug
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const currentContainer = document.getElementById(`tooltip-image-${pointData.id}`);
                    if (!currentContainer) {
                        return;
                    }
                    
                    // Clear loading flag and set loaded flag
                    currentContainer.dataset.loading = 'false';
                    currentContainer.dataset.loaded = 'true';
                    
                    if (data.success && data.found) {
                        // Image exists, display it - try absolute path first
                        const imgPath = data.absolute_path || data.path;
                        const imgHTML = `
                            <img src="${imgPath}" 
                                 alt="Image of ${pointData.name}" 
                                 style="max-width: 180px; max-height: 120px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.3); object-fit: cover;"
                                 onload="const tooltip = this.closest('.map-poi-tooltip'); if (tooltip) { tooltip.style.display = 'none'; tooltip.offsetHeight; tooltip.style.display = ''; }"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="padding: 15px; background: rgba(0,0,0,0.1); border-radius: 4px; color: #666; font-size: 11px; border: 1px dashed #999; display: none;">
                                📷 Image failed to load
                            </div>
                        `;
                        currentContainer.innerHTML = imgHTML;
                        
                        // Alternative approach: completely regenerate tooltip content
                        const parentTooltip = currentContainer.closest('.map-poi-tooltip');
                        if (parentTooltip) {
                            parentTooltip.innerHTML = `
                                <strong>${pointData.name}</strong><br>
                                <div id="tooltip-image-${pointData.id}" style="margin: 8px 0; text-align: center; min-height: 20px;">
                                    ${imgHTML}
                                </div>
                                Type: ${pointData.type}<br>
                                <p style="max-width: 200px; margin: 5px 0 0 0; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; line-height: 1.3;">${pointData.description}</p>
                            `;
                        }
                    } else {
                        // No image found
                        currentContainer.innerHTML = `
                            <div style="padding: 15px; background: rgba(0,0,0,0.1); border-radius: 4px; color: #666; font-size: 11px; border: 1px dashed #999;">
                                📷 No image available
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    const currentContainer = document.getElementById(`tooltip-image-${pointData.id}`);
                    if (currentContainer) {
                        // Clear loading flag
                        currentContainer.dataset.loading = 'false';
                        currentContainer.dataset.loaded = 'true';
                        currentContainer.innerHTML = `
                            <div style="padding: 15px; background: rgba(0,0,0,0.1); border-radius: 4px; color: #666; font-size: 11px; border: 1px dashed #999;">
                                📷 Error loading image
                            </div>
                        `;
                    }
                });
            }
            
            // Function to position tooltip within viewport
            function positionTooltip() {
                const rect = pointElement.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const viewportWidth = window.innerWidth;
                
                // Reset positioning classes
                tooltip.classList.remove('tooltip-above', 'tooltip-below', 'tooltip-left', 'tooltip-right');
                
                // Calculate positions
                const spaceAbove = rect.top;
                const spaceBelow = viewportHeight - rect.bottom;
                const spaceLeft = rect.left;
                const spaceRight = viewportWidth - rect.right;
                
                // Determine best vertical position
                if (spaceAbove >= tooltipRect.height + 10) {
                    // Enough space above, position above (default)
                    tooltip.classList.add('tooltip-above');
                } else if (spaceBelow >= tooltipRect.height + 10) {
                    // Not enough space above, position below
                    tooltip.classList.add('tooltip-below');
                } else {
                    // Not enough space above or below, choose the side with more space
                    if (spaceAbove > spaceBelow) {
                        tooltip.classList.add('tooltip-above');
                    } else {
                        tooltip.classList.add('tooltip-below');
                    }
                }
                
                // Check horizontal bounds
                const tooltipLeft = rect.left + rect.width / 2 - tooltipRect.width / 2;
                if (tooltipLeft < 10) {
                    tooltip.classList.add('tooltip-right');
                } else if (tooltipLeft + tooltipRect.width > viewportWidth - 10) {
                    tooltip.classList.add('tooltip-left');
                }
            }
            
            // Add hover events
            pointElement.addEventListener('mouseenter', function() {
                tooltip.classList.add('show');
                pointElement.classList.add('active');
                
                // Load image when tooltip is shown, passing the point data
                loadMainImageForTooltip(point);
                // Position tooltip after a short delay to ensure dimensions are calculated
                setTimeout(positionTooltip, 50);
            });
            
            pointElement.addEventListener('mouseleave', function() {
                tooltip.classList.remove('show');
                pointElement.classList.remove('active');
            });
            
            // Add click event to redirect to detail page
            pointElement.addEventListener('click', function(e) {
                e.stopPropagation();
                window.location.href = `place_detail.php?id=${point.id}`;
            });
            
            pointElement.appendChild(tooltip);
            mapOverlay.appendChild(pointElement);
        }
        
        // Load points from database
        function loadPointsFromDB() {
            fetch('./scriptes/map_save_points.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'loadPoints',
                    map_id: currentMapId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.points) {
                    // Clear existing points FIRST
                    clearPointsFromDOM();
                    points = [];
                    
                    // Load points from database
                    data.points.forEach(dbPoint => {
                        // Check if point already exists to prevent duplicates
                        const existingPoint = points.find(p => p.id === dbPoint.id_IP);
                        if (existingPoint) {
                            return;
                        }
                        
                        const point = {
                            id: dbPoint.id_IP,
                            name: dbPoint.name_IP,
                            description: dbPoint.description_IP,
                            type: dbPoint.type_IP,
                            x: parseFloat(dbPoint.x_IP),
                            y: parseFloat(dbPoint.y_IP)
                        };
                        points.push(point);
                        createPointElement(point);
                    });
                    
                    // Update welcome banner with point count
                    updateWelcomeBanner(points.length);
                } else {
                    updateWelcomeBanner(0);
                }
            })
            .catch(error => {
                console.error('Connection error:', error);
                console.log('Unable to load points from database.');
                updateWelcomeBanner(-1); // -1 indicates connection error
            });
        }
        
        // Update welcome banner with point information
        function updateWelcomeBanner(pointCount) {
            const dynamicText = document.getElementById('dynamic-welcome-text');
            
            if (pointCount > 0) {
                dynamicText.innerHTML = `📍 <strong>${pointCount} locations</strong> are available to explore. Enjoy your exploration!`;
            } else if (pointCount === 0) {
                dynamicText.innerHTML = `📍 No locations are currently available on this map. Check back later as new places are discovered and added to the world!`;
            } else {
                dynamicText.innerHTML = `⚠️ Unable to connect to the server to load locations. The map is still viewable, but location data may not be available.`;
            }
        }
        
        // Help system functionality
        let helpTimeout;
        const helpTrigger = document.getElementById('map-help-trigger');
        const helpContent = document.getElementById('map-help-content');
        const helpContainer = document.getElementById('map-help-container');
        const mainText = document.getElementById('mainText');
        
        // Handle scroll positioning for help container
        function handleHelpContainerPosition() {
            const mainTextRect = mainText.getBoundingClientRect();
            const scrollY = window.pageYOffset;
            
            // If mainText top is below the viewport top, switch to fixed positioning
            if (mainTextRect.top <= 20) {
                helpContainer.classList.add('fixed');
            } else {
                helpContainer.classList.remove('fixed');
            }
        }
        
        // Add scroll listener
        window.addEventListener('scroll', handleHelpContainerPosition);
        window.addEventListener('resize', handleHelpContainerPosition);
        
        // === Multi-Map Management Functions ===
        
        // Load available maps from database
        function loadAvailableMaps() {
            return fetch('./scriptes/maps_manager.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_maps_with_point_counts'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    availableMaps = data.maps;
                    populateMapSelector();
                    
                    // Set default map if not already set
                    if (!currentMapData && availableMaps.length > 0) {
                        currentMapId = availableMaps[0].id_map;
                        switchToMap(currentMapId);
                    }
                } else {
                    console.error('Failed to load maps:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading maps:', error);
            });
        }
        
        // Populate the map selector dropdown
        function populateMapSelector() {
            const selector = document.getElementById('map-selector');
            
            if (!selector) {
                console.error('Map selector element not found!');
                return;
            }
            
            selector.innerHTML = '';
            
            availableMaps.forEach(map => {
                const option = document.createElement('option');
                option.value = map.id_map;
                option.textContent = `${map.name_map} (${map.point_count} points)`;
                selector.appendChild(option);
            });
            
            // Set current selection
            selector.value = currentMapId;
            
            // Add change event listener
            selector.addEventListener('change', function() {
                const newMapId = parseInt(this.value);
                if (newMapId !== currentMapId) {
                    switchToMap(newMapId);
                }
            });
        }
        
        // Switch to a different map
        function switchToMap(mapId) {
            currentMapId = mapId;
            currentMapData = availableMaps.find(m => m.id_map == mapId);
            
            if (currentMapData) {
                // Update map image
                const mapImage = document.getElementById('interactive-map-image');
                mapImage.src = currentMapData.map_file;
                mapImage.alt = currentMapData.name_map;
                
                // Update map info display
                updateMapInfo();
                
                // Clear current points and load points for this map
                clearPointsFromDOM();
                points = [];
                loadPointsFromDB();
                
                // Update selector to show current selection
                document.getElementById('map-selector').value = mapId;
            }
        }
        
        // Update map information display
        function updateMapInfo() {
            const mapInfo = document.getElementById('map-info');
            if (currentMapData) {
                mapInfo.innerHTML = `
                    <span style="color: #222088; font-weight: bold;">${currentMapData.name_map}</span> |
                    <span style="color: #666;">${currentMapData.point_count} points to explore</span>
                `;
            }
        }
        
        // Clear all points from DOM
        function clearPointsFromDOM() {
            const existingPoints = document.querySelectorAll('.map-point-of-interest');
            existingPoints.forEach(point => point.remove());
        }
        
        // Initial position check
        window.addEventListener('load', function() {
            handleHelpContainerPosition();
            // Load maps first, then types, then points
            loadAvailableMaps().then(function() {
                return loadPointTypes();
            }).then(function(typesLoaded) {
                loadPointsFromDB(); // Load points after maps and types are loaded
            });
        });
        
        // Show help on hover (with delay) or click
        helpTrigger.addEventListener('mouseenter', function() {
            helpTimeout = setTimeout(() => {
                helpContent.classList.add('show');
            }, 800); // 800ms delay
        });
        
        helpTrigger.addEventListener('mouseleave', function() {
            clearTimeout(helpTimeout);
            // Don't hide immediately on mouse leave, let user interact with tooltip
        });
        
        helpTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            clearTimeout(helpTimeout);
            helpContent.classList.toggle('show');
        });
        
        // Hide help when clicking outside
        document.addEventListener('click', function(e) {
            if (!helpContent.contains(e.target) && !helpTrigger.contains(e.target)) {
                helpContent.classList.remove('show');
            }
        });
        
        function hideHelp() {
            helpContent.classList.remove('show');
        }
        
        // Close notification function (legacy)
        function closeNotification(id) {
            const notification = document.getElementById(id);
            if (notification) {
                notification.style.display = 'none';
            }
        }
        
        // Add some visual feedback for map interaction
        mapOverlay.addEventListener('mousemove', function(e) {
            // Add subtle cursor feedback when hovering over empty areas
            const isOverPoint = e.target.classList.contains('map-point-of-interest');
            mapOverlay.style.cursor = isOverPoint ? 'pointer' : 'default';
        });
    </script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>

