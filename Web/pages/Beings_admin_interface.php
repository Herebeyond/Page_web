<?php
// Beings Admin Interface - AJAX endpoint for managing species and races
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../login/db.php';

// Verify database connection was successful  
if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    echo '<div class="error-message">Database connection failed</div>';
    exit;
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

$action = isset($_GET['action']) ? $_GET['action'] : 'main';

switch ($action) {
    case 'main':
        renderMainInterface();
        break;
    case 'edit_species':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        renderEditSpeciesForm($id);
        break;
    case 'add_species':
        renderAddSpeciesForm();
        break;
    case 'edit_race':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        renderEditRaceForm($id);
        break;
    case 'add_race':
        $speciesId = isset($_GET['species_id']) ? (int)$_GET['species_id'] : 0;
        renderAddRaceForm($speciesId);
        break;
    case 'save_species':
        saveSpecies();
        break;
    case 'save_race':
        saveRace();
        break;
    case 'delete_species':
        deleteSpecies();
        break;
    case 'delete_race':
        deleteRace();
        break;
    default:
        renderMainInterface();
}

function renderMainInterface() {
    global $pdo;
    ?>
    <div class="admin-interface">
        <h2>Beings Management</h2>
        
        <div class="admin-tabs">
            <button class="tab-btn active" onclick="showTab('species')">Species Management</button>
            <button class="tab-btn" onclick="showTab('races')">Race Management</button>
            <button class="tab-btn" onclick="showTab('statistics')">Statistics</button>
        </div>

        <!-- Species Management Tab -->
        <div id="species-tab" class="tab-content active">
            <div class="tab-header">
                <h3>Species Management</h3>
                <button class="btn-primary" onclick="addNewSpecies()">+ Add New Species</button>
            </div>
            
            <div class="entities-list">
                <?php
                $stmt = $pdo->prepare("SELECT s.*, COUNT(r.id_race) as race_count 
                                     FROM species s 
                                     LEFT JOIN races r ON s.id_specie = r.correspondence 
                                     GROUP BY s.id_specie 
                                     ORDER BY s.specie_name");
                $stmt->execute();
                
                while ($species = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $imgPath = empty($species['icon_specie']) ? '../images/icon_default.png' : 
                              '../images/' . str_replace(' ', '_', $species['icon_specie']);
                    ?>
                    <div class="entity-item">
                        <div class="entity-info">
                            <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="Species icon" class="entity-icon">
                            <div class="entity-details">
                                <h4><?php echo htmlspecialchars($species['specie_name']); ?></h4>
                                <p><?php echo $species['race_count']; ?> race(s)</p>
                                <?php if (!empty($species['content_specie'])): ?>
                                <p class="entity-description">
                                    <?php echo htmlspecialchars(substr($species['content_specie'], 0, 100)); ?>...
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="entity-actions">
                            <button class="btn-edit" onclick="editSpecies(<?php echo $species['id_specie']; ?>)">Edit</button>
                            <button class="btn-danger" onclick="confirmDeleteSpecies(<?php echo $species['id_specie']; ?>, '<?php echo htmlspecialchars($species['specie_name']); ?>')">Delete</button>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- Race Management Tab -->
        <div id="races-tab" class="tab-content">
            <div class="tab-header">
                <h3>Race Management</h3>
                <button class="btn-primary" onclick="addNewRace()">+ Add New Race</button>
            </div>
            
            <div class="entities-list">
                <?php
                $stmt = $pdo->prepare("SELECT r.*, s.specie_name, COUNT(c.id_character) as character_count 
                                     FROM races r 
                                     JOIN species s ON r.correspondence = s.id_specie 
                                     LEFT JOIN characters c ON r.id_race = c.correspondence 
                                     GROUP BY r.id_race 
                                     ORDER BY s.specie_name, r.race_name");
                $stmt->execute();
                
                while ($race = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $imgPath = empty($race['icon_race']) ? '../images/icon_default.png' : 
                              '../images/' . str_replace(' ', '_', $race['icon_race']);
                    ?>
                    <div class="entity-item">
                        <div class="entity-info">
                            <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="Race icon" class="entity-icon">
                            <div class="entity-details">
                                <h4><?php echo htmlspecialchars($race['race_name']); ?></h4>
                                <p><strong>Species:</strong> <?php echo htmlspecialchars($race['specie_name']); ?></p>
                                <p><?php echo $race['character_count']; ?> character(s)</p>
                                <div class="race-stats">
                                    <?php if (!empty($race['lifespan'])): ?>
                                    <span>Lifespan: <?php echo htmlspecialchars($race['lifespan']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($race['homeworld'])): ?>
                                    <span>Homeworld: <?php echo htmlspecialchars($race['homeworld']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="entity-actions">
                            <button class="btn-edit" onclick="editRace(<?php echo $race['id_race']; ?>)">Edit</button>
                            <button class="btn-danger" onclick="confirmDeleteRace(<?php echo $race['id_race']; ?>, '<?php echo htmlspecialchars($race['race_name']); ?>')">Delete</button>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- Statistics Tab -->
        <div id="statistics-tab" class="tab-content">
            <div class="tab-header">
                <h3>Beings Statistics</h3>
            </div>
            
            <div class="stats-grid">
                <?php
                // Get statistics
                $statsQueries = [
                    'Total Species' => "SELECT COUNT(*) FROM species",
                    'Total Races' => "SELECT COUNT(*) FROM races",
                    'Total Characters' => "SELECT COUNT(*) FROM characters",
                    'Species with Races' => "SELECT COUNT(DISTINCT s.id_specie) FROM species s JOIN races r ON s.id_specie = r.correspondence"
                ];
                
                foreach ($statsQueries as $label => $query) {
                    $stmt = $pdo->prepare($query);
                    $stmt->execute();
                    $count = $stmt->fetchColumn();
                    ?>
                    <div class="stat-card">
                        <h4><?php echo $label; ?></h4>
                        <div class="stat-number"><?php echo $count; ?></div>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <!-- Top Species by Race Count -->
            <div class="chart-section">
                <h4>Species by Race Count</h4>
                <div class="chart-list">
                    <?php
                    $stmt = $pdo->prepare("SELECT s.specie_name, COUNT(r.id_race) as race_count 
                                         FROM species s 
                                         LEFT JOIN races r ON s.id_specie = r.correspondence 
                                         GROUP BY s.id_specie 
                                         ORDER BY race_count DESC 
                                         LIMIT 10");
                    $stmt->execute();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <div class="chart-item">
                            <span class="chart-label"><?php echo htmlspecialchars($row['specie_name']); ?></span>
                            <div class="chart-bar">
                                <div class="chart-fill" style="width: <?php echo $row['race_count'] * 20; ?>%"></div>
                                <span class="chart-value"><?php echo $row['race_count']; ?></span>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <style>
    .admin-interface {
        padding: 1rem;
    }

    .admin-tabs {
        display: flex;
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 1.5rem;
    }

    .tab-btn {
        background: none;
        border: none;
        padding: 1rem 1.5rem;
        cursor: pointer;
        font-size: 1rem;
        color: #6c757d;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .tab-btn.active {
        color: #222088;
        border-bottom-color: #222088;
    }

    .tab-btn:hover {
        background: #f8f9fa;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .tab-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .btn-primary {
        background: #222088;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #1a1066;
    }

    .entities-list {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }

    .entity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.3s ease;
    }

    .entity-item:hover {
        background: #f8f9fa;
    }

    .entity-item:last-child {
        border-bottom: none;
    }

    .entity-info {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .entity-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 1rem;
        border: 1px solid #dee2e6;
    }

    .entity-details h4 {
        margin: 0 0 0.25rem 0;
        color: #222088;
    }

    .entity-details p {
        margin: 0.25rem 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .entity-description {
        font-style: italic;
    }

    .race-stats {
        display: flex;
        gap: 1rem;
        font-size: 0.8rem;
        color: #6c757d;
    }

    .entity-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-edit, .btn-danger {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background: #007bff;
        color: white;
    }

    .btn-edit:hover {
        background: #0056b3;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #222088, #3f51b5);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
    }

    .stat-card h4 {
        margin: 0 0 0.5rem 0;
        font-size: 1rem;
        opacity: 0.9;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .chart-section {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
    }

    .chart-section h4 {
        margin: 0 0 1rem 0;
        color: #222088;
    }

    .chart-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .chart-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .chart-label {
        width: 150px;
        font-size: 0.9rem;
        color: #495057;
    }

    .chart-bar {
        flex: 1;
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        position: relative;
        margin: 0 1rem;
    }

    .chart-fill {
        height: 100%;
        background: linear-gradient(90deg, #222088, #3f51b5);
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .chart-value {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8rem;
        color: white;
        font-weight: bold;
    }
    </style>

    <script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');
    }

    function addNewSpecies() {
        fetch('./Beings_admin_interface.php?action=add_species')
            .then(response => response.text())
            .then(html => {
                document.getElementById('adminModalContent').innerHTML = html;
            });
    }

    function addNewRace() {
        fetch('./Beings_admin_interface.php?action=add_race')
            .then(response => response.text())
            .then(html => {
                document.getElementById('adminModalContent').innerHTML = html;
            });
    }

    function editSpecies(id) {
        fetch('./Beings_admin_interface.php?action=edit_species&id=' + id)
            .then(response => response.text())
            .then(html => {
                document.getElementById('adminModalContent').innerHTML = html;
            });
    }

    function editRace(id) {
        fetch('./Beings_admin_interface.php?action=edit_race&id=' + id)
            .then(response => response.text())
            .then(html => {
                document.getElementById('adminModalContent').innerHTML = html;
            });
    }

    function confirmDeleteSpecies(id, name) {
        if (confirm('Are you sure you want to delete the species "' + name + '"? This will also delete all associated races and characters.')) {
            deleteEntity('species', id);
        }
    }

    function confirmDeleteRace(id, name) {
        if (confirm('Are you sure you want to delete the race "' + name + '"? This will also delete all associated characters.')) {
            deleteEntity('race', id);
        }
    }

    function deleteEntity(type, id) {
        fetch('./Beings_admin_interface.php?action=delete_' + type, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Refresh the page
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the ' + type);
        });
    }
    </script>
    <?php
}

function renderAddSpeciesForm() {
    ?>
    <div class="form-container">
        <h3>Add New Species</h3>
        <form id="speciesForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_species">
            
            <div class="form-group">
                <label for="specie_name">Species Name *</label>
                <input type="text" id="specie_name" name="specie_name" required>
            </div>
            
            <div class="form-group">
                <label for="specie_icon">Species Icon</label>
                <input type="file" id="specie_icon" name="specie_icon" accept="image/*">
                <small>Max 5MB - JPG, PNG, GIF, WebP</small>
            </div>
            
            <div class="form-group">
                <label for="specie_content">Description</label>
                <textarea id="specie_content" name="specie_content" rows="4"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Create Species</button>
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <style>
    .form-container {
        padding: 2rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #222088;
        font-weight: 600;
    }
    
    .form-group input, .form-group textarea, .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
        outline: none;
        border-color: #222088;
    }
    
    .form-group small {
        display: block;
        margin-top: 0.25rem;
        color: #6c757d;
        font-size: 0.8rem;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    </style>
    
    <script>
    document.getElementById('speciesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('./Beings_admin_interface.php?action=save_species', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Species created successfully!');
                closeAdminModal();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the species');
        });
    });
    </script>
    <?php
}

function renderEditSpeciesForm($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM species WHERE id_specie = ?");
    $stmt->execute([$id]);
    $species = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$species) {
        echo '<div class="error-message">Species not found</div>';
        return;
    }
    
    ?>
    <div class="form-container">
        <h3>Edit Species: <?php echo htmlspecialchars($species['specie_name']); ?></h3>
        <form id="speciesForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_species">
            <input type="hidden" name="id" value="<?php echo $species['id_specie']; ?>">
            
            <div class="form-group">
                <label for="specie_name">Species Name *</label>
                <input type="text" id="specie_name" name="specie_name" 
                       value="<?php echo htmlspecialchars($species['specie_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="specie_icon">Species Icon</label>
                <?php if (!empty($species['icon_specie'])): ?>
                <div class="current-image">
                    <img src="../images/<?php echo htmlspecialchars($species['icon_specie']); ?>" 
                         alt="Current icon" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                    <small>Current icon</small>
                </div>
                <?php endif; ?>
                <input type="file" id="specie_icon" name="specie_icon" accept="image/*">
                <small>Max 5MB - JPG, PNG, GIF, WebP (leave empty to keep current icon)</small>
            </div>
            
            <div class="form-group">
                <label for="specie_content">Description</label>
                <textarea id="specie_content" name="specie_content" rows="4"><?php echo htmlspecialchars($species['content_specie']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Update Species</button>
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById('speciesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('./Beings_admin_interface.php?action=save_species', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Species updated successfully!');
                closeAdminModal();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the species');
        });
    });
    </script>
    <?php
}

