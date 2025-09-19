<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>

<div id="mainText"> <!-- Right div -->
    <button id="Return" onclick="window.history.back()">Return</button><br>

    <h2>Admin page</h2>
    <form method="POST" action="Admin.php" onsubmit="return validateForm()">
        <label for="usernameSearch">Search for a user</label>
        <input type="text" id="usernameSearch" name="username" placeholder="Enter username" oninput="searchUser(this.value)">
        <div id="userSuggestions" style="border: 1px solid #ccc; max-height: 150px; overflow-y: auto;"></div>
        <br><br>
        <button type="submit">Submit</button>
        <button type="button" onclick="fetchUserInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="blockUser()">Block User</button><br>
        <button type="button" onclick="unblockUser()">Unblock User</button><br><br>
    </form><br>
    <div id="userInfo"></div>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<p style="color:red;">' . sanitize_output($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<p style="color:green;">' . sanitize_output($_SESSION['success']) . '</p>';
        unset($_SESSION['success']);
    }
    ?>
</div>

<script>

    // Function to fetch user in real-time in a search bar
    function searchUser(query) {
        const suggestionsDiv = document.getElementById('userSuggestions');
        suggestionsDiv.innerHTML = ''; // Clear previous suggestions

        if (query.length < 2) {
            return; // Don't search for very short queries
        }

        fetch(`scriptes/search_user.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.users.length > 0) {
                    data.users.forEach(user => {
                        const suggestion = document.createElement('div');
                        suggestion.textContent = `${user.id_user} - ${user.username}`;
                        suggestion.style.cursor = 'pointer';
                        suggestion.onclick = () => {
                            document.getElementById('usernameSearch').value = user.username;
                            suggestionsDiv.innerHTML = ''; // Clear suggestions
                        };
                        suggestionsDiv.appendChild(suggestion);
                    });
                } else {
                    suggestionsDiv.innerHTML = '<div>No users found</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching user suggestions:', error);
            });
    }
    
    // Function to fetch user information
    function fetchUserInfo() {
        const username = document.getElementById('usernameSearch').value.trim();
        const userInfoDiv = document.getElementById('userInfo');
        
        if (!username) {
            userInfoDiv.innerHTML = '<p style="color: red;">Please enter a username first</p>';
            return;
        }
        
        fetch(`scriptes/fetch_user_info.php?user=${encodeURIComponent(username)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    userInfoDiv.innerHTML = `
                        <h3>User Information:</h3>
                        <p><strong>ID:</strong> ${user.id_user}</p>
                        <p><strong>Username:</strong> ${user.username}</p>
                        <p><strong>Email:</strong> ${user.email}</p>
                        <p><strong>Active:</strong> ${user.is_active ? 'Yes' : 'No'}</p>
                        <p><strong>Blocked:</strong> ${user.blocked ? user.blocked : 'No'}</p>
                        <p><strong>Created:</strong> ${user.created_at}</p>
                    `;
                } else {
                    userInfoDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching user info:', error);
                userInfoDiv.innerHTML = '<p style="color: red;">Error fetching user information</p>';
            });
    }
    
    // Function to block a user
    function blockUser() {
        const username = document.getElementById('usernameSearch').value.trim();
        const userInfoDiv = document.getElementById('userInfo');
        
        if (!username) {
            userInfoDiv.innerHTML = '<p style="color: red;">Please enter a username first</p>';
            return;
        }
        
        if (!confirm(`Are you sure you want to block user "${username}"?`)) {
            return;
        }
        
        // First get user ID from username
        fetch(`scriptes/fetch_user_info.php?user=${encodeURIComponent(username)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const userId = data.user.id_user;
                    return fetch(`scriptes/block_user.php?user=${userId}`);
                } else {
                    throw new Error(data.message);
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userInfoDiv.innerHTML = '<p style="color: green;">User blocked successfully</p>';
                    fetchUserInfo(); // Refresh user info
                } else {
                    userInfoDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error blocking user:', error);
                userInfoDiv.innerHTML = '<p style="color: red;">Error blocking user</p>';
            });
    }
    
    // Function to unblock a user
    function unblockUser() {
        const username = document.getElementById('usernameSearch').value.trim();
        const userInfoDiv = document.getElementById('userInfo');
        
        if (!username) {
            userInfoDiv.innerHTML = '<p style="color: red;">Please enter a username first</p>';
            return;
        }
        
        if (!confirm(`Are you sure you want to unblock user "${username}"?`)) {
            return;
        }
        
        // First get user ID from username
        fetch(`scriptes/fetch_user_info.php?user=${encodeURIComponent(username)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const userId = data.user.id_user;
                    return fetch(`scriptes/unblock_user.php?user=${userId}`);
                } else {
                    throw new Error(data.message);
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userInfoDiv.innerHTML = '<p style="color: green;">User unblocked successfully</p>';
                    fetchUserInfo(); // Refresh user info
                } else {
                    userInfoDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error unblocking user:', error);
                userInfoDiv.innerHTML = '<p style="color: red;">Error unblocking user</p>';
            });
    }
    
    // Function to validate the form
    function validateForm() {
        const username = document.getElementById('usernameSearch').value.trim();
        if (!username) {
            alert('Please enter a username');
            return false;
        }
        return true;
    }
</script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>