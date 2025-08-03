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
            
            <div id="interactive-map-container">
                <img id="interactive-map-image" src="../images/map/map_monde.png" alt="World Map">
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
                    <a href="Map_modif.php" style="
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
            const pointElement = document.createElement('div');
            pointElement.className = 'map-point-of-interest map-point-readonly';
            pointElement.style.left = point.x + '%';
            pointElement.style.top = point.y + '%';
            pointElement.dataset.pointId = point.id;
            
            // Set point color based on type
            const pointColor = getColorForType(point.type);
            pointElement.style.backgroundColor = pointColor;
            pointElement.style.borderColor = '#ffffff';
            
            // Create slug for image path
            const slug = point.name.toLowerCase()
                .trim()
                .replace(/[^a-z0-9\-]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
            
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
            
            // Function to load main image
            function loadMainImageForTooltip() {
                const imageContainer = document.getElementById(`tooltip-image-${point.id}`);
                const possibleExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                let imageFound = false;
                
                async function checkImage(extension) {
                    try {
                        const imagePath = `../images/places/${slug}/main.${extension}`;
                        const response = await fetch(imagePath);
                        if (response.ok) {
                            imageContainer.innerHTML = `
                                <img src="${imagePath}" 
                                     alt="Image of ${point.name}" 
                                     style="max-width: 180px; max-height: 120px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.3); object-fit: cover;">
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
                        imageContainer.innerHTML = `
                            <div style="padding: 15px; background: rgba(0,0,0,0.1); border-radius: 4px; color: #666; font-size: 11px; border: 1px dashed #999;">
                                📷 No image available
                            </div>
                        `;
                    }
                })();
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
                // Load image when tooltip is shown
                loadMainImageForTooltip();
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
                    map_id: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.points) {
                    // Clear existing points
                    points = [];
                    
                    // Load points from database
                    data.points.forEach(dbPoint => {
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
                    
                    console.log(`${points.length} points loaded from database`);
                    
                    // Update welcome banner with point count
                    updateWelcomeBanner(points.length);
                } else {
                    console.log('No points in database: ' + data.message);
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
        
        // Initial position check
        window.addEventListener('load', function() {
            handleHelpContainerPosition();
            // Load point types first, then load points
            loadPointTypes().then(function(typesLoaded) {
                loadPointsFromDB(); // Load points after types are loaded
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