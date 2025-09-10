<?php
// Start output buffering to prevent headers already sent issues
ob_start();

// Start session first
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection and authentication
require_once '../blueprints/page_init.php';

// Clear any unwanted output from includes
ob_clean();

// Set JSON response header
header('Content-Type: application/json');

/**
 * Check admin authentication
 * Uses $_SESSION['user_roles'] set by page_init.php
 */
function checkAdminAuth() {
    error_log("Auth check - User roles: " . print_r($_SESSION['user_roles'] ?? 'not set', true));
    
    if (!isset($_SESSION['user_roles']) || !is_array($_SESSION['user_roles'])) {
        error_log("Auth failed - No user_roles in session or not array");
        return false;
    }
    
    // Check for both 'Admin' and 'admin' (case insensitive)
    $hasAdminRole = in_array('Admin', $_SESSION['user_roles']) || in_array('admin', $_SESSION['user_roles']);
    error_log("Auth check - Has admin role: " . ($hasAdminRole ? 'yes' : 'no'));
    
    return $hasAdminRole;
}

/**
 * Send JSON response and exit
 */
function sendResponse($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

// File upload handling functions
function generateUniqueFileName($originalName, $folder) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $basePath = __DIR__ . '/../../images/' . $folder . '/';
    
    // Generate a random number
    do {
        $randomNumber = rand(1000000, 9999999);
        $fileName = $randomNumber . '.' . $extension;
        $fullPath = $basePath . $fileName;
    } while (file_exists($fullPath)); // Keep trying until we get a unique name
    
    return $fileName;
}

function uploadImage($fileInputName, $folder) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        if ($_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // No file uploaded, which is okay
        }
        throw new Exception('File upload error: ' . $_FILES[$fileInputName]['error']);
    }
    
    $file = $_FILES[$fileInputName];
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP files are allowed.');
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File too large. Maximum size is 5MB.');
    }
    
    // Create directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../images/' . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename with original name + random number
    $randomNumber = rand(100000000, 999999999);
    $newFileName = $originalName . '_' . $randomNumber . '.' . $extension;
    
    // Check if file already exists and regenerate if needed
    $attempts = 0;
    while (file_exists($uploadDir . $newFileName) && $attempts < 10) {
        $randomNumber = rand(100000000, 999999999);
        $newFileName = $originalName . '_' . $randomNumber . '.' . $extension;
        $attempts++;
    }
    
    if ($attempts >= 10) {
        throw new Exception('Could not generate unique filename after 10 attempts.');
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
        throw new Exception('Failed to save uploaded file.');
    }
    
    // Return only the filename (not the full path)
    return $newFileName;
}

// Check if user is authenticated as admin
if (!checkAdminAuth()) {
    http_response_code(403);
    sendResponse(false, 'Admin authentication required. Current roles: ' . 
        (isset($_SESSION['user_roles']) ? implode(', ', $_SESSION['user_roles']) : 'none'));
}

$action = $_GET['action'] ?? '';