function renderAddRaceForm($speciesId = 0) {
    global $pdo;
    
    ?>
    <div class="form-container">
        <h3>Add New Race</h3>
        <form id="raceForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_race">
            
            <div class="form-group">
                <label for="race_name">Race Name *</label>
                <input type="text" id="race_name" name="race_name" required>
            </div>
            
            <div class="form-group">
                <label for="correspondence">Species *</label>
                <select id="correspondence" name="correspondence" required>
                    <option value="">Select a species</option>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM species ORDER BY specie_name");
                    $stmt->execute();
                    while ($species = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = $speciesId == $species['id_specie'] ? 'selected' : '';
                        echo "<option value='{$species['id_specie']}' $selected>" . 
                             htmlspecialchars($species['specie_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="race_icon">Race Icon</label>
                <input type="file" id="race_icon" name="race_icon" accept="image/*">
                <small>Max 5MB - JPG, PNG, GIF, WebP</small>
            </div>
            
            <div class="form-group">
                <label for="lifespan">Lifespan</label>
                <input type="text" id="lifespan" name="lifespan" placeholder="e.g., 100 years, Immortal">
            </div>
            
            <div class="form-group">
                <label for="homeworld">Homeworld</label>
                <input type="text" id="homeworld" name="homeworld">
            </div>
            
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country">
            </div>
            
            <div class="form-group">
                <label for="habitat">Habitat</label>
                <input type="text" id="habitat" name="habitat">
            </div>
            
            <div class="form-group">
                <label for="race_content">Description</label>
                <textarea id="race_content" name="race_content" rows="4"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Create Race</button>
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById('raceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('./Beings_admin_interface.php?action=save_race', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Race created successfully!');
                closeAdminModal();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the race');
        });
    });
    </script>
    <?php
}

