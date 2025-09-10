/**
 * Tab Management JavaScript
 * Handles tab switching functionality for admin interfaces
 */

class TabManager {
    constructor() {
        this.init();
    }
    
    init() {
        // Auto-initialize tabs when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeTabHandlers();
        });
    }
    
    initializeTabHandlers() {
        // Find all tab buttons and add click handlers
        const tabButtons = document.querySelectorAll('[data-tab], .tab-button, .tab-btn');
        tabButtons.forEach(button => {
            if (!button.hasAttribute('data-tab-initialized')) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tabName = button.getAttribute('data-tab') || 
                                   this.extractTabNameFromOnclick(button.getAttribute('onclick'));
                    if (tabName) {
                        this.showTab(tabName);
                    }
                });
                button.setAttribute('data-tab-initialized', 'true');
            }
        });
    }
    
    extractTabNameFromOnclick(onclickString) {
        if (!onclickString) return null;
        const match = onclickString.match(/showTab\(['"]([^'"]+)['"]\)/);
        return match ? match[1] : null;
    }
    
    showTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
            content.classList.remove('active');
        });
        
        // Remove active class from all tab buttons
        const tabButtons = document.querySelectorAll('.tab-button, .tab-btn, [data-tab]');
        tabButtons.forEach(button => {
            button.classList.remove('active');
        });
        
        // Show the selected tab content (try multiple ID patterns)
        const possibleIds = [tabName, `${tabName}-tab`, `tab-${tabName}`];
        let selectedTab = null;
        
        for (const id of possibleIds) {
            selectedTab = document.getElementById(id);
            if (selectedTab) break;
        }
        
        if (selectedTab) {
            selectedTab.style.display = 'block';
            selectedTab.classList.add('active');
        }
        
        // Add active class to the clicked button
        const activeButton = document.querySelector(`[data-tab="${tabName}"], [onclick*="showTab('${tabName}')"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
        
        // Trigger custom event for other components
        document.dispatchEvent(new CustomEvent('tabChanged', { 
            detail: { tabName, selectedTab } 
        }));
    }
    
    // Get currently active tab
    getCurrentTab() {
        const activeTab = document.querySelector('.tab-content.active');
        return activeTab ? activeTab.id : null;
    }
    
    // Enable/disable tab
    setTabEnabled(tabName, enabled = true) {
        const tabButton = document.querySelector(`[data-tab="${tabName}"], [onclick*="showTab('${tabName}')"]`);
        if (tabButton) {
            tabButton.disabled = !enabled;
            tabButton.classList.toggle('disabled', !enabled);
        }
    }
}

// Initialize tab manager globally
window.tabManager = new TabManager();

// Legacy compatibility function
window.showTab = (tabName) => {
    window.tabManager.showTab(tabName);
};

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TabManager;
}