// If no action or action is 'main', display the admin interface HTML
if (empty($action) || $action === 'main') {
    // Clear the JSON header and buffer since we're serving HTML
    ob_clean();
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Beings Admin Interface</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .admin-container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
            .tab-container { border-bottom: 2px solid #ddd; margin-bottom: 20px; }
            .tab-btn { background: none; border: none; padding: 1rem 1.5rem; cursor: pointer; font-size: 1rem; color: #6c757d; border-bottom: 3px solid transparent; transition: all 0.3s ease; }
            .tab-btn.active { color: #222088; border-bottom-color: #222088; }
            .tab-btn:hover { background: #f8f9fa; }
            .tab-content { display: none; }
            .tab-content.active { display: block; }
            .tab-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
            .btn-primary { background: #222088; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; font-size: 1rem; transition: all 0.3s ease; }
            .btn-primary:hover { background: #1a1066; }
            .form-group { margin-bottom: 1rem; }
            .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
            .form-group input, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
            .form-group textarea { min-height: 120px; resize: vertical; }
            .entities-list { display: grid; gap: 1rem; }
            .entity-card { background: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid #222088; }
            .entity-name { font-size: 1.1rem; font-weight: bold; margin-bottom: 0.5rem; }
            .entity-description { color: #6c757d; margin-bottom: 1rem; }
            .entity-actions { display: flex; gap: 0.5rem; }
            .btn-edit { background: #17a2b8; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
            .btn-delete { background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
            .success { color: #28a745; margin: 1rem 0; }
            .error { color: #dc3545; margin: 1rem 0; }
            
            /* Edit Modal Styles */
            .edit-popup-modal {
                display: none;
                position: fixed;
                z-index: 2000; /* Higher than main modal */
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.6);
            }
            
            .edit-modal-content {
                background-color: #fefefe;
                margin: 5% auto;
                padding: 0;
                border: none;
                border-radius: 12px;
                width: 90%;
                max-width: 600px;
                max-height: 85vh;
                overflow: hidden;
                box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            }
            
            .edit-modal-content .modal-header {
                background: linear-gradient(135deg, #222088, #1a1066);
                color: white;
                padding: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0;
            }
            
            .edit-modal-content .modal-header h2 {
                margin: 0;
                font-size: 1.5rem;
            }
            
            .edit-modal-content .close {
                color: white;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                line-height: 1;
                opacity: 0.8;
                transition: opacity 0.3s ease;
            }
            
            .edit-modal-content .close:hover {
                opacity: 1;
            }
            
            #editModalBody {
                padding: 25px;
                max-height: calc(85vh - 80px);
                overflow-y: auto;
            }
            
            .edit-form-container {
                background: white;
            }
            
            .edit-form-container .form-group {
                margin-bottom: 1.5rem;
            }
            
            .edit-form-container .form-group label {
                font-size: 1rem;
                color: #333;
                margin-bottom: 0.5rem;
            }
            
            .edit-form-actions {
                margin-top: 2rem;
                padding-top: 1.5rem;
                border-top: 1px solid #eee;
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            }
            
            .edit-form-actions .btn-primary {
                padding: 0.75rem 2rem;
            }
            
            .edit-form-actions .btn-secondary {
                background: #6c757d;
                color: white;
                border: none;
                padding: 0.75rem 2rem;
                border-radius: 8px;
                cursor: pointer;
                font-size: 1rem;
                transition: all 0.3s ease;
            }
            
            .edit-form-actions .btn-secondary:hover {
                background: #5a6268;
            }
        </style>
    </head>
    <body>
        <div class="admin-container">
            <h1>Beings Administration</h1>
            <p><a href="../Beings.php">← Back to Beings Page</a></p>
            
            <div class="tab-container">
                <button class="tab-btn active" onclick="switchTab('species')">Species Management</button>
                <button class="tab-btn" onclick="switchTab('races')">Races Management</button>
            </div>
            
            <!-- Species Management Tab -->
            <div id="species-tab" class="tab-content active">
                <div class="tab-header">
                    <h2>Species Management</h2>
                    <button class="btn-primary" onclick="showAddSpeciesForm()">Add New Species</button>
                </div>
                
                <div id="add-species-form" style="display: none;">
                    <h3>Add New Species</h3>
                    <form id="species-form">
                        <div class="form-group">
                            <label>Species Name:</label>
                            <input type="text" name="specie_name" required>
                        </div>
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea name="specie_content"></textarea>
                        </div>
                        <button type="submit" class="btn-primary">Save Species</button>
                        <button type="button" onclick="hideAddSpeciesForm()">Cancel</button>
                    </form>
                </div>
                
                <div id="species-list" class="entities-list">
                    <p>Loading species...</p>
                </div>
            </div>
            
            <!-- Races Management Tab -->
            <div id="races-tab" class="tab-content">
                <div class="tab-header">
                    <h2>Races Management</h2>
                    <button class="btn-primary" onclick="showAddRaceForm()">Add New Race</button>
                </div>
                
                <div id="add-race-form" style="display: none;">
                    <h3>Add New Race</h3>
                    <form id="race-form">
                        <div class="form-group">
                            <label>Race Name:</label>
                            <input type="text" name="race_name" required>
                        </div>
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea name="race_content"></textarea>
                        </div>
                        <button type="submit" class="btn-primary">Save Race</button>
                        <button type="button" onclick="hideAddRaceForm()">Cancel</button>
                    </form>
                </div>
                
                <div id="races-list" class="entities-list">
                    <p>Loading races...</p>
                </div>
            </div>
        </div>
        
        <div id="messages"></div>
        
        <script>
            // Tab switching functionality
            function switchTab(tabName) {
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                event.target.classList.add('active');
                document.getElementById(tabName + '-tab').classList.add('active');
                
                if (tabName === 'species') loadSpecies();
                if (tabName === 'races') loadRaces();
            }
            
            function switchModalTab(tabName) {
                document.querySelectorAll('.modal-tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.modal-tab-content').forEach(content => content.classList.remove('active'));
                
                event.target.classList.add('active');
                document.getElementById('modal-' + tabName + '-tab').classList.add('active');
                
                // Load data when switching tabs in modal
                if (window.beingsManager) {
                    if (tabName === 'species') window.beingsManager.loadModalSpecies();
                    if (tabName === 'races') window.beingsManager.loadModalRaces();
                }
            }
            
            // Form management
            function showAddSpeciesForm() {
                document.getElementById('add-species-form').style.display = 'block';
            }
            
            function hideAddSpeciesForm() {
                document.getElementById('add-species-form').style.display = 'none';
                document.getElementById('species-form').reset();
            }
            
            function showAddRaceForm() {
                document.getElementById('add-race-form').style.display = 'block';
            }
            
            function hideAddRaceForm() {
                document.getElementById('add-race-form').style.display = 'none';
                document.getElementById('race-form').reset();
            }
            
            // Image preview function
            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                const file = input.files[0];
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Image preview">`;
                        preview.classList.add('has-image');
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = '';
                    preview.classList.remove('has-image');
                }
            }
            
            // API calls
            function loadSpecies() {
                fetch('?action=get_species')
                    .then(response => response.json())
                    .then(data => {
                        const list = document.getElementById('species-list');
                        if (data.success) {
                            list.innerHTML = data.data.map(species => `
                                <div class="entity-card">
                                    <div class="entity-name">${species.specie_name}</div>
                                    <div class="entity-description">${species.content_specie || 'No description'}</div>
                                    <div class="entity-actions">
                                        <button class="btn-edit" onclick="editSpecies(${species.id_specie})">Edit</button>
                                        <button class="btn-delete" onclick="deleteSpecies(${species.id_specie})">Delete</button>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            list.innerHTML = `<p class="error">${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        document.getElementById('species-list').innerHTML = `<p class="error">Error: ${error.message}</p>`;
                    });
            }
            
            function loadRaces() {
                fetch('?action=get_races')
                    .then(response => response.json())
                    .then(data => {
                        const list = document.getElementById('races-list');
                        if (data.success) {
                            list.innerHTML = data.data.map(race => `
                                <div class="entity-card">
                                    <div class="entity-name">${race.race_name}</div>
                                    <div class="entity-description">${race.content_race || 'No description'}</div>
                                    <div class="entity-actions">
                                        <button class="btn-edit" onclick="editRace(${race.id_race})">Edit</button>
                                        <button class="btn-delete" onclick="deleteRace(${race.id_race})">Delete</button>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            list.innerHTML = `<p class="error">${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        document.getElementById('races-list').innerHTML = `<p class="error">Error: ${error.message}</p>`;
                    });
            }
            
            // Form submissions
            document.getElementById('species-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('?action=save_species', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        hideAddSpeciesForm();
                        loadSpecies();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Error: ' + error.message, 'error');
                });
            });
            
            document.getElementById('race-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('?action=save_race', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        hideAddRaceForm();
                        loadRaces();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Error: ' + error.message, 'error');
                });
            });
            
            function showMessage(message, type) {
                const messagesDiv = document.getElementById('messages');
                messagesDiv.innerHTML = `<p class="${type}">${message}</p>`;
                setTimeout(() => messagesDiv.innerHTML = '', 3000);
            }
            
            function deleteSpecies(id) {
                if (confirm('Are you sure you want to delete this species?')) {
                    const formData = new FormData();
                    formData.append('specie_id', id);
                    
                    fetch('?action=delete_species', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.message, data.success ? 'success' : 'error');
                        if (data.success) loadSpecies();
                    });
                }
            }
            
            function deleteRace(id) {
                if (confirm('Are you sure you want to delete this race?')) {
                    const formData = new FormData();
                    formData.append('race_id', id);
                    
                    fetch('?action=delete_race', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.message, data.success ? 'success' : 'error');
                        if (data.success) loadRaces();
                    });
                }
            }
            
            // Modal functions
            function showModalAddSpeciesForm() {
                document.getElementById('modal-add-species-form').style.display = 'block';
            }
            
            function hideModalAddSpeciesForm() {
                document.getElementById('modal-add-species-form').style.display = 'none';
                document.getElementById('speciesForm').reset();
                document.getElementById('species-preview').innerHTML = '';
                document.getElementById('species-preview').classList.remove('has-image');
            }
            
            function showModalAddRaceForm() {
                document.getElementById('modal-add-race-form').style.display = 'block';
                loadSpeciesForDropdown();
            }
            
            function hideModalAddRaceForm() {
                document.getElementById('modal-add-race-form').style.display = 'none';
                document.getElementById('raceForm').reset();
                document.getElementById('race-preview').innerHTML = '';
                document.getElementById('race-preview').classList.remove('has-image');
            }
            
            function loadSpeciesForDropdown() {
                fetch('?action=get_species_for_dropdown')
                    .then(response => response.json())
                    .then(data => {
                        const select = document.getElementById('species-select');
                        if (data.success && select) {
                            select.innerHTML = '<option value="">Select a species...</option>';
                            data.data.forEach(species => {
                                select.innerHTML += `<option value="${species.id_specie}">${species.specie_name}</option>`;
                            });
                        }
                    })
                    .catch(error => console.error('Error loading species:', error));
            }
            
            // Edit form functions
            function showModalEditSpeciesForm() {
                document.getElementById('modal-edit-species-form').style.display = 'block';
            }
            
            function hideModalEditSpeciesForm() {
                document.getElementById('modal-edit-species-form').style.display = 'none';
                document.getElementById('editSpeciesForm').reset();
                document.getElementById('edit-species-preview').innerHTML = '';
                document.getElementById('edit-species-preview').classList.remove('has-image');
                document.getElementById('current-species-img').style.display = 'none';
            }
            
            function showModalEditRaceForm() {
                document.getElementById('modal-edit-race-form').style.display = 'block';
                loadSpeciesForEditDropdown();
            }
            
            function hideModalEditRaceForm() {
                document.getElementById('modal-edit-race-form').style.display = 'none';
                document.getElementById('editRaceForm').reset();
                document.getElementById('edit-race-preview').innerHTML = '';
                document.getElementById('edit-race-preview').classList.remove('has-image');
                document.getElementById('current-race-img').style.display = 'none';
            }
            
            function loadSpeciesForEditDropdown() {
                fetch('?action=get_species_for_dropdown')
                    .then(response => response.json())
                    .then(data => {
                        const select = document.getElementById('edit-species-select');
                        if (data.success && select) {
                            select.innerHTML = '<option value="">Select a species...</option>';
                            data.data.forEach(species => {
                                select.innerHTML += `<option value="${species.id_specie}">${species.specie_name}</option>`;
                            });
                        }
                    })
                    .catch(error => console.error('Error loading species:', error));
            }
            
            // Global functions for modal management (called from HTML onclick)
            function closeEditModal() {
                if (window.beingsManager) {
                    window.beingsManager.closeEditModal();
                } else {
                    document.getElementById('editModal').style.display = 'none';
                    document.getElementById('editModalBody').innerHTML = '';
                }
            }
            
            // Close modal when clicking outside of it
            window.addEventListener('click', function(event) {
                const editModal = document.getElementById('editModal');
                if (event.target === editModal) {
                    closeEditModal();
                }
            });
            
            // Edit functions (called from edit buttons)
            function editModalSpecies(id) {
                // Hide other forms
                document.getElementById('modal-add-species-form').style.display = 'none';
                document.getElementById('modal-add-race-form').style.display = 'none';
                document.getElementById('modal-edit-race-form').style.display = 'none';
                
                // Fetch species data
                fetch(`?action=get_species_by_id&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            const species = data.data;
                            
                            // Fill form fields
                            document.getElementById('edit-specie-id').value = species.id_specie;
                            document.getElementById('edit-specie-name').value = species.specie_name || '';
                            document.getElementById('edit-specie-content').value = species.content_specie || '';
                            document.getElementById('edit-specie-lifespan').value = species.lifespan || '';
                            document.getElementById('edit-specie-homeworld').value = species.homeworld || '';
                            document.getElementById('edit-specie-country').value = species.country || '';
                            document.getElementById('edit-specie-habitat').value = species.habitat || '';
                            
                            // Show current image if exists
                            const currentImg = document.getElementById('current-species-img');
                            if (species.icon_specie) {
                                currentImg.src = `../images/species/${species.icon_specie}`;
                                currentImg.style.display = 'block';
                            } else {
                                currentImg.style.display = 'none';
                            }
                            
                            // Show edit form
                            showModalEditSpeciesForm();
                        } else {
                            showMessage(data.message || 'Failed to load species data', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading species for edit:', error);
                        showMessage('Error loading species data', 'error');
                    });
            }
            
            function editModalRace(id) {
                // Hide other forms
                document.getElementById('modal-add-species-form').style.display = 'none';
                document.getElementById('modal-add-race-form').style.display = 'none';
                document.getElementById('modal-edit-species-form').style.display = 'none';
                
                // Fetch race data
                fetch(`?action=get_race_by_id&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            const race = data.data;
                            
                            // Fill form fields
                            document.getElementById('edit-race-id').value = race.id_race;
                            document.getElementById('edit-race-name').value = race.race_name || '';
                            document.getElementById('edit-race-content').value = race.content_race || '';
                            
                            // Load species dropdown and set current selection
                            loadSpeciesForEditDropdown();
                            setTimeout(() => {
                                document.getElementById('edit-species-select').value = race.correspondence || '';
                            }, 100);
                            
                            // Show current image if exists
                            const currentImg = document.getElementById('current-race-img');
                            if (race.icon_race) {
                                currentImg.src = `../images/races/${race.icon_race}`;
                                currentImg.style.display = 'block';
                            } else {
                                currentImg.style.display = 'none';
                            }
                            
                            // Show edit form
                            showModalEditRaceForm();
                        } else {
                            showMessage(data.message || 'Failed to load race data', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading race for edit:', error);
                        showMessage('Error loading race data', 'error');
                    });
            }
            
            // Modal form event handlers
            document.getElementById('speciesForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('?action=save_species', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        hideModalAddSpeciesForm();
                        loadSpecies();
                    }
                })
                .catch(error => {
                    showMessage('Error: ' + error.message, 'error');
                });
            });
            
            document.getElementById('raceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('?action=save_race', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        hideModalAddRaceForm();
                        loadRaces();
                    }
                })
                .catch(error => {
                    showMessage('Error: ' + error.message, 'error');
                });
            });
            
            // Edit form event handlers
            document.getElementById('editSpeciesForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('?action=edit_species', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        hideModalEditSpeciesForm();
                        loadSpecies();
                    }
                })
                .catch(error => {
                    showMessage('Error: ' + error.message, 'error');
                });
            });
            
            document.getElementById('editRaceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('?action=edit_race', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        hideModalEditRaceForm();
                        loadRaces();
                    }
                })
                .catch(error => {
                    showMessage('Error: ' + error.message, 'error');
                });
            });
            
            // Load initial data
            loadSpecies();
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Modal content for AJAX loading (just the inner content, no full HTML page)
if ($action === 'modal') {
    // Clear the JSON header and buffer since we're serving HTML
    ob_clean();
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <div class="modal-admin-content">
        <style>
            .modal-admin-content {
                padding: 20px;
                font-family: Arial, sans-serif;
            }
            
            .modal-tabs {
                border-bottom: 2px solid #ddd;
                margin-bottom: 20px;
                display: flex;
                gap: 0;
            }
            
            .modal-tab-btn {
                background: none;
                border: none;
                padding: 1rem 1.5rem;
                cursor: pointer;
                font-size: 1rem;
                color: #6c757d;
                border-bottom: 3px solid transparent;
                transition: all 0.3s ease;
            }
            
            .modal-tab-btn.active {
                color: #222088;
                border-bottom-color: #222088;
            }
            
            .modal-tab-btn:hover {
                background: #f8f9fa;
            }
            
            .modal-tab-content {
                display: none;
            }
            
            .modal-tab-content.active {
                display: block;
            }
            
            .tab-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
            }
            
            .tab-header h3 {
                margin: 0;
                color: #333;
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
            
            .form-group {
                margin-bottom: 1rem;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: bold;
                color: #333;
            }
            
            .form-group input, .form-group textarea, .form-group select {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 1rem;
                box-sizing: border-box;
            }
            
            .form-group textarea {
                min-height: 120px;
                resize: vertical;
            }
            
            .entities-list {
                display: grid;
                gap: 1rem;
                margin-top: 1rem;
            }
            
            .entity-card {
                background: #f8f9fa;
                padding: 1rem;
                border-radius: 8px;
                border-left: 4px solid #222088;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .entity-name {
                font-size: 1.1rem;
                font-weight: bold;
                margin-bottom: 0.5rem;
                color: #333;
            }
            
            .entity-description {
                color: #6c757d;
                margin-bottom: 1rem;
                line-height: 1.4;
            }
            
            .entity-detail {
                color: #495057;
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
                padding: 0.25rem 0;
            }
            
            .entity-icon {
                width: 40px;
                height: 40px;
                object-fit: cover;
                border-radius: 4px;
                margin: 0.5rem 0;
                border: 1px solid #ddd;
            }
            
            .image-preview {
                margin-top: 10px;
                max-width: 200px;
                max-height: 200px;
                border: 2px dashed #ddd;
                border-radius: 4px;
                padding: 10px;
                text-align: center;
                display: none;
            }
            
            .image-preview img {
                max-width: 100%;
                max-height: 180px;
                object-fit: contain;
                border-radius: 4px;
            }
            
            .image-preview.has-image {
                display: block;
                border-color: #222088;
                background: #f8f9fa;
            }
            
            .entity-actions {
                display: flex;
                gap: 0.5rem;
            }
            
            .btn-edit {
                background: #17a2b8;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9rem;
            }
            
            .btn-edit:hover {
                background: #138496;
            }
            
            .btn-delete {
                background: #dc3545;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9rem;
            }
            
            .btn-delete:hover {
                background: #c82333;
            }
            
            .success {
                color: #28a745;
                margin: 1rem 0;
                padding: 0.5rem;
                background: #d4edda;
                border-radius: 4px;
            }
            
            .error {
                color: #dc3545;
                margin: 1rem 0;
                padding: 0.5rem;
                background: #f8d7da;
                border-radius: 4px;
            }
            
            #modal-add-species-form, #modal-add-race-form {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
            
            #modal-add-species-form h4, #modal-add-race-form h4 {
                margin-top: 0;
                color: #333;
            }
            
            #modal-add-species-form button, #modal-add-race-form button {
                margin-right: 1rem;
            }
            
            button[type="button"] {
                background: #6c757d;
                color: white;
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                cursor: pointer;
                font-size: 1rem;
            }
            
            button[type="button"]:hover {
                background: #545b62;
            }
        </style>
        
        <h2>Beings Administration</h2>
        
        <div class="modal-tabs">
            <button class="modal-tab-btn active" onclick="switchModalTab('species')">Species Management</button>
            <button class="modal-tab-btn" onclick="switchModalTab('races')">Races Management</button>
        </div>
        
        <!-- Species Management Tab -->
        <div id="modal-species-tab" class="modal-tab-content active">
            <div class="tab-header">
                <h3>Species Management</h3>
                <button class="btn-primary" onclick="showModalAddSpeciesForm()">Add New Species</button>
            </div>
            
            <div id="modal-add-species-form" style="display: none;">
                <h4>Add New Species</h4>
                <form id="speciesForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Species Name: <span style="color: red;">*</span></label>
                        <input type="text" name="specie_name" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="content_specie" placeholder="Optional description of the species"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Icon/Image:</label>
                        <input type="file" name="icon_specie" accept="image/*" onchange="previewImage(this, 'species-preview')">
                        <div id="species-preview" class="image-preview"></div>
                    </div>
                    <div class="form-group">
                        <label>Lifespan:</label>
                        <input type="text" name="lifespan" placeholder="e.g., 80-100 years">
                    </div>
                    <div class="form-group">
                        <label>Homeworld:</label>
                        <input type="text" name="homeworld" placeholder="Planet or realm of origin">
                    </div>
                    <div class="form-group">
                        <label>Country/Region:</label>
                        <input type="text" name="country" placeholder="Specific country or region">
                    </div>
                    <div class="form-group">
                        <label>Habitat:</label>
                        <input type="text" name="habitat" placeholder="Preferred living environment">
                    </div>
                    <button type="submit" class="btn-primary">Save Species</button>
                    <button type="button" onclick="hideModalAddSpeciesForm()">Cancel</button>
                </form>
            </div>
            
            <div id="modal-species-list" class="entities-list">
                <p>Loading species...</p>
            </div>
        </div>
        
        <!-- Races Management Tab -->
        <div id="modal-races-tab" class="modal-tab-content">
            <div class="tab-header">
                <h3>Races Management</h3>
                <button class="btn-primary" onclick="showModalAddRaceForm()">Add New Race</button>
            </div>
            
            <div id="modal-add-race-form" style="display: none;">
                <h4>Add New Race</h4>
                <form id="raceForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Race Name: <span style="color: red;">*</span></label>
                        <input type="text" name="race_name" required>
                    </div>
                    <div class="form-group">
                        <label>Species: <span style="color: red;">*</span></label>
                        <select name="correspondence" required id="species-select">
                            <option value="">Select a species...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="content_race" placeholder="Optional description of the race"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Icon/Image:</label>
                        <input type="file" name="icon_race" accept="image/*" onchange="previewImage(this, 'race-preview')">
                        <div id="race-preview" class="image-preview"></div>
                    </div>
                    <button type="submit" class="btn-primary">Save Race</button>
                    <button type="button" onclick="hideModalAddRaceForm()">Cancel</button>
                </form>
            </div>
            
            <div id="modal-races-list" class="entities-list">
                <p>Loading races...</p>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal Popup -->
    <div id="editModal" class="modal edit-popup-modal" style="display: none;">
        <div class="modal-content edit-modal-content">
            <div class="modal-header">
                <h2 id="editModalTitle">Edit</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div id="editModalBody">
                <!-- Edit forms will be loaded here -->
            </div>
        </div>
    </div>
    
    <script>
        // Initialize modal data when loaded - with better timing
        function initializeModalData() {
            if (window.beingsManager && window.beingsManager.loadModalSpecies) {
                console.log('Initializing modal with species data...');
                window.beingsManager.loadModalSpecies();
            } else {
                console.log('BeingsManager not ready, retrying in 200ms...');
                setTimeout(initializeModalData, 200);
            }
        }
        
        // Start initialization
        initializeModalData();
    </script>
    <?php
    exit;
}

