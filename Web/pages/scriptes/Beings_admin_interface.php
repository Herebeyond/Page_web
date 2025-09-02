<?php
// Beings Admin Interface - AJAX endpoint for managing species and races
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set error handler for JSON responses
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'PHP Error: ' . $message]);
    exit;
});

// Set exception handler for JSON responses
set_exception_handler(function($exception) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $exception->getMessage()]);
    exit;
});

require_once __DIR__ . '/../../login/db.php';

// Verify database connection was successful  
if (!isset($pdo) || !$pdo) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
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
                <button class="btn-primary" onclick="addNewSpecies()">Add New Species</button>
            </div>
            
            <div class="entities-list">
                <?php
                try {
                    $stmt = $pdo->prepare("SELECT s.*, COUNT(r.id_race) as race_count 
                                          FROM species s 
                                          LEFT JOIN races r ON s.id_specie = r.correspondence 
                                          GROUP BY s.id_specie 
                                          ORDER BY s.specie_name");
                    $stmt->execute();
                    
                    while ($species = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $iconPath = !empty($species['icon_specie']) ? '../images/' . $species['icon_specie'] : '../images/icon_default.png';
                        ?>
                        <div class="entity-item">
                            <div class="entity-info">
                                <img src="<?php echo htmlspecialchars($iconPath); ?>" alt="Species Icon" class="entity-icon" onerror="this.src='../images/icon_default.png'">
                                <div class="entity-details">
                                    <h4><?php echo htmlspecialchars($species['specie_name']); ?></h4>
                                    <p><?php echo $species['race_count']; ?> race(s)</p>
                                    <?php if (!empty($species['content_specie'])): ?>
                                    <p class="entity-description"><?php echo htmlspecialchars(substr($species['content_specie'], 0, 100)); ?>...</p>
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
                } catch (PDOException $e) {
                    echo '<div class="error-message">Error loading species: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>

        <!-- Race Management Tab -->
        <div id="races-tab" class="tab-content">
            <div class="tab-header">
                <h3>Race Management</h3>
                <button class="btn-primary" onclick="addNewRace()">Add New Race</button>
            </div>
            
            <div class="entities-list">
                <?php
                try {
                    $stmt = $pdo->prepare("SELECT r.*, s.specie_name 
                                          FROM races r 
                                          JOIN species s ON r.correspondence = s.id_specie 
                                          ORDER BY s.specie_name, r.race_name");
                    $stmt->execute();
                    
                    while ($race = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $iconPath = !empty($race['icon_race']) ? '../images/' . $race['icon_race'] : '../images/icon_default.png';
                        ?>
                        <div class="entity-item">
                            <div class="entity-info">
                                <img src="<?php echo htmlspecialchars($iconPath); ?>" alt="Race Icon" class="entity-icon" onerror="this.src='../images/icon_default.png'">
                                <div class="entity-details">
                                    <h4><?php echo htmlspecialchars($race['race_name']); ?></h4>
                                    <p>Species: <?php echo htmlspecialchars($race['specie_name']); ?></p>
                                    <div class="race-stats">
                                        <?php if (!empty($race['lifespan'])): ?>
                                        <span>Lifespan: <?php echo htmlspecialchars($race['lifespan']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($race['homeworld'])): ?>
                                        <span>Homeworld: <?php echo htmlspecialchars($race['homeworld']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($race['content_race'])): ?>
                                    <p class="entity-description"><?php echo htmlspecialchars(substr($race['content_race'], 0, 100)); ?>...</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="entity-actions">
                                <button class="btn-edit" onclick="editRace(<?php echo $race['id_race']; ?>)">Edit</button>
                                <button class="btn-danger" onclick="confirmDeleteRace(<?php echo $race['id_race']; ?>, '<?php echo htmlspecialchars($race['race_name']); ?>')">Delete</button>
                            </div>
                        </div>
                        <?php
                    }
                } catch (PDOException $e) {
                    echo '<div class="error-message">Error loading races: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>

        <!-- Statistics Tab -->
        <div id="statistics-tab" class="tab-content">
            <h3>Beings Statistics</h3>
            
            <?php
            try {
                // Get basic counts
                $speciesCount = $pdo->query("SELECT COUNT(*) FROM species")->fetchColumn();
                $racesCount = $pdo->query("SELECT COUNT(*) FROM races")->fetchColumn();
                $charactersCount = $pdo->query("SELECT COUNT(*) FROM characters")->fetchColumn();
                
                // Get species with most races
                $stmt = $pdo->prepare("SELECT s.specie_name, COUNT(r.id_race) as race_count 
                                      FROM species s 
                                      LEFT JOIN races r ON s.id_specie = r.correspondence 
                                      GROUP BY s.id_specie 
                                      ORDER BY race_count DESC");
                $stmt->execute();
                $speciesStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $maxRaces = $speciesStats[0]['race_count'] ?? 1;
                ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Total Species</h4>
                        <div class="stat-number"><?php echo $speciesCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Races</h4>
                        <div class="stat-number"><?php echo $racesCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Characters</h4>
                        <div class="stat-number"><?php echo $charactersCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Avg Races/Species</h4>
                        <div class="stat-number"><?php echo $speciesCount > 0 ? round($racesCount / $speciesCount, 1) : 0; ?></div>
                    </div>
                </div>
                
                <div class="chart-section">
                    <h4>Races per Species</h4>
                    <div class="chart-list">
                        <?php foreach ($speciesStats as $stat): 
                            $percentage = $maxRaces > 0 ? min(100, ($stat['race_count'] / $maxRaces) * 100) : 0;
                        ?>
                        <div class="chart-item">
                            <div class="chart-label"><?php echo htmlspecialchars($stat['specie_name']); ?></div>
                            <div class="chart-bar">
                                <div class="chart-fill" style="width: <?php echo $percentage; ?>%"></div>
                                <div class="chart-value"><?php echo $stat['race_count']; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php
            } catch (PDOException $e) {
                echo '<div class="error-message">Error loading statistics: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
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
        fetch('./scriptes/Beings_admin_interface.php?action=add_species')
            .then(response => response.text())
            .then(data => {
                document.getElementById('adminModalContent').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading add species form');
            });
    }

    function addNewRace() {
        fetch('./scriptes/Beings_admin_interface.php?action=add_race')
            .then(response => response.text())
            .then(data => {
                document.getElementById('adminModalContent').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading add race form');
            });
    }

    function editSpecies(id) {
        fetch('./scriptes/Beings_admin_interface.php?action=edit_species&id=' + id)
            .then(response => response.text())
            .then(data => {
                document.getElementById('adminModalContent').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading edit species form');
            });
    }

    function editRace(id) {
        fetch('./scriptes/Beings_admin_interface.php?action=edit_race&id=' + id)
            .then(response => response.text())
            .then(data => {
                document.getElementById('adminModalContent').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading edit race form');
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
        fetch('./scriptes/Beings_admin_interface.php?action=delete_' + type, {
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
                // Reload the main interface
                fetch('./scriptes/Beings_admin_interface.php?action=main')
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('adminModalContent').innerHTML = data;
                    });
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
            <div class="form-group">
                <label for="specie_name">Species Name *</label>
                <input type="text" id="specie_name" name="specie_name" required>
            </div>
            
            <div class="form-group">
                <label for="specie_icon">Species Icon</label>
                <input type="file" id="specie_icon" name="specie_icon" accept="image/*">
                <small>Upload an image file (JPG, PNG, GIF, WebP). Max size: 5MB</small>
            </div>
            
            <div class="form-group">
                <label for="specie_content">Description</label>
                <textarea id="specie_content" name="specie_content" rows="5" placeholder="Enter species description..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Species</button>
            </div>
        </form>
    </div>
    
    <style>
    .form-container {
        padding: 2rem;
        max-width: 600px;
        margin: 0 auto;
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
        
        fetch('./scriptes/Beings_admin_interface.php?action=save_species', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeAdminModal();
                location.reload(); // Refresh the page to show the new species
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
            <input type="hidden" name="id" value="<?php echo $species['id_specie']; ?>">
            
            <div class="form-group">
                <label for="specie_name">Species Name *</label>
                <input type="text" id="specie_name" name="specie_name" value="<?php echo htmlspecialchars($species['specie_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="specie_icon">Species Icon</label>
                <?php if (!empty($species['icon_specie'])): ?>
                <div style="margin-bottom: 0.5rem;">
                    <img src="../images/<?php echo htmlspecialchars($species['icon_specie']); ?>" alt="Current Icon" style="width: 50px; height: 50px; border-radius: 4px;">
                    <small>Current icon</small>
                </div>
                <?php endif; ?>
                <input type="file" id="specie_icon" name="specie_icon" accept="image/*">
                <small>Upload a new image to replace the current one (JPG, PNG, GIF, WebP). Max size: 5MB</small>
            </div>
            
            <div class="form-group">
                <label for="specie_content">Description</label>
                <textarea id="specie_content" name="specie_content" rows="5"><?php echo htmlspecialchars($species['content_specie']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Species</button>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById('speciesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('./scriptes/Beings_admin_interface.php?action=save_species', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeAdminModal();
                location.reload(); // Refresh the page to show the updated species
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
    
    // Get all species for the dropdown
    $speciesQuery = $pdo->prepare("SELECT id_specie, specie_name FROM species ORDER BY specie_name");
    $speciesQuery->execute();
    $allSpecies = $speciesQuery->fetchAll(PDO::FETCH_ASSOC);
    
    ?>
    <div class="form-container">
        <h3>Add New Race</h3>
        <form id="raceForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="race_name">Race Name *</label>
                <input type="text" id="race_name" name="race_name" required>
            </div>
            
            <div class="form-group">
                <label for="correspondence">Species *</label>
                <select id="correspondence" name="correspondence" required>
                    <option value="">Select a species...</option>
                    <?php foreach ($allSpecies as $species): ?>
                    <option value="<?php echo $species['id_specie']; ?>" <?php echo $speciesId == $species['id_specie'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($species['specie_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="race_icon">Race Icon</label>
                <input type="file" id="race_icon" name="race_icon" accept="image/*">
                <small>Upload an image file (JPG, PNG, GIF, WebP). Max size: 5MB</small>
            </div>
            
            <div class="form-group">
                <label for="lifespan">Lifespan</label>
                <input type="text" id="lifespan" name="lifespan" placeholder="e.g., 100-150 years">
            </div>
            
            <div class="form-group">
                <label for="homeworld">Homeworld</label>
                <input type="text" id="homeworld" name="homeworld" placeholder="e.g., Earth, Pandora">
            </div>
            
            <div class="form-group">
                <label for="country">Country/Region</label>
                <input type="text" id="country" name="country" placeholder="e.g., Northern Kingdoms">
            </div>
            
            <div class="form-group">
                <label for="habitat">Habitat</label>
                <input type="text" id="habitat" name="habitat" placeholder="e.g., Forests, Mountains">
            </div>
            
            <div class="form-group">
                <label for="race_content">Description</label>
                <textarea id="race_content" name="race_content" rows="5" placeholder="Enter race description..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Race</button>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById('raceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('./scriptes/Beings_admin_interface.php?action=save_race', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeAdminModal();
                location.reload(); // Refresh the page to show the new race
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
    
    // Get all species for the dropdown
    $speciesQuery = $pdo->prepare("SELECT id_specie, specie_name FROM species ORDER BY specie_name");
    $speciesQuery->execute();
    $allSpecies = $speciesQuery->fetchAll(PDO::FETCH_ASSOC);
    
    ?>
    <div class="form-container">
        <h3>Edit Race: <?php echo htmlspecialchars($race['race_name']); ?></h3>
        <form id="raceForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $race['id_race']; ?>">
            
            <div class="form-group">
                <label for="race_name">Race Name *</label>
                <input type="text" id="race_name" name="race_name" value="<?php echo htmlspecialchars($race['race_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="correspondence">Species *</label>
                <select id="correspondence" name="correspondence" required>
                    <?php foreach ($allSpecies as $species): ?>
                    <option value="<?php echo $species['id_specie']; ?>" <?php echo $race['correspondence'] == $species['id_specie'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($species['specie_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="race_icon">Race Icon</label>
                <?php if (!empty($race['icon_race'])): ?>
                <div style="margin-bottom: 0.5rem;">
                    <img src="../images/<?php echo htmlspecialchars($race['icon_race']); ?>" alt="Current Icon" style="width: 50px; height: 50px; border-radius: 4px;">
                    <small>Current icon</small>
                </div>
                <?php endif; ?>
                <input type="file" id="race_icon" name="race_icon" accept="image/*">
                <small>Upload a new image to replace the current one (JPG, PNG, GIF, WebP). Max size: 5MB</small>
            </div>
            
            <div class="form-group">
                <label for="lifespan">Lifespan</label>
                <input type="text" id="lifespan" name="lifespan" value="<?php echo htmlspecialchars($race['lifespan']); ?>">
            </div>
            
            <div class="form-group">
                <label for="homeworld">Homeworld</label>
                <input type="text" id="homeworld" name="homeworld" value="<?php echo htmlspecialchars($race['homeworld']); ?>">
            </div>
            
            <div class="form-group">
                <label for="country">Country/Region</label>
                <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($race['country']); ?>">
            </div>
            
            <div class="form-group">
                <label for="habitat">Habitat</label>
                <input type="text" id="habitat" name="habitat" value="<?php echo htmlspecialchars($race['habitat']); ?>">
            </div>
            
            <div class="form-group">
                <label for="race_content">Description</label>
                <textarea id="race_content" name="race_content" rows="5"><?php echo htmlspecialchars($race['content_race']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Race</button>
            </div>
        </form>
    </div>
    
    <script>
    document.getElementById('raceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('./scriptes/Beings_admin_interface.php?action=save_race', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeAdminModal();
                location.reload(); // Refresh the page to show the updated race
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
        // Check if required POST data is present
        if (!isset($_POST['specie_name'])) {
            throw new Exception('Missing species name');
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['specie_name']);
        $content = isset($_POST['specie_content']) ? trim($_POST['specie_content']) : '';
        
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
            $sql = "UPDATE species SET specie_name = ?, content_specie = ?";
            $params = [$name, $content];
            
            if ($iconPath) {
                $sql .= ", icon_specie = ?";
                $params[] = $iconPath;
            }
            
            $sql .= " WHERE id_specie = ?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Species updated successfully']);
        } else {
            // Create new species
            $stmt = $pdo->prepare("INSERT INTO species (specie_name, content_specie, icon_specie) VALUES (?, ?, ?)");
            $stmt->execute([$name, $content, $iconPath]);
            
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
            $sql = "UPDATE races SET race_name = ?, correspondence = ?, content_race = ?, lifespan = ?, homeworld = ?, country = ?, habitat = ?";
            $params = [$name, $correspondence, $content, $lifespan, $homeworld, $country, $habitat];
            
            if ($iconPath) {
                $sql .= ", icon_race = ?";
                $params[] = $iconPath;
            }
            
            $sql .= " WHERE id_race = ?";
            $params[] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Race updated successfully']);
        } else {
            // Create new race
            $stmt = $pdo->prepare("INSERT INTO races (race_name, correspondence, content_race, lifespan, homeworld, country, habitat, icon_race) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $correspondence, $content, $lifespan, $homeworld, $country, $habitat, $iconPath]);
            
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
    
    $uploadDir = __DIR__ . '/../../images/';
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
