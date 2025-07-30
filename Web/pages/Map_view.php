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
        
        const mapOverlay = document.getElementById('interactive-map-overlay');
        
        // Create point element (read-only version)
        function createPointElement(point) {
            const pointElement = document.createElement('div');
            pointElement.className = 'map-point-of-interest map-point-readonly';
            pointElement.style.left = point.x + '%';
            pointElement.style.top = point.y + '%';
            pointElement.dataset.pointId = point.id;
            
            // Create tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'map-poi-tooltip';
            tooltip.innerHTML = `
                <strong>${point.name}</strong><br>
                <em>${point.type}</em><br>
                ${point.description}
            `;
            
            // Add hover events
            pointElement.addEventListener('mouseenter', function() {
                tooltip.classList.add('show');
                pointElement.classList.add('active');
            });
            
            pointElement.addEventListener('mouseleave', function() {
                tooltip.classList.remove('show');
                pointElement.classList.remove('active');
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
                    action: 'load_points',
                    map_id: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Load points from database
                    points = data.points || [];
                    points.forEach(point => {
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
            loadPointsFromDB();
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