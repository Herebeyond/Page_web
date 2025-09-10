/**
 * Character Management JavaScript
 * Handles character-related functionality and CRUD operations
 */

class CharacterManager {
    constructor() {
        this.init();
    }
    
    init() {
        // Auto-initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeEventHandlers();
        });
    }
    
    initializeEventHandlers() {
        // Character action buttons
        const characterButtons = document.querySelectorAll('[data-character-action], .character-btn');
        characterButtons.forEach(button => {
            if (!button.hasAttribute('data-char-initialized')) {
                const action = button.getAttribute('data-character-action');
                button.addEventListener('click', (e) => this.handleCharacterAction(e, action));
                button.setAttribute('data-char-initialized', 'true');
            }
        });
        
        // Character forms
        const characterForms = document.querySelectorAll('.character-form, #characterForm');
        characterForms.forEach(form => {
            if (!form.hasAttribute('data-char-initialized')) {
                form.addEventListener('submit', (e) => this.handleCharacterFormSubmit(e));
                form.setAttribute('data-char-initialized', 'true');
            }
        });
    }
    
    async handleCharacterAction(event, action) {
        event.preventDefault();
        const button = event.target;
        const characterId = button.getAttribute('data-character-id') || 
                           this.extractIdFromOnclick(button.getAttribute('onclick'));
        
        switch (action) {
            case 'edit':
                await this.editCharacter(characterId);
                break;
            case 'delete':
                await this.deleteCharacter(characterId);
                break;
            case 'view':
                await this.viewCharacter(characterId);
                break;
            case 'add':
                await this.addCharacter();
                break;
            default:
                console.warn(`Unknown character action: ${action}`);
        }
    }
    
    extractIdFromOnclick(onclickString) {
        if (!onclickString) return null;
        const match = onclickString.match(/\('([^']+)'\)|"([^"]+)"|(\d+)/);
        return match ? (match[1] || match[2] || match[3]) : null;
    }
    
    async addCharacter() {
        try {
            // Show character creation modal/form
            const modal = document.getElementById('characterModal') || 
                         document.getElementById('addCharacterModal');
            if (modal) {
                modal.style.display = 'block';
                this.prepareCharacterForm();
            } else {
                // Create inline form or redirect
                window.location.href = 'add_character.php';
            }
        } catch (error) {
            console.error('Error adding character:', error);
            window.notificationManager?.error('Erreur lors de l\'ajout du personnage');
        }
    }
    
    async editCharacter(characterId) {
        if (!characterId) return;
        
        try {
            window.loadingIndicator?.show('Chargement du personnage...');
            
            const response = await window.apiClient?.get(`api/characters/${characterId}`);
            if (response && response.success) {
                this.populateCharacterForm(response.data);
                const modal = document.getElementById('characterModal');
                if (modal) {
                    modal.style.display = 'block';
                }
            } else {
                throw new Error(response?.message || 'Erreur lors du chargement');
            }
        } catch (error) {
            console.error('Error editing character:', error);
            window.notificationManager?.error('Erreur lors de la modification du personnage');
        } finally {
            window.loadingIndicator?.hide();
        }
    }
    
    async deleteCharacter(characterId) {
        if (!characterId) return;
        
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce personnage ?')) {
            return;
        }
        
        try {
            window.loadingIndicator?.show('Suppression en cours...');
            
            const response = await window.apiClient?.delete(`api/characters/${characterId}`);
            if (response && response.success) {
                // Remove from DOM
                const characterRow = document.querySelector(`tr[data-character-id="${characterId}"]`) ||
                                    document.querySelector(`[data-character-id="${characterId}"]`);
                if (characterRow) {
                    characterRow.remove();
                }
                
                window.notificationManager?.success('Personnage supprimé avec succès');
            } else {
                throw new Error(response?.message || 'Erreur lors de la suppression');
            }
        } catch (error) {
            console.error('Error deleting character:', error);
            window.notificationManager?.error('Erreur lors de la suppression du personnage');
        } finally {
            window.loadingIndicator?.hide();
        }
    }
    
    async viewCharacter(characterId) {
        if (!characterId) return;
        
        // Redirect to character view page or show in modal
        window.location.href = `character.php?id=${characterId}`;
    }
    
    prepareCharacterForm() {
        const form = document.getElementById('characterForm');
        if (form) {
            form.reset();
            form.querySelector('[name="character_id"]')?.setAttribute('value', '');
        }
    }
    
    populateCharacterForm(characterData) {
        const form = document.getElementById('characterForm');
        if (!form || !characterData) return;
        
        // Populate form fields
        Object.keys(characterData).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = !!characterData[key];
                } else {
                    field.value = characterData[key] || '';
                }
            }
        });
    }
    
    async handleCharacterFormSubmit(event) {
        event.preventDefault();
        
        try {
            const form = event.target;
            const formData = new FormData(form);
            const characterId = formData.get('character_id');
            const isEdit = characterId && characterId !== '';
            
            window.loadingIndicator?.show(isEdit ? 'Modification en cours...' : 'Création en cours...');
            
            const endpoint = isEdit ? `api/characters/${characterId}` : 'api/characters';
            const method = isEdit ? 'put' : 'post';
            
            const response = await window.apiClient?.[method](endpoint, formData);
            
            if (response && response.success) {
                window.notificationManager?.success(
                    isEdit ? 'Personnage modifié avec succès' : 'Personnage créé avec succès'
                );
                
                // Close modal and refresh
                const modal = document.getElementById('characterModal');
                if (modal) {
                    modal.style.display = 'none';
                }
                
                // Refresh the page or update the table
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(response?.message || 'Erreur lors de la sauvegarde');
            }
        } catch (error) {
            console.error('Error saving character:', error);
            window.notificationManager?.error('Erreur lors de la sauvegarde du personnage');
        } finally {
            window.loadingIndicator?.hide();
        }
    }
    
    // Character filtering and search
    filterCharacters(searchTerm) {
        const characterRows = document.querySelectorAll('.character-row, tr[data-character-id]');
        const lowerSearchTerm = searchTerm.toLowerCase();
        
        characterRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(lowerSearchTerm);
            row.style.display = matches ? '' : 'none';
        });
    }
    
    // Character statistics
    getCharacterStats() {
        const characters = document.querySelectorAll('.character-row, tr[data-character-id]');
        return {
            total: characters.length,
            visible: Array.from(characters).filter(row => row.style.display !== 'none').length
        };
    }
}

// Initialize character manager globally
window.characterManager = new CharacterManager();

// Legacy compatibility functions
window.editCharacter = (characterId) => {
    window.characterManager.editCharacter(characterId);
};

window.deleteCharacter = (characterId) => {
    window.characterManager.deleteCharacter(characterId);
};

window.addCharacter = () => {
    window.characterManager.addCharacter();
};

window.viewCharacter = (characterId) => {
    window.characterManager.viewCharacter(characterId);
};

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CharacterManager;
}
