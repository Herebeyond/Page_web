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
                        suggestion.textContent = `${user.id} - ${user.username}`;
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
</script>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>