function renderEditRaceForm($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT r.*, s.specie_name FROM races r 
                          JOIN species s ON r.correspondence = s.id_specie 
                          WHERE r.id_race = ?");
    $stmt->execute([$id]);
    $race = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$race) {
        echo '<div class="error-message">Race not found</div>';
        return;
    }
    
    ?>
    <div class="form-container">
        <h3>Edit Race: <?php echo htmlspecialchars($race['race_name']); ?></h3>
        <form id="raceForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_race">
            <input type="hidden" name="id" value="<?php echo $race['id_race']; ?>">
            
            <div class="form-group">
                <label for="race_name">Race Name *</label>
                <input type="text" id="race_name" name="race_name" 
                       value="<?php echo htmlspecialchars($race['race_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="correspondence">Species *</label>
                <select id="correspondence" name="correspondence" required>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM species ORDER BY specie_name");
                    $stmt->execute();
                    while ($species = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = $race['correspondence'] == $species['id_specie'] ? 'selected' : '';
                        echo "<option value='{$species['id_specie']}' $selected>" . 
                             htmlspecialchars($species['specie_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="race_icon">Race Icon</label>
                <?php if (!empty($race['icon_race'])): ?>
                <div class="current-image">
                    <img src="../images/<?php echo htmlspecialchars($race['icon_race']); ?>" 
                         alt="Current icon" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                    <small>Current icon</small>
                </div>
                <?php endif; ?>
                <input type="file" id="race_icon" name="race_icon" accept="image/*">
                <small>Max 5MB - JPG, PNG, GIF, WebP (leave empty to keep current icon)</small>
            </div>
            
            <div class="form-group">
                <label for="lifespan">Lifespan</label>
                <input type="text" id="lifespan" name="lifespan" 
                       value="<?php echo htmlspecialchars($race['lifespan']); ?>">
            </div>
            
            <div class="form-group">
                <label for="homeworld">Homeworld</label>
                <input type="text" id="homeworld" name="homeworld" 
                       value="<?php echo htmlspecialchars($race['homeworld']); ?>">
            </div>
            
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" 
                       value="<?php echo htmlspecialchars($race['country']); ?>">
            </div>
            
            <div class="form-group">
                <label for="habitat">Habitat</label>
                <input type="text" id="habitat" name="habitat" 
                       value="<?php echo htmlspecialchars($race['habitat']); ?>">
            </div>
            
            <div class="form-group">
                <label for="race_content">Description</label>
                <textarea id="race_content" name="race_content" rows="4"><?php echo htmlspecialchars($race['content_race']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Update Race</button>
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Cancel</button>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById('raceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('./Beings_admin_interface.php?action=save_race', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Race updated successfully!');
                closeAdminModal();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the race');
        });
    });
    </script>
    <?php
}

