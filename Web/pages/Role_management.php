<?php
require_once "./blueprints/page_init.php";
require_once "./blueprints/gl_ap_start.php";
?>

<div class="content-page">
    <h1>🔐 Role Management System</h1>
    
    <?php if (in_array('admin', $user_roles)): ?>
        
        <!-- User Search and Selection Section -->
        <div class="admin-section">
            <h2>� Search and Select Users</h2>
            
            <div class="search-container">
                <div class="search-filters">
                    <div class="filter-group">
                        <label for="searchInput">Search Users:</label>
                        <input type="text" id="searchInput" placeholder="Search by username, email, or role..." class="search-input">
                    </div>
                    
                    <div class="filter-group">
                        <label for="roleFilter">Filter by Role:</label>
                        <select id="roleFilter" class="search-select">
                            <option value="">All roles</option>
                            <?php
                            $roles = getAllRoles($pdo);
                            foreach ($roles as $role) {
                                echo "<option value='{$role['role_name']}'>{$role['role_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="button" id="clearSearch" class="btn btn-secondary">Clear Search</button>
                    </div>
                </div>
                
                <div class="selection-controls">
                    <button type="button" id="selectAll" class="btn btn-secondary">Select All Visible</button>
                    <button type="button" id="deselectAll" class="btn btn-secondary">Deselect All</button>
                    <span id="selectionCount" class="selection-info">0 users selected</span>
                </div>
            </div>
            
            <!-- Users List with Selection -->
            <div class="users-list" id="usersList">
                <!-- Users will be populated here by JavaScript -->
            </div>
        </div>
        
        <!-- Role Assignment Section -->
        <div class="admin-section">
            <h2>👥 Assign Roles to Selected Users</h2>
            
            <div class="bulk-actions-container">
                <div id="selectedUsersDisplay" class="selected-users-display">
                    <p>No users selected. Please select users from the search above.</p>
                </div>
                
                <div class="bulk-role-actions">
                    <div class="form-container">
                        <form id="bulkRoleForm" class="admin-form">
                            <div class="form-group">
                                <label for="bulkRoleSelect">Select Role to Add/Remove:</label>
                                <select id="bulkRoleSelect" name="roleId" required>
                                    <option value="">Choose a role...</option>
                                    <?php
                                    foreach ($roles as $role) {
                                        echo "<option value='{$role['role_name']}'>{$role['role_name']} - {$role['role_description']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="action" value="bulk_add" class="btn btn-primary">➕ Add Role to Selected Users</button>
                                <button type="submit" name="action" value="bulk_remove" class="btn btn-danger">➖ Remove Role from Selected Users</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Role Management -->
        <div class="admin-section">
            <h2>⚙️ Manage Roles</h2>
            
            <div class="roles-list">
                <h3>Available Roles:</h3>
                <div class="roles-grid">
                    <?php foreach ($roles as $role): ?>
                        <div class="role-card <?= $role['is_active'] ? 'active' : 'inactive' ?>">
                            <h4><?= htmlspecialchars($role['role_name']) ?></h4>
                            <p><?= htmlspecialchars($role['role_description']) ?></p>
                            <div class="role-status">
                                Status: <?= $role['is_active'] ? '✅ Active' : '❌ Inactive' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-container">
                <h3>Create New Role:</h3>
                <form id="newRoleForm" class="admin-form">
                    <div class="form-group">
                        <label for="newRoleName">Role Name:</label>
                        <input type="text" id="newRoleName" name="roleName" required pattern="[a-zA-Z0-9_-]+" title="Only letters, numbers, underscores and hyphens allowed">
                    </div>
                    
                    <div class="form-group">
                        <label for="newRoleDescription">Description:</label>
                        <textarea id="newRoleDescription" name="roleDescription" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">➕ Create Role</button>
                </form>
            </div>
        </div>
        
    <?php else: ?>
        <div class="error-banner">
            <h2>Access Denied</h2>
            <p>You need administrator privileges to access this page.</p>
            <a href="Homepage.php" class="btn">← Back to Homepage</a>
        </div>
    <?php endif; ?>
</div>

<script>
// Global variables for user management
let allUsers = [];
let selectedUsers = [];
let filteredUsers = [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    setupEventListeners();
});

// Load all users from the server
function loadUsers() {
    fetch('./scriptes/role_management_api.php?action=get_all_users')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            allUsers = data.users;
            filteredUsers = [...allUsers];
            displayUsers();
        } else {
            showNotification('Error loading users: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error loading users: ' + error.message, 'error');
    });
}

// Set up event listeners
function setupEventListeners() {
    // Search input
    document.getElementById('searchInput').addEventListener('input', filterUsers);
    
    // Role filter
    document.getElementById('roleFilter').addEventListener('change', filterUsers);
    
    // Clear search
    document.getElementById('clearSearch').addEventListener('click', clearSearch);
    
    // Selection controls
    document.getElementById('selectAll').addEventListener('click', selectAllVisible);
    document.getElementById('deselectAll').addEventListener('click', deselectAll);
    
    // Bulk role form
    document.getElementById('bulkRoleForm').addEventListener('submit', handleBulkRoleAssignment);
    
    // New role form
    document.getElementById('newRoleForm').addEventListener('submit', handleNewRole);
}

