/**
 * Shared JavaScript utilities for all pages
 * Provides common functionality like notifications, loading indicators, etc.
 */

class NotificationManager {
    static show(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification-banner ${type}-notification`;
        
        const styles = {
            success: { bg: '#d4edda', text: '#155724', border: '#c3e6cb' },
            error: { bg: '#f8d7da', text: '#721c24', border: '#f5c6cb' },
            warning: { bg: '#fff3cd', text: '#856404', border: '#ffeaa7' },
            info: { bg: '#d1ecf1', text: '#0c5460', border: '#bee5eb' }
        };
        
        const style = styles[type] || styles.info;
        
        notification.style.cssText = `
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            z-index: 10000; background: ${style.bg}; color: ${style.text};
            border: 1px solid ${style.border}; border-radius: 4px;
            padding: 15px 20px; max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            opacity: 1; transition: opacity 0.3s ease;
        `;
        
        notification.innerHTML = `
            <span>${message}</span>
            <button style="margin-left: 15px; background: none; border: none; 
                          font-size: 18px; cursor: pointer; color: ${style.text};"
                    onclick="this.parentElement.remove()">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
    
    static success(message) { this.show(message, 'success'); }
    static error(message) { this.show(message, 'error'); }
    static warning(message) { this.show(message, 'warning'); }
    static info(message) { this.show(message, 'info'); }
}

class LoadingIndicator {
    static show() {
        if (document.getElementById('loadingIndicator')) return;
        
        const loader = document.createElement('div');
        loader.id = 'loadingIndicator';
        loader.innerHTML = `
            <div style="width: 50px; height: 50px; border: 5px solid #f3f3f3;
                        border-top: 5px solid #3498db; border-radius: 50%;
                        animation: spin 1s linear infinite;"></div>
        `;
        loader.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 9999;
            display: flex; justify-content: center; align-items: center;
        `;
        
        // Add spin animation if not exists
        if (!document.getElementById('spinKeyframes')) {
            const style = document.createElement('style');
            style.id = 'spinKeyframes';
            style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
            document.head.appendChild(style);
        }
        
        document.body.appendChild(loader);
    }
    
    static hide() {
        const loader = document.getElementById('loadingIndicator');
        if (loader) loader.remove();
    }
}

// Generic API handler
class ApiClient {
    static async request(url, options = {}) {
        LoadingIndicator.show();
        try {
            const response = await fetch(url, options);
            const data = await response.json();
            LoadingIndicator.hide();
            return data;
        } catch (error) {
            LoadingIndicator.hide();
            console.error('API Error:', error);
            NotificationManager.error('Network error occurred');
            throw error;
        }
    }
    
    static async post(url, data) {
        return this.request(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
    }
}

// Make utilities globally available
window.NotificationManager = NotificationManager;
window.LoadingIndicator = LoadingIndicator;
window.ApiClient = ApiClient;

// Legacy compatibility functions
window.showNotification = NotificationManager.show.bind(NotificationManager);
window.showSuccessMessage = NotificationManager.success.bind(NotificationManager);
window.showErrorMessage = NotificationManager.error.bind(NotificationManager);
window.showLoadingIndicator = LoadingIndicator.show;
window.hideLoadingIndicator = LoadingIndicator.hide;