function saveSpecies() {
    global $pdo;
    
    header('Content-Type: application/json');
    
    try {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['specie_name']);
        $content = trim($_POST['specie_content']);
        
        if (empty($name)) {
            throw new Exception('Species name is required');
        }
        
        // Handle file upload
        $iconPath = null;
        if (isset($_FILES['specie_icon']) && $_FILES['specie_icon']['error'] === UPLOAD_ERR_OK) {
            $iconPath = handleFileUpload($_FILES['specie_icon']);
        }
        
        if ($id > 0) {
            // Update existing species
            $fields = ['specie_name = ?'];
            $params = [$name];
            
            if (!empty($content)) {
                $fields[] = 'content_specie = ?';
                $params[] = $content;
            }
            
            if ($iconPath) {
                $fields[] = 'icon_specie = ?';
                $params[] = $iconPath;
            }
            
            $params[] = $id;
            
            $sql = "UPDATE species SET " . implode(', ', $fields) . " WHERE id_specie = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Species updated successfully']);
        } else {
            // Create new species
            $stmt = $pdo->prepare("INSERT INTO species (specie_name, icon_specie, content_specie) VALUES (?, ?, ?)");
            $stmt->execute([$name, $iconPath, $content]);
            
            echo json_encode(['success' => true, 'message' => 'Species created successfully']);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function saveRace() {
    global $pdo;
    
    header('Content-Type: application/json');
    
    try {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['race_name']);
        $correspondence = (int)$_POST['correspondence'];
        $content = trim($_POST['race_content']);
        $lifespan = trim($_POST['lifespan']);
        $homeworld = trim($_POST['homeworld']);
        $country = trim($_POST['country']);
        $habitat = trim($_POST['habitat']);
        
        if (empty($name)) {
            throw new Exception('Race name is required');
        }
        
        if ($correspondence <= 0) {
            throw new Exception('Species selection is required');
        }
        
        // Handle file upload
        $iconPath = null;
        if (isset($_FILES['race_icon']) && $_FILES['race_icon']['error'] === UPLOAD_ERR_OK) {
            $iconPath = handleFileUpload($_FILES['race_icon']);
        }
        
        if ($id > 0) {
            // Update existing race
            $fields = ['race_name = ?', 'correspondence = ?', 'content_race = ?', 'lifespan = ?', 
                      'homeworld = ?', 'country = ?', 'habitat = ?'];
            $params = [$name, $correspondence, $content, $lifespan, $homeworld, $country, $habitat];
            
            if ($iconPath) {
                $fields[] = 'icon_race = ?';
                $params[] = $iconPath;
            }
            
            $params[] = $id;
            
            $sql = "UPDATE races SET " . implode(', ', $fields) . " WHERE id_race = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Race updated successfully']);
        } else {
            // Create new race
            $stmt = $pdo->prepare("INSERT INTO races (race_name, correspondence, icon_race, content_race, lifespan, homeworld, country, habitat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $correspondence, $iconPath, $content, $lifespan, $homeworld, $country, $habitat]);
            
            echo json_encode(['success' => true, 'message' => 'Race created successfully']);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleFileUpload($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Please upload JPG, PNG, GIF, or WebP images.');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds 5MB limit.');
    }
    
    $uploadDir = __DIR__ . '/../images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $fileInfo['filename']) . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to upload file.');
    }
    
    return $filename;
}

function deleteSpecies() {
    global $pdo;
    
    header('Content-Type: application/json');
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)$input['id'];
        
        if ($id <= 0) {
            throw new Exception('Invalid species ID');
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete characters first (they reference races)
        $stmt = $pdo->prepare("DELETE c FROM characters c 
                              JOIN races r ON c.correspondence = r.id_race 
                              WHERE r.correspondence = ?");
        $stmt->execute([$id]);
        
        // Delete races
        $stmt = $pdo->prepare("DELETE FROM races WHERE correspondence = ?");
        $stmt->execute([$id]);
        
        // Delete species
        $stmt = $pdo->prepare("DELETE FROM species WHERE id_specie = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Species and all associated data deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function deleteRace() {
    global $pdo;
    
    header('Content-Type: application/json');
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)$input['id'];
        
        if ($id <= 0) {
            throw new Exception('Invalid race ID');
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete characters first
        $stmt = $pdo->prepare("DELETE FROM characters WHERE correspondence = ?");
        $stmt->execute([$id]);
        
        // Delete race
        $stmt = $pdo->prepare("DELETE FROM races WHERE id_race = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Race and all associated characters deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