// Filter users based on search and role filter
function filterUsers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    
    filteredUsers = allUsers.filter(user => {
        const matchesSearch = user.username.toLowerCase().includes(searchTerm) ||
                            user.email.toLowerCase().includes(searchTerm) ||
                            user.roles.some(role => role.toLowerCase().includes(searchTerm));
        
        const matchesRole = !roleFilter || user.roles.includes(roleFilter);
        
        return matchesSearch && matchesRole;
    });
    
    displayUsers();
}

// Clear search filters
function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('roleFilter').value = '';
    filteredUsers = [...allUsers];
    displayUsers();
}

// Display users in the list
function displayUsers() {
    const usersList = document.getElementById('usersList');
    
    if (filteredUsers.length === 0) {
        usersList.innerHTML = '<p class="no-users">No users found matching the search criteria.</p>';
        return;
    }
    
    let html = '<div class="users-grid">';
    
    filteredUsers.forEach(user => {
        const isSelected = selectedUsers.includes(user.id_user);
        const rolesHtml = user.roles.map(role => `<span class="role-badge">${role}</span>`).join('');
        
        html += `
            <div class="user-card ${isSelected ? 'selected' : ''}" data-user-id="${user.id_user}">
                <div class="user-checkbox">
                    <input type="checkbox" id="user_${user.id_user}" ${isSelected ? 'checked' : ''} 
                           onchange="toggleUserSelection(${user.id_user})">
                </div>
                <div class="user-info">
                    <h4>${escapeHtml(user.username)}</h4>
                    <p class="user-email">${escapeHtml(user.email)}</p>
                    <div class="user-roles">
                        ${rolesHtml || '<span class="no-roles">No roles assigned</span>'}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    usersList.innerHTML = html;
}

// Toggle user selection
function toggleUserSelection(userId) {
    const index = selectedUsers.indexOf(userId);
    
    if (index > -1) {
        selectedUsers.splice(index, 1);
    } else {
        selectedUsers.push(userId);
    }
    
    updateSelectionDisplay();
    updateSelectedUsersDisplay();
}

// Select all visible users
function selectAllVisible() {
    filteredUsers.forEach(user => {
        if (!selectedUsers.includes(user.id_user)) {
            selectedUsers.push(user.id_user);
        }
    });
    
    updateSelectionDisplay();
    updateSelectedUsersDisplay();
    displayUsers(); // Refresh to show checkboxes
}

// Deselect all users
function deselectAll() {
    selectedUsers = [];
    updateSelectionDisplay();
    updateSelectedUsersDisplay();
    displayUsers(); // Refresh to show checkboxes
}

// Update selection count display
function updateSelectionDisplay() {
    const count = selectedUsers.length;
    document.getElementById('selectionCount').textContent = `${count} user${count !== 1 ? 's' : ''} selected`;
}

// Update selected users display
function updateSelectedUsersDisplay() {
    const display = document.getElementById('selectedUsersDisplay');
    
    if (selectedUsers.length === 0) {
        display.innerHTML = '<p>No users selected. Please select users from the search above.</p>';
        return;
    }
    
    const selectedUserData = allUsers.filter(user => selectedUsers.includes(user.id_user));
    
    let html = '<h3>Selected Users:</h3><div class="selected-users-list">';
    
    selectedUserData.forEach(user => {
        const rolesHtml = user.roles.map(role => `<span class="role-badge">${role}</span>`).join('');
        html += `
            <div class="selected-user-item">
                <strong>${escapeHtml(user.username)}</strong> (${escapeHtml(user.email)})
                <div class="user-current-roles">${rolesHtml || '<span class="no-roles">No roles</span>'}</div>
            </div>
        `;
    });
    
    html += '</div>';
    display.innerHTML = html;
}

// Handle bulk role assignment
function handleBulkRoleAssignment(e) {
    e.preventDefault();
    
    if (selectedUsers.length === 0) {
        showNotification('Please select at least one user', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    const action = e.submitter.value;
    
    formData.append('action', action);
    formData.append('userIds', JSON.stringify(selectedUsers));
    
    fetch('./scriptes/role_management_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadUsers(); // Refresh user data
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('An error occurred: ' + error.message, 'error');
    });
}

// Handle new role creation
function handleNewRole(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'create_role');
    
    fetch('./scriptes/role_management_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            e.target.reset();
            // Refresh the page to show new role
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('An error occurred: ' + error.message, 'error');
    });
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        color: white;
        font-weight: bold;
        z-index: 1000;
        max-width: 300px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;
    
    // Set background color based on type
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#28a745';
            break;
        case 'error':
            notification.style.backgroundColor = '#dc3545';
            break;
        case 'info':
            notification.style.backgroundColor = '#17a2b8';
            break;
        default:
            notification.style.backgroundColor = '#6c757d';
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
</script>

<?php
require_once "./blueprints/gl_ap_end.php";
?>
