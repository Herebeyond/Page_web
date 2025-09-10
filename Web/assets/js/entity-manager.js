/**
 * Generic Entity Management JavaScript
 * Handles CRUD operations for various entity types (species, races, characters, etc.)
 */

class EntityManager {
    constructor(config = {}) {
        this.apiEndpoint = config.apiEndpoint || './scriptes/admin_interface.php';
        this.entityType = config.entityType || 'entity';
        this.dynamicUpdate = config.dynamicUpdate !== false;
    }
    
    async confirmDelete(id, name) {
        const confirmed = confirm(
            `Are you sure you want to delete the ${this.entityType} "${name}"? This action cannot be undone.`
        );
        
        if (!confirmed) return;
        
        try {
            const data = await ApiClient.post(`${this.apiEndpoint}?action=delete_${this.entityType}`, { id });
            
            if (data.success) {
                NotificationManager.success(data.message || `${this.entityType} deleted successfully`);
                
                if (this.dynamicUpdate) {
                    this.removeFromDOM(id);
                } else {
                    setTimeout(() => window.location.reload(), 1000);
                }
            } else {
                NotificationManager.error('Error: ' + data.message);
            }
        } catch (error) {
            console.error(`Error deleting ${this.entityType}:`, error);
            NotificationManager.error(`An error occurred while deleting the ${this.entityType}`);
        }
    }
    
    removeFromDOM(id) {
        // Try multiple selector patterns to find the entity
        const selectors = [
            `[data-${this.entityType}-id="${id}"]`,
            `.${this.entityType}-card[data-id="${id}"]`,
            `#${this.entityType}-${id}`
        ];
        
        let entityElement = null;
        for (const selector of selectors) {
            entityElement = document.querySelector(selector);
            if (entityElement) break;
        }
        
        if (entityElement) {
            entityElement.style.transition = 'opacity 0.3s ease';
            entityElement.style.opacity = '0';
            setTimeout(() => {
                entityElement.remove();
                this.updateEntityCount();
            }, 300);
        } else {
            // If we can't find the element, just reload the page
            setTimeout(() => window.location.reload(), 1000);
        }
    }
    
    updateEntityCount() {
        // Update count displays if they exist
        const countElements = document.querySelectorAll(`[data-count-type="${this.entityType}"]`);
        countElements.forEach(element => {
            const currentCount = parseInt(element.textContent) || 0;
            const newCount = Math.max(0, currentCount - 1);
            element.textContent = newCount;
        });
    }
    
    // Generic CRUD operations
    async create(data) {
        try {
            const result = await ApiClient.post(`${this.apiEndpoint}?action=create_${this.entityType}`, data);
            
            if (result.success) {
                NotificationManager.success(result.message || `${this.entityType} created successfully`);
                return result;
            } else {
                NotificationManager.error('Error: ' + result.message);
                return null;
            }
        } catch (error) {
            console.error(`Error creating ${this.entityType}:`, error);
            NotificationManager.error(`An error occurred while creating the ${this.entityType}`);
            return null;
        }
    }
    
    async update(id, data) {
        try {
            const result = await ApiClient.post(`${this.apiEndpoint}?action=update_${this.entityType}`, { id, ...data });
            
            if (result.success) {
                NotificationManager.success(result.message || `${this.entityType} updated successfully`);
                return result;
            } else {
                NotificationManager.error('Error: ' + result.message);
                return null;
            }
        } catch (error) {
            console.error(`Error updating ${this.entityType}:`, error);
            NotificationManager.error(`An error occurred while updating the ${this.entityType}`);
            return null;
        }
    }
    
    async read(id) {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_${this.entityType}&id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                NotificationManager.error('Error: ' + data.message);
                return null;
            }
        } catch (error) {
            console.error(`Error reading ${this.entityType}:`, error);
            NotificationManager.error(`An error occurred while loading the ${this.entityType}`);
            return null;
        }
    }
}

// Make EntityManager globally available
window.EntityManager = EntityManager;

// Legacy compatibility functions
window.createEntityManager = (entityType, apiEndpoint, dynamicUpdate = true) => {
    return new EntityManager({ entityType, apiEndpoint, dynamicUpdate });
};

// Generic delete function for backward compatibility
window.deleteEntity = async (type, id, endpoint) => {
    const manager = new EntityManager({ entityType: type, apiEndpoint: endpoint });
    return manager.confirmDelete(id, 'this item');
};
