/**
 * Beings Page JavaScript Functions
 * Handles all interactive functionality for the Beings page
 */

class BeingsManager {
    constructor(config = {}) {
        this.apiEndpoint = config.apiEndpoint || 'scriptes/Beings_admin_interface.php';
        this.isAdmin = config.isAdmin || false;
        this.init();
    }
    
    init() {
        // Initialize modal close handlers
        this.initModalHandlers();
        // Initialize form submission handlers using event delegation
        this.initFormHandlers();
    }
    
    initModalHandlers() {
        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            const modal = document.getElementById('adminModal');
            if (event.target === modal) {
                this.closeAdminModal();
            }
        });
    }
    
    initFormHandlers() {
        // Use event delegation to handle form submissions in the modal
        const modal = document.getElementById('adminModal');
        if (modal) {
            modal.addEventListener('submit', (event) => {
                const form = event.target;
                
                // Handle species forms
                if (form.id === 'speciesForm') {
                    event.preventDefault();
                    console.log('Species form submission intercepted via event delegation');
                    this.handleSpeciesFormSubmission(form);
                }
                
                // Handle race forms
                if (form.id === 'raceForm') {
                    event.preventDefault();
                    console.log('Race form submission intercepted via event delegation');
                    this.handleRaceFormSubmission(form);
                }
                
                // Handle edit species forms
                if (form.id === 'editSpeciesForm') {
                    event.preventDefault();
                    console.log('Edit species form submission intercepted via event delegation');
                    this.handleEditSpeciesFormSubmission(form);
                }
                
                // Handle edit race forms
                if (form.id === 'editRaceForm') {
                    event.preventDefault();
                    console.log('Edit race form submission intercepted via event delegation');
                    this.handleEditRaceFormSubmission(form);
                }
            });
            console.log('Event delegation for modal forms initialized');
        } else {
            console.error('Admin modal not found for event delegation');
        }
    }
    
    async handleSpeciesFormSubmission(form) {
        try {
            const formData = new FormData(form);
            
            console.log('Submitting species form data...');
            const response = await fetch(`${this.apiEndpoint}?action=save_species`, {
                method: 'POST',
                body: formData
            });
            
            const responseText = await response.text();
            console.log('Species API response:', responseText);
            
            try {
                const data = JSON.parse(responseText);
                if (data.success) {
                    alert(data.message);
                    this.closeAdminModal();
                    location.reload(); // Refresh to show new species
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                alert('Server response was not valid JSON: ' + responseText.substring(0, 100));
            }
        } catch (error) {
            console.error('Species form submission error:', error);
            alert('An error occurred while saving the species: ' + error.message);
        }
    }
    
    async handleRaceFormSubmission(form) {
        try {
            const formData = new FormData(form);
            
            console.log('Submitting race form data...');
            const response = await fetch(`${this.apiEndpoint}?action=save_race`, {
                method: 'POST',
                body: formData
            });
            
            const responseText = await response.text();
            console.log('Race API response:', responseText);
            
            try {
                const data = JSON.parse(responseText);
                if (data.success) {
                    alert(data.message);
                    this.closeAdminModal();
                    location.reload(); // Refresh to show new race
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                alert('Server response was not valid JSON: ' + responseText.substring(0, 100));
            }
        } catch (error) {
            console.error('Race form submission error:', error);
            alert('An error occurred while saving the race: ' + error.message);
        }
    }
    
    async handleEditSpeciesFormSubmission(form) {
        try {
            const formData = new FormData(form);
            
            console.log('Submitting edit species form data...');
            const response = await fetch(`${this.apiEndpoint}?action=edit_species`, {
                method: 'POST',
                body: formData
            });
            
            const responseText = await response.text();
            console.log('Edit Species API response:', responseText);
            
            try {
                const data = JSON.parse(responseText);
                if (data.success) {
                    alert(data.message);
                    // Hide edit form and refresh species list
                    document.getElementById('modal-edit-species-form').style.display = 'none';
                    this.loadModalSpecies();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                alert('Server response was not valid JSON: ' + responseText.substring(0, 100));
            }
        } catch (error) {
            console.error('Edit species form submission error:', error);
            alert('An error occurred while updating the species: ' + error.message);
        }
    }
    
    async handleEditRaceFormSubmission(form) {
        try {
            const formData = new FormData(form);
            
            console.log('Submitting edit race form data...');
            const response = await fetch(`${this.apiEndpoint}?action=edit_race`, {
                method: 'POST',
                body: formData
            });
            
            const responseText = await response.text();
            console.log('Edit Race API response:', responseText);
            
            try {
                const data = JSON.parse(responseText);
                if (data.success) {
                    alert(data.message);
                    // Hide edit form and refresh races list
                    document.getElementById('modal-edit-race-form').style.display = 'none';
                    this.loadModalRaces();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                alert('Server response was not valid JSON: ' + responseText.substring(0, 100));
            }
        } catch (error) {
            console.error('Edit race form submission error:', error);
            alert('An error occurred while updating the race: ' + error.message);
        }
    }
    
    // Species Management
    async openAdminModal() {
        const modal = document.getElementById('adminModal');
        if (!modal) {
            console.error('Admin modal not found');
            return;
        }
        
        try {
            console.log('Loading admin modal content...');
            const response = await fetch(`${this.apiEndpoint}?action=modal`);
            const html = await response.text();
            document.getElementById('adminModalContent').innerHTML = html;
            modal.style.display = 'block';
            
            // Ensure species data loads after modal content is ready
            console.log('Modal content loaded, loading species data...');
            setTimeout(() => {
                this.loadModalSpecies();
            }, 100);
            
        } catch (error) {
            console.error('Error loading admin interface:', error);
            document.getElementById('adminModalContent').innerHTML = 
                '<div class="error-message">Failed to load admin interface</div>';
            modal.style.display = 'block';
        }
    }
    
    closeAdminModal() {
        const modal = document.getElementById('adminModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Modal-specific functions
    switchModalTab(tabName) {
        document.querySelectorAll('.modal-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.modal-tab-content').forEach(content => content.classList.remove('active'));
        
        document.querySelector(`[onclick="switchModalTab('${tabName}')"]`).classList.add('active');
        document.getElementById('modal-' + tabName + '-tab').classList.add('active');
        
        if (tabName === 'species') this.loadModalSpecies();
        if (tabName === 'races') this.loadModalRaces();
    }

    showModalAddSpeciesForm() {
        const form = document.getElementById('modal-add-species-form');
        if (form) form.style.display = 'block';
    }

    hideModalAddSpeciesForm() {
        const form = document.getElementById('modal-add-species-form');
        if (form) {
            form.style.display = 'none';
            document.getElementById('speciesForm').reset();
        }
    }

    showModalAddRaceForm() {
        const form = document.getElementById('modal-add-race-form');
        if (form) {
            form.style.display = 'block';
            // Load species for dropdown
            this.loadSpeciesForDropdown();
        }
    }

    async loadSpeciesForDropdown() {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_species_for_dropdown`);
            const data = await response.json();
            const select = document.getElementById('species-select');
            
            if (select && data.success) {
                // Clear existing options except the first one
                select.innerHTML = '<option value="">Select a species...</option>';
                
                // Add species options
                data.data.forEach(species => {
                    const option = document.createElement('option');
                    option.value = species.id_specie;
                    option.textContent = species.specie_name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading species for dropdown:', error);
        }
    }

    async loadSpeciesForEditDropdown() {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_species_for_dropdown`);
            const data = await response.json();
            const select = document.getElementById('edit-species-select');
            
            if (select && data.success) {
                // Clear existing options except the first one
                select.innerHTML = '<option value="">Select a species...</option>';
                
                // Add species options
                data.data.forEach(species => {
                    const option = document.createElement('option');
                    option.value = species.id_specie;
                    option.textContent = species.specie_name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading species for dropdown:', error);
        }
    }

    hideModalAddRaceForm() {
        const form = document.getElementById('modal-add-race-form');
        if (form) {
            form.style.display = 'none';
            document.getElementById('raceForm').reset();
        }
    }

    async loadModalSpecies() {
        console.log('loadModalSpecies called');
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_species`);
            console.log('Species API response received:', response.status);
            
            const data = await response.json();
            console.log('Species data:', data);
            
            const list = document.getElementById('modal-species-list');
            console.log('Species list element found:', !!list);
            
            if (list && data.success) {
                if (data.data && data.data.length > 0) {
                    list.innerHTML = data.data.map(species => `
                        <div class="entity-card">
                            <div class="entity-name">${species.specie_name}</div>
                            <div class="entity-description">${species.content_specie || 'No description'}</div>
                            ${species.lifespan ? `<div class="entity-detail"><strong>Lifespan:</strong> ${species.lifespan}</div>` : ''}
                            ${species.homeworld ? `<div class="entity-detail"><strong>Homeworld:</strong> ${species.homeworld}</div>` : ''}
                            ${species.country ? `<div class="entity-detail"><strong>Country:</strong> ${species.country}</div>` : ''}
                            ${species.habitat ? `<div class="entity-detail"><strong>Habitat:</strong> ${species.habitat}</div>` : ''}
                            ${species.icon_specie ? `<img src="../images/species/${species.icon_specie}" alt="${species.specie_name}" class="entity-icon">` : ''}
                            <div class="entity-actions">
                                <button class="btn-edit" onclick="window.beingsManager.editModalSpecies(${species.id_specie})">Edit</button>
                                <button class="btn-delete" onclick="window.beingsManager.deleteModalSpecies(${species.id_specie})">Delete</button>
                            </div>
                        </div>
                    `).join('');
                    console.log('Species list updated with', data.data.length, 'items');
                } else {
                    list.innerHTML = '<p>No species found. <button class="btn-primary" onclick="showModalAddSpeciesForm()">Add your first species</button></p>';
                    console.log('No species found in database');
                }
            } else if (list) {
                list.innerHTML = `<p class="error">${data?.message || 'Error loading species'}</p>`;
                console.error('Error loading species:', data?.message);
            } else {
                console.error('modal-species-list element not found');
            }
        } catch (error) {
            console.error('Error loading modal species:', error);
            const list = document.getElementById('modal-species-list');
            if (list) {
                list.innerHTML = `<p class="error">Error loading species: ${error.message}</p>`;
            }
        }
    }

    async loadModalRaces() {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_races`);
            const data = await response.json();
            const list = document.getElementById('modal-races-list');
            
            if (list && data.success) {
                list.innerHTML = data.data.map(race => `
                    <div class="entity-card">
                        <div class="entity-name">${race.race_name}</div>
                        <div class="entity-description">${race.content_race || 'No description'}</div>
                        ${race.species_name ? `<div class="entity-detail"><strong>Species:</strong> ${race.species_name}</div>` : ''}
                        ${race.icon_race ? `<img src="../images/races/${race.icon_race}" alt="${race.race_name}" class="entity-icon">` : ''}
                        <div class="entity-actions">
                            <button class="btn-edit" onclick="window.beingsManager.editModalRace(${race.id_race})">Edit</button>
                            <button class="btn-delete" onclick="window.beingsManager.deleteModalRace(${race.id_race})">Delete</button>
                        </div>
                    </div>
                `).join('');
            } else if (list) {
                list.innerHTML = `<p class="error">${data?.message || 'Error loading races'}</p>`;
            }
        } catch (error) {
            console.error('Error loading modal races:', error);
        }
    }

    async deleteModalSpecies(id) {
        if (confirm('Are you sure you want to delete this species?')) {
            try {
                const formData = new FormData();
                formData.append('specie_id', id);
                
                const response = await fetch(`${this.apiEndpoint}?action=delete_species`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                alert(data.message);
                if (data.success) this.loadModalSpecies();
            } catch (error) {
                alert('Error deleting species: ' + error.message);
            }
        }
    }

    async deleteModalRace(id) {
        if (confirm('Are you sure you want to delete this race?')) {
            try {
                const formData = new FormData();
                formData.append('race_id', id);
                
                const response = await fetch(`${this.apiEndpoint}?action=delete_race`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                alert(data.message);
                if (data.success) this.loadModalRaces();
            } catch (error) {
                alert('Error deleting race: ' + error.message);
            }
        }
    }

    editModalSpecies(id) {
        this.openEditSpeciesModal(id);
    }

    editModalRace(id) {
        this.openEditRaceModal(id);
    }
    
    openEditModal(title, content) {
        document.getElementById('editModalTitle').textContent = title;
        document.getElementById('editModalBody').innerHTML = content;
        document.getElementById('editModal').style.display = 'block';
    }
    
    closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editModalBody').innerHTML = '';
    }
    
    openEditSpeciesModal(id) {
        // Fetch species data
        fetch(`${this.apiEndpoint}?action=get_species_by_id&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const species = data.data;
                    const formHtml = `
                        <div class="edit-form-container">
                            <form id="editSpeciesPopupForm" enctype="multipart/form-data">
                                <input type="hidden" name="specie_id" value="${species.id_specie}">
                                
                                <div class="form-group">
                                    <label>Species Name: <span style="color: red;">*</span></label>
                                    <input type="text" name="specie_name" value="${species.specie_name || ''}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Description:</label>
                                    <textarea name="content_specie" placeholder="Optional description of the species">${species.content_specie || ''}</textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Icon/Image:</label>
                                    <input type="file" name="icon_specie" accept="image/*" onchange="previewImage(this, 'edit-species-popup-preview')">
                                    <div id="edit-species-popup-preview" class="image-preview"></div>
                                    ${species.icon_specie ? `
                                    <div style="margin-top: 10px;">
                                        <small>Current image:</small><br>
                                        <img src="../images/species/${species.icon_specie}" alt="Current species image" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                                    </div>
                                    ` : ''}
                                </div>
                                
                                <div class="form-group">
                                    <label>Lifespan:</label>
                                    <input type="text" name="lifespan" value="${species.lifespan || ''}" placeholder="e.g., 80-100 years">
                                </div>
                                
                                <div class="form-group">
                                    <label>Homeworld:</label>
                                    <input type="text" name="homeworld" value="${species.homeworld || ''}" placeholder="Planet or realm of origin">
                                </div>
                                
                                <div class="form-group">
                                    <label>Country/Region:</label>
                                    <input type="text" name="country" value="${species.country || ''}" placeholder="Specific country or region">
                                </div>
                                
                                <div class="form-group">
                                    <label>Habitat:</label>
                                    <input type="text" name="habitat" value="${species.habitat || ''}" placeholder="Preferred living environment">
                                </div>
                                
                                <div class="edit-form-actions">
                                    <button type="button" class="btn-secondary" onclick="window.beingsManager.closeEditModal()">Cancel</button>
                                    <button type="submit" class="btn-primary">Update Species</button>
                                </div>
                            </form>
                        </div>
                    `;
                    
                    this.openEditModal('Edit Species', formHtml);
                    
                    // Attach form submission handler
                    document.getElementById('editSpeciesPopupForm').addEventListener('submit', (e) => {
                        e.preventDefault();
                        const formData = new FormData(e.target);
                        
                        fetch(`${this.apiEndpoint}?action=edit_species`, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                this.closeEditModal();
                                // Refresh species data without page reload
                                if (typeof this.loadModalSpecies === 'function') {
                                    this.loadModalSpecies();
                                }
                                // Also refresh the main page display if we're on the beings page
                                this.refreshBeingsDisplay();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            alert('Error: ' + error.message);
                        });
                    });
                } else {
                    alert('Failed to load species data');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
    }
    
    openEditRaceModal(id) {
        // First load species options for the dropdown
        fetch(`${this.apiEndpoint}?action=get_species`)
            .then(response => response.json())
            .then(speciesData => {
                const speciesOptions = speciesData.success ? speciesData.data : [];
                
                // Then fetch race data
                return fetch(`${this.apiEndpoint}?action=get_race_by_id&id=${id}`);
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const race = data.data;
                    
                    // Get species options again for the form
                    fetch(`${this.apiEndpoint}?action=get_species`)
                        .then(response => response.json())
                        .then(speciesData => {
                            const speciesOptions = speciesData.success ? speciesData.data : [];
                            let speciesOptionsHtml = '<option value="">Select a species...</option>';
                            speciesOptions.forEach(species => {
                                const selected = species.id_specie == race.correspondence ? 'selected' : '';
                                speciesOptionsHtml += `<option value="${species.id_specie}" ${selected}>${species.specie_name}</option>`;
                            });
                            
                            const formHtml = `
                                <div class="edit-form-container">
                                    <form id="editRacePopupForm" enctype="multipart/form-data">
                                        <input type="hidden" name="race_id" value="${race.id_race}">
                                        
                                        <div class="form-group">
                                            <label>Race Name: <span style="color: red;">*</span></label>
                                            <input type="text" name="race_name" value="${race.race_name || ''}" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Species: <span style="color: red;">*</span></label>
                                            <select name="correspondence" required>
                                                ${speciesOptionsHtml}
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Description:</label>
                                            <textarea name="content_race" placeholder="Optional description of the race">${race.content_race || ''}</textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Icon/Image:</label>
                                            <input type="file" name="icon_race" accept="image/*" onchange="previewImage(this, 'edit-race-popup-preview')">
                                            <div id="edit-race-popup-preview" class="image-preview"></div>
                                            ${race.icon_race ? `
                                            <div style="margin-top: 10px;">
                                                <small>Current image:</small><br>
                                                <img src="../images/races/${race.icon_race}" alt="Current race image" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                                            </div>
                                            ` : ''}
                                        </div>
                                        
                                        <div class="edit-form-actions">
                                            <button type="button" class="btn-secondary" onclick="window.beingsManager.closeEditModal()">Cancel</button>
                                            <button type="submit" class="btn-primary">Update Race</button>
                                        </div>
                                    </form>
                                </div>
                            `;
                            
                            this.openEditModal('Edit Race', formHtml);
                            
                            // Attach form submission handler
                            document.getElementById('editRacePopupForm').addEventListener('submit', (e) => {
                                e.preventDefault();
                                const formData = new FormData(e.target);
                                
                                fetch(`${this.apiEndpoint}?action=edit_race`, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert(data.message);
                                        this.closeEditModal();
                                        // Refresh races data without page reload
                                        if (typeof this.loadModalRaces === 'function') {
                                            this.loadModalRaces();
                                        }
                                        // Also refresh the main page display if we're on the beings page
                                        this.refreshBeingsDisplay();
                                    } else {
                                        alert('Error: ' + data.message);
                                    }
                                })
                                .catch(error => {
                                    alert('Error: ' + error.message);
                                });
                            });
                        });
                } else {
                    alert('Failed to load race data');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
    }
    
    switchModalTab(tabName) {
        document.querySelectorAll('.modal-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.modal-tab-content').forEach(content => content.classList.remove('active'));
        
        // Find and activate the correct tab button
        const tabBtn = document.querySelector(`.modal-tab-btn[onclick*="${tabName}"]`);
        if (tabBtn) tabBtn.classList.add('active');
        
        // Activate the correct tab content
        const tabContent = document.getElementById(`modal-${tabName}-tab`);
        if (tabContent) tabContent.classList.add('active');
        
        // Load data when switching tabs
        if (tabName === 'species') this.loadModalSpecies();
        if (tabName === 'races') this.loadModalRaces();
    }
    
    // Refresh the main beings display page (if we're on it)
    refreshBeingsDisplay() {
        const beingsGrid = document.querySelector('.beings-grid');
        if (beingsGrid) {
            console.log('Refreshing beings display...');
            // Get current URL parameters to maintain search/filter state
            const urlParams = new URLSearchParams(window.location.search);
            const searchTerm = urlParams.get('search') || '';
            const filterSpecie = urlParams.get('specie') || '';
            const page = urlParams.get('page') || '1';
            
            // Build refresh URL
            let refreshUrl = window.location.pathname + '?ajax=1';
            if (searchTerm) refreshUrl += '&search=' + encodeURIComponent(searchTerm);
            if (filterSpecie) refreshUrl += '&specie=' + encodeURIComponent(filterSpecie);
            if (page !== '1') refreshUrl += '&page=' + page;
            
            fetch(refreshUrl)
                .then(response => response.text())
                .then(html => {
                    // Parse the response and extract the beings grid
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newBeingsGrid = doc.querySelector('.beings-grid');
                    
                    if (newBeingsGrid) {
                        beingsGrid.innerHTML = newBeingsGrid.innerHTML;
                        console.log('Beings display refreshed successfully');
                    }
                })
                .catch(error => {
                    console.error('Error refreshing beings display:', error);
                });
        }
    }
    
    // Toggle species races visibility
    toggleSpeciesRaces(speciesId) {
        const racesSection = document.getElementById('races-' + speciesId);
        const toggleIcon = document.querySelector(`.species-card[data-species-id="${speciesId}"] .toggle-icon`);
        
        if (racesSection && toggleIcon) {
            const isVisible = racesSection.classList.contains('show');
            
            if (isVisible) {
                // Hide races with smooth animation
                racesSection.classList.remove('show');
                toggleIcon.textContent = '▼';
                toggleIcon.style.transform = 'rotate(0deg)';
            } else {
                // Show races with smooth animation
                racesSection.classList.add('show');
                toggleIcon.textContent = '▲';
                toggleIcon.style.transform = 'rotate(180deg)';
                
                // Load races data if not already loaded
                this.loadSpeciesRaces(speciesId);
            }
        }
    }
    
    // Load races for a specific species (AJAX)
    loadSpeciesRaces(speciesId) {
        const racesSection = document.getElementById('races-' + speciesId);
        if (!racesSection) return;
        
        // Check if races are already loaded
        const racesGrid = racesSection.querySelector('.races-grid');
        if (racesGrid && racesGrid.children.length > 0) {
            return; // Already loaded
        }
        
        // Show loading state
        racesSection.innerHTML = '<div class="loading">Loading races...</div>';
        
        fetch(`${this.apiEndpoint}?action=get_species_races&species_id=${speciesId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.races) {
                    this.renderSpeciesRaces(speciesId, data.races);
                } else {
                    racesSection.innerHTML = '<div class="no-races"><p>No races defined for this species yet.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading races:', error);
                racesSection.innerHTML = '<div class="error">Failed to load races</div>';
            });
    }
    
    // Render races for a species
    renderSpeciesRaces(speciesId, races) {
        const racesSection = document.getElementById('races-' + speciesId);
        if (!racesSection) return;
        
        if (races.length === 0) {
            racesSection.innerHTML = '<div class="no-races"><p>No races defined for this species yet.</p></div>';
            return;
        }
        
        let racesHtml = '<div class="races-grid">';
        races.forEach(race => {
            const raceImgPath = race.icon_race ? 
                `../images/races/${race.icon_race}` : 
                '../images/icon_default.png';
                
            racesHtml += `
                <div class="race-card" data-race-id="${race.id_race}">
                    <div class="race-header">
                        <div class="race-image">
                            <img src="${raceImgPath}" 
                                 alt="${race.race_name}"
                                 onerror="this.src='../images/icon_default.png'">
                        </div>
                        <div class="race-info" onclick="window.location.href='./Beings_display.php?race_id=${race.id_race}'">
                            <h3 class="race-name">${race.race_name}</h3>
                            <p class="race-character-count">0 character(s)</p>
                            ${race.content_race ? `
                            <p class="race-description">
                                ${race.content_race.substring(0, 100)}
                                ${race.content_race.length > 100 ? '...' : ''}
                            </p>
                            ` : ''}
                        </div>
                        <div class="race-toggle" onclick="window.beingsManager.toggleRaceCharacters('${race.id_race}')">
                            <span class="toggle-icon">▼</span>
                        </div>
                    </div>
                    <div class="characters-section" id="characters-${race.id_race}">
                        <!-- Characters will be loaded here -->
                    </div>
                </div>
            `;
        });
        racesHtml += '</div>';
        
        racesSection.innerHTML = racesHtml;
    }
    
    // Toggle race characters visibility
    toggleRaceCharacters(raceId) {
        const charactersSection = document.getElementById('characters-' + raceId);
        const toggleIcon = document.querySelector(`.race-card[data-race-id="${raceId}"] .toggle-icon`);
        
        if (charactersSection && toggleIcon) {
            const isVisible = charactersSection.style.display !== 'none';
            
            if (isVisible) {
                charactersSection.style.display = 'none';
                toggleIcon.textContent = '▼';
            } else {
                charactersSection.style.display = 'block';
                toggleIcon.textContent = '▲';
                
                // Load characters data if not already loaded
                this.loadRaceCharacters(raceId);
            }
        }
    }
    
    // Load characters for a specific race
    loadRaceCharacters(raceId) {
        const charactersSection = document.getElementById('characters-' + raceId);
        if (!charactersSection) return;
        
        // Show loading state
        charactersSection.innerHTML = '<div class="loading">Loading characters...</div>';
        
        fetch(`${this.apiEndpoint}?action=get_race_characters&race_id=${raceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.characters) {
                    this.renderRaceCharacters(raceId, data.characters);
                } else {
                    charactersSection.innerHTML = '<div class="no-characters"><p>No characters for this race yet.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading characters:', error);
                charactersSection.innerHTML = '<div class="error">Failed to load characters</div>';
            });
    }
    
    // Render characters for a race
    renderRaceCharacters(raceId, characters) {
        const charactersSection = document.getElementById('characters-' + raceId);
        if (!charactersSection) return;
        
        if (characters.length === 0) {
            charactersSection.innerHTML = '<div class="no-characters"><p>No characters for this race yet.</p></div>';
            return;
        }
        
        let charactersHtml = '<div class="characters-grid">';
        characters.forEach(character => {
            const charImgPath = character.icon_character ? 
                `../images/characters/${character.icon_character}` : 
                '../images/icon_default.png';
                
            charactersHtml += `
                <div class="character-card">
                    <div class="character-image">
                        <img src="${charImgPath}" 
                             alt="${character.character_name}"
                             onerror="this.src='../images/icon_default.png'">
                    </div>
                    <div class="character-info" onclick="window.location.href='./Character_display.php?character_id=${character.id_character}'">
                        <h4 class="character-name">${character.character_name}</h4>
                        ${character.content_character ? `
                        <p class="character-description">
                            ${character.content_character.substring(0, 80)}
                            ${character.content_character.length > 80 ? '...' : ''}
                        </p>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        charactersHtml += '</div>';
        
        charactersSection.innerHTML = charactersHtml;
    }
    
    // View race details (existing function placeholder)
    viewRaceDetails(speciesId, raceId) {
        window.location.href = `./Beings_display.php?specie_id=${speciesId}&race_id=${raceId}`;
    }
    
    // View species characters (existing function placeholder)
    viewSpeciesCharacters(speciesId) {
        window.location.href = `./Beings_display.php?specie_id=${speciesId}`;
    }
    
    async addNewSpecies() {
        const modal = document.getElementById('adminModal');
        if (!modal) return;
        
        try {
            const response = await fetch(`${this.apiEndpoint}?action=add_species`);
            const html = await response.text();
            document.getElementById('adminModalContent').innerHTML = html;
            modal.style.display = 'block';
        } catch (error) {
            console.error('Error loading species form:', error);
            NotificationManager.error('Failed to load species form');
        }
    }
    
    async editSpecies(speciesId) {
        if (!speciesId) {
            NotificationManager.error('Invalid species ID');
            return;
        }
        
        const modal = document.getElementById('adminModal');
        try {
            const response = await fetch(`${this.apiEndpoint}?action=edit_species&id=${speciesId}`);
            const html = await response.text();
            document.getElementById('adminModalContent').innerHTML = html;
            modal.style.display = 'block';
        } catch (error) {
            console.error('Error loading species edit form:', error);
            NotificationManager.error('Failed to load edit form');
        }
    }
    
    async confirmDeleteSpecies(speciesId, speciesName) {
        if (!speciesId) {
            NotificationManager.error('Invalid species ID');
            return;
        }
        
        const confirmed = confirm(
            `Are you sure you want to delete the species "${speciesName}"? ` +
            'This will also delete all associated races and characters.'
        );
        
        if (!confirmed) return;
        
        try {
            const data = await ApiClient.post(`${this.apiEndpoint}?action=delete_species`, { id: speciesId });
            
            if (data.success) {
                NotificationManager.success('Species deleted successfully');
                this.removeSpeciesFromDOM(speciesId);
                this.closeAdminModal();
            } else {
                NotificationManager.error('Error deleting species: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting species:', error);
            NotificationManager.error('Network error occurred');
        }
    }
    
    // Race Management
    async addRaceToSpecies(speciesId) {
        if (!speciesId) {
            NotificationManager.error('Invalid species ID');
            return;
        }
        
        const modal = document.getElementById('adminModal');
        try {
            const response = await fetch(`${this.apiEndpoint}?action=add_race&species_id=${speciesId}`);
            const html = await response.text();
            document.getElementById('adminModalContent').innerHTML = html;
            modal.style.display = 'block';
        } catch (error) {
            console.error('Error loading race form:', error);
            NotificationManager.error('Failed to load race form');
        }
    }
    
    async confirmDeleteRace(raceId, raceName) {
        if (!raceId) {
            NotificationManager.error('Invalid race ID');
            return;
        }
        
        const confirmed = confirm(
            `Are you sure you want to delete the race "${raceName}"? ` +
            'This will also delete all associated characters.'
        );
        
        if (!confirmed) return;
        
        try {
            const data = await ApiClient.post(`${this.apiEndpoint}?action=delete_race`, { id: raceId });
            
            if (data.success) {
                NotificationManager.success('Race deleted successfully');
                this.removeRaceFromDOM(raceId);
            } else {
                NotificationManager.error('Error deleting race: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error deleting race:', error);
            NotificationManager.error('Network error occurred');
        }
    }
    
    // UI Interactions
    toggleSpeciesRaces(speciesId) {
        const racesSection = document.getElementById(`races-${speciesId}`);
        const toggleIcon = document.querySelector(`[onclick*="toggleSpeciesRaces('${speciesId}')"] .toggle-icon`);
        
        if (racesSection && toggleIcon) {
            const isVisible = racesSection.classList.contains('show');
            
            if (isVisible) {
                racesSection.classList.remove('show');
                toggleIcon.textContent = '▼';
                toggleIcon.style.transform = 'rotate(0deg)';
            } else {
                racesSection.classList.add('show');
                toggleIcon.textContent = '▲';
                toggleIcon.style.transform = 'rotate(180deg)';
                
                // Load races if not already loaded
                this.loadSpeciesRaces(speciesId);
            }
        }
    }

    toggleRaceCharacters(raceId) {
        const charactersSection = document.getElementById(`characters-${raceId}`);
        const toggleIcon = document.querySelector(`[onclick*="toggleRaceCharacters('${raceId}')"] .toggle-icon`);
        
        if (charactersSection && toggleIcon) {
            const isVisible = charactersSection.classList.contains('show');
            
            if (isVisible) {
                charactersSection.classList.remove('show');
                toggleIcon.textContent = '▼';
                toggleIcon.style.transform = 'rotate(0deg)';
            } else {
                charactersSection.classList.add('show');
                toggleIcon.textContent = '▲';
                toggleIcon.style.transform = 'rotate(180deg)';
            }
        }
    }    // Navigation
    viewRaceDetails(speciesId, raceId) {
        window.location.href = `./Beings_display.php?specie_id=${speciesId}&race_id=${raceId}`;
    }
    
    viewSpeciesCharacters(speciesId) {
        window.location.href = `./Beings_display.php?specie_id=${speciesId}`;
    }
    
    // DOM Manipulation
    removeSpeciesFromDOM(speciesId) {
        const speciesCard = document.querySelector(`[data-species-id="${speciesId}"]`);
        if (speciesCard) {
            speciesCard.style.transition = 'opacity 0.3s ease';
            speciesCard.style.opacity = '0';
            setTimeout(() => speciesCard.remove(), 300);
        }
    }
    
    removeRaceFromDOM(raceId) {
        const raceCard = document.querySelector(`[data-race-id="${raceId}"]`);
        if (raceCard) {
            raceCard.style.transition = 'opacity 0.3s ease';
            raceCard.style.opacity = '0';
            setTimeout(() => {
                raceCard.remove();
                this.updateRaceCount(raceCard);
            }, 300);
        }
    }
    
    updateRaceCount(raceCard) {
        const speciesCard = raceCard?.closest('.species-card');
        if (speciesCard) {
            const raceCountElement = speciesCard.querySelector('[data-count-type="race"]');
            if (raceCountElement) {
                const currentCount = parseInt(raceCountElement.textContent.match(/\d+/)?.[0] || 0);
                const newCount = Math.max(0, currentCount - 1);
                raceCountElement.textContent = `${newCount} race(s)`;
            }
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get configuration from PHP (set in the page)
    const config = window.BeingsConfig || {};
    window.beingsManager = new BeingsManager(config);
    
    // Make functions globally available for onclick handlers (backward compatibility)
    window.openAdminModal = () => window.beingsManager.openAdminModal();
    window.closeAdminModal = () => window.beingsManager.closeAdminModal();
    window.switchModalTab = (tabName) => window.beingsManager.switchModalTab(tabName);
    window.showModalAddSpeciesForm = () => window.beingsManager.showModalAddSpeciesForm();
    window.hideModalAddSpeciesForm = () => window.beingsManager.hideModalAddSpeciesForm();
    window.showModalAddRaceForm = () => window.beingsManager.showModalAddRaceForm();
    window.hideModalAddRaceForm = () => window.beingsManager.hideModalAddRaceForm();
    window.addNewSpecies = () => window.beingsManager.addNewSpecies();
    window.editSpecies = (id) => window.beingsManager.editSpecies(id);
    window.confirmDeleteSpecies = (id, name) => window.beingsManager.confirmDeleteSpecies(id, name);
    window.addRaceToSpecies = (id) => window.beingsManager.addRaceToSpecies(id);
    window.confirmDeleteRace = (id, name) => window.beingsManager.confirmDeleteRace(id, name);
    window.toggleSpeciesRaces = (id) => window.beingsManager.toggleSpeciesRaces(id);
    window.toggleRaceCharacters = (id) => window.beingsManager.toggleRaceCharacters(id);
    window.viewRaceDetails = (sId, rId) => window.beingsManager.viewRaceDetails(sId, rId);
    window.viewSpeciesCharacters = (id) => window.beingsManager.viewSpeciesCharacters(id);
    
    // Add global click handler for edit modal
    window.addEventListener('click', function(event) {
        const editModal = document.getElementById('editModal');
        if (editModal && event.target === editModal) {
            if (window.beingsManager) {
                window.beingsManager.closeEditModal();
            }
        }
    });
});