try {
    switch ($action) {
        case 'save_species':
            try {
                $name = trim($_POST['specie_name'] ?? '');
                $content = trim($_POST['content_specie'] ?? '');
                $lifespan = trim($_POST['lifespan'] ?? '');
                $homeworld = trim($_POST['homeworld'] ?? '');
                $country = trim($_POST['country'] ?? '');
                $habitat = trim($_POST['habitat'] ?? '');
                
                if (empty($name)) {
                    sendResponse(false, 'Species name is required');
                }
                
                // Check if species already exists
                $checkStmt = $pdo->prepare("SELECT id_specie FROM species WHERE specie_name = ?");
                $checkStmt->execute([$name]);
                
                if ($checkStmt->fetch()) {
                    sendResponse(false, 'A species with this name already exists');
                }
                
                // Handle file upload
                $iconPath = null;
                if (isset($_FILES['icon_specie']) && $_FILES['icon_specie']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $iconPath = uploadImage('icon_specie', 'species');
                }
                
                // Insert new species
                $stmt = $pdo->prepare("INSERT INTO species (specie_name, content_specie, icon_specie, lifespan, homeworld, country, habitat) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$name, $content ?: null, $iconPath, $lifespan ?: null, $homeworld ?: null, $country ?: null, $habitat ?: null]);
                
                if ($result) {
                    sendResponse(true, 'Species saved successfully', ['id' => $pdo->lastInsertId()]);
                } else {
                    sendResponse(false, 'Failed to save species');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error: ' . $e->getMessage());
            }
            break;
            
        case 'save_race':
            try {
                $name = trim($_POST['race_name'] ?? '');
                $content = trim($_POST['content_race'] ?? '');
                $correspondence = intval($_POST['correspondence'] ?? 0);
                
                if (empty($name)) {
                    sendResponse(false, 'Race name is required');
                }
                
                if ($correspondence <= 0) {
                    sendResponse(false, 'Species selection is required');
                }
                
                // Verify species exists
                $speciesCheck = $pdo->prepare("SELECT id_specie FROM species WHERE id_specie = ?");
                $speciesCheck->execute([$correspondence]);
                if (!$speciesCheck->fetch()) {
                    sendResponse(false, 'Selected species does not exist');
                }
                
                // Check if race already exists
                $checkStmt = $pdo->prepare("SELECT id_race FROM races WHERE race_name = ?");
                $checkStmt->execute([$name]);
                
                if ($checkStmt->fetch()) {
                    sendResponse(false, 'A race with this name already exists');
                }
                
                // Handle file upload
                $iconPath = null;
                if (isset($_FILES['icon_race']) && $_FILES['icon_race']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $iconPath = uploadImage('icon_race', 'races');
                }
                
                // Insert new race
                $stmt = $pdo->prepare("INSERT INTO races (race_name, content_race, icon_race, correspondence) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([$name, $content ?: null, $iconPath, $correspondence]);
                
                if ($result) {
                    sendResponse(true, 'Race saved successfully', ['id' => $pdo->lastInsertId()]);
                } else {
                    sendResponse(false, 'Failed to save race');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error: ' . $e->getMessage());
            }
            break;
            
        case 'get_species':
            $stmt = $pdo->prepare("SELECT id_specie, specie_name, content_specie, icon_specie, lifespan, homeworld, country, habitat, created_at FROM species ORDER BY specie_name");
            $stmt->execute();
            $species = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(true, 'Species retrieved successfully', $species);
            break;
            
        case 'get_species_by_id':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                sendResponse(false, 'Invalid species ID');
            }
            
            $stmt = $pdo->prepare("SELECT id_specie, specie_name, content_specie, icon_specie, lifespan, homeworld, country, habitat, created_at FROM species WHERE id_specie = ?");
            $stmt->execute([$id]);
            $species = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($species) {
                sendResponse(true, 'Species retrieved successfully', $species);
            } else {
                sendResponse(false, 'Species not found');
            }
            break;
            
        case 'get_races':
            $stmt = $pdo->prepare("SELECT r.id_race, r.race_name, r.content_race, r.icon_race, r.correspondence, r.created_at, s.specie_name 
                                   FROM races r 
                                   LEFT JOIN species s ON r.correspondence = s.id_specie 
                                   ORDER BY s.specie_name, r.race_name");
            $stmt->execute();
            $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(true, 'Races retrieved successfully', $races);
            break;
            
        case 'get_race_by_id':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                sendResponse(false, 'Invalid race ID');
            }
            
            $stmt = $pdo->prepare("SELECT r.id_race, r.race_name, r.content_race, r.icon_race, r.correspondence, r.created_at, s.specie_name 
                                   FROM races r 
                                   LEFT JOIN species s ON r.correspondence = s.id_specie 
                                   WHERE r.id_race = ?");
            $stmt->execute([$id]);
            $race = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($race) {
                sendResponse(true, 'Race retrieved successfully', $race);
            } else {
                sendResponse(false, 'Race not found');
            }
            break;
            
        case 'get_species_for_dropdown':
            $stmt = $pdo->prepare("SELECT id_specie, specie_name FROM species ORDER BY specie_name");
            $stmt->execute();
            $species = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(true, 'Species for dropdown retrieved successfully', $species);
            break;
            
        case 'edit_species':
            try {
                $id = intval($_POST['specie_id'] ?? 0);
                $name = trim($_POST['specie_name'] ?? '');
                $content = trim($_POST['content_specie'] ?? '');
                $lifespan = trim($_POST['lifespan'] ?? '');
                $homeworld = trim($_POST['homeworld'] ?? '');
                $country = trim($_POST['country'] ?? '');
                $habitat = trim($_POST['habitat'] ?? '');
                
                if ($id <= 0) {
                    sendResponse(false, 'Invalid species ID');
                }
                
                if (empty($name)) {
                    sendResponse(false, 'Species name is required');
                }
                
                // Check if another species has this name
                $checkStmt = $pdo->prepare("SELECT id_specie FROM species WHERE specie_name = ? AND id_specie != ?");
                $checkStmt->execute([$name, $id]);
                
                if ($checkStmt->fetch()) {
                    sendResponse(false, 'Another species with this name already exists');
                }
                
                // Get current species data to preserve existing icon if no new one uploaded
                $currentStmt = $pdo->prepare("SELECT icon_specie FROM species WHERE id_specie = ?");
                $currentStmt->execute([$id]);
                $currentData = $currentStmt->fetch(PDO::FETCH_ASSOC);
                $currentIcon = $currentData['icon_specie'] ?? null;
                
                // Handle file upload
                $iconPath = $currentIcon; // Keep existing icon by default
                if (isset($_FILES['icon_specie']) && $_FILES['icon_specie']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $newIconPath = uploadImage('icon_specie', 'species');
                    if ($newIconPath) {
                        // Delete old icon if exists and is different
                        if ($currentIcon && $currentIcon !== $newIconPath) {
                            $oldIconPath = __DIR__ . '/../../images/species/' . $currentIcon;
                            if (file_exists($oldIconPath)) {
                                unlink($oldIconPath);
                            }
                        }
                        $iconPath = $newIconPath;
                    }
                }
                
                // Update species
                $stmt = $pdo->prepare("UPDATE species SET specie_name = ?, content_specie = ?, icon_specie = ?, lifespan = ?, homeworld = ?, country = ?, habitat = ? WHERE id_specie = ?");
                $result = $stmt->execute([$name, $content ?: null, $iconPath, $lifespan ?: null, $homeworld ?: null, $country ?: null, $habitat ?: null, $id]);
                
                if ($result) {
                    sendResponse(true, 'Species updated successfully');
                } else {
                    sendResponse(false, 'Failed to update species');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error: ' . $e->getMessage());
            }
            break;
            
        case 'edit_race':
            try {
                $id = intval($_POST['race_id'] ?? 0);
                $name = trim($_POST['race_name'] ?? '');
                $content = trim($_POST['content_race'] ?? '');
                $correspondence = intval($_POST['correspondence'] ?? 0);
                
                if ($id <= 0) {
                    sendResponse(false, 'Invalid race ID');
                }
                
                if (empty($name)) {
                    sendResponse(false, 'Race name is required');
                }
                
                if ($correspondence <= 0) {
                    sendResponse(false, 'Species selection is required');
                }
                
                // Verify species exists
                $speciesCheck = $pdo->prepare("SELECT id_specie FROM species WHERE id_specie = ?");
                $speciesCheck->execute([$correspondence]);
                if (!$speciesCheck->fetch()) {
                    sendResponse(false, 'Selected species does not exist');
                }
                
                // Check if another race has this name
                $checkStmt = $pdo->prepare("SELECT id_race FROM races WHERE race_name = ? AND id_race != ?");
                $checkStmt->execute([$name, $id]);
                
                if ($checkStmt->fetch()) {
                    sendResponse(false, 'Another race with this name already exists');
                }
                
                // Get current race data to preserve existing icon if no new one uploaded
                $currentStmt = $pdo->prepare("SELECT icon_race FROM races WHERE id_race = ?");
                $currentStmt->execute([$id]);
                $currentData = $currentStmt->fetch(PDO::FETCH_ASSOC);
                $currentIcon = $currentData['icon_race'] ?? null;
                
                // Handle file upload
                $iconPath = $currentIcon; // Keep existing icon by default
                if (isset($_FILES['icon_race']) && $_FILES['icon_race']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $newIconPath = uploadImage('icon_race', 'races');
                    if ($newIconPath) {
                        // Delete old icon if exists and is different
                        if ($currentIcon && $currentIcon !== $newIconPath) {
                            $oldIconPath = __DIR__ . '/../../images/races/' . $currentIcon;
                            if (file_exists($oldIconPath)) {
                                unlink($oldIconPath);
                            }
                        }
                        $iconPath = $newIconPath;
                    }
                }
                
                // Update race
                $stmt = $pdo->prepare("UPDATE races SET race_name = ?, content_race = ?, icon_race = ?, correspondence = ? WHERE id_race = ?");
                $result = $stmt->execute([$name, $content ?: null, $iconPath, $correspondence, $id]);
                
                if ($result) {
                    sendResponse(true, 'Race updated successfully');
                } else {
                    sendResponse(false, 'Failed to update race');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error: ' . $e->getMessage());
            }
            break;
            
        case 'delete_species':
            $id = intval($_POST['specie_id'] ?? 0);
            
            if ($id <= 0) {
                sendResponse(false, 'Invalid species ID');
            }
            
            $stmt = $pdo->prepare("DELETE FROM species WHERE id_specie = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                sendResponse(true, 'Species deleted successfully');
            } else {
                sendResponse(false, 'Failed to delete species');
            }
            break;
            
        case 'delete_race':
            $id = intval($_POST['race_id'] ?? 0);
            
            if ($id <= 0) {
                sendResponse(false, 'Invalid race ID');
            }
            
            $stmt = $pdo->prepare("DELETE FROM races WHERE id_race = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                sendResponse(true, 'Race deleted successfully');
            } else {
                sendResponse(false, 'Failed to delete race');
            }
            break;
        
        case 'get_species_races':
            $speciesId = $_GET['species_id'] ?? '';
            if (empty($speciesId)) {
                sendResponse(false, 'Species ID is required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM races WHERE correspondence = ? ORDER BY race_name");
                $stmt->execute([$speciesId]);
                $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                sendResponse(true, 'Races loaded successfully', ['races' => $races]);
            } catch (Exception $e) {
                sendResponse(false, 'Failed to load races: ' . $e->getMessage());
            }
            break;
        
        case 'get_race_characters':
            $raceId = $_GET['race_id'] ?? '';
            if (empty($raceId)) {
                sendResponse(false, 'Race ID is required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM characters WHERE correspondence = ? ORDER BY character_name");
                $stmt->execute([$raceId]);
                $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                sendResponse(true, 'Characters loaded successfully', ['characters' => $characters]);
            } catch (Exception $e) {
                sendResponse(false, 'Failed to load characters: ' . $e->getMessage());
            }
            break;
        
        default:
            sendResponse(false, 'Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    error_log("Beings Admin Interface Error: " . $e->getMessage());
    sendResponse(false, 'Server error: ' . $e->getMessage());
}
?>
