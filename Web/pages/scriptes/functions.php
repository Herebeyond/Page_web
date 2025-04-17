<?php



function isImageLinkValid($url) {
    // Convert relative URL to absolute path
    $absolutePath = realpath(__DIR__ . '/../' . $url);

    // Check if the file exists and is an image
    if ($absolutePath && file_exists($absolutePath)) {
        $fileInfo = getimagesize($absolutePath);
        return $fileInfo !== false;
    }
    return false;
}

// Function to sanitize output
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}



?>
<script>

    function escapeHtml(text) { // Fonction to escape HTML characters in fetchSpecieInfo and fetchRaceInfo
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function nl2br(str) { // Fonction to convert new lines to <br> tags in fetchSpecieInfo and fetchRaceInfo
        return str.replace(/\n/g, '<br>');
    }

    function fetchSpecieInfo() { // Fonction for getting and display the information of the specie selected in the option of the select in the form thanks to the button Fetch Info
        var specieName = document.querySelector('select[name="SpecieName"]').value;
        if (specieName) {
            fetch('scriptes/fetch_specie_info.php?specie=' + encodeURIComponent(specieName))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        let iconHtml = data.icon ? `<p>Icon link: ${escapeHtml(data.icon)}</p><p>Icon: <img id="imgEdit" src="../images/${escapeHtml(data.icon)}" alt="Specie Icon"></p>` : '<p>Icon does not exist</p>';
                        let contentHtml = data.content ? `<p>Content: <br>${nl2br(escapeHtml(data.content))}</p>` : '<p>Content does not exist</p>';
                        document.getElementById('specieInfo').innerHTML = iconHtml + contentHtml;
                    } else {
                        document.getElementById('specieInfo').innerHTML = '<p style="color:red;">' + escapeHtml(data.message) + '</p>';
                    }
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                    document.getElementById('specieInfo').innerHTML = '<p style="color:red;">Error fetching specie info</p>';
                });
        } else {
            document.getElementById('specieInfo').innerHTML = '<p style="color:red;">Please select a specie</p>';
        }
    }

    function fetchRaceInfo() { // Fonction for getting and display the information of the race selected in the option of the select in the form thanks to the button Fetch Info
        var raceName = document.querySelector('select[name="Race_name"]').value; // Get the selected race name

        if (raceName) {
            fetch('scriptes/fetch_race_info.php?race=' + encodeURIComponent(raceName)) // Make a GET request to the backend
                .then(response => {
                    if (!response.ok) { // Check if the response is not OK
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse the response as JSON
                })
                .then(data => {
                    if (data.success) { // If the backend indicates success
                        // Build the HTML for the race information
                        let correspondenceHtml = data.correspondence ? `<p>Correspondence: ${escapeHtml(data.correspondence)}</p>` : '<p>Correspondence does not exist</p>';
                        let icon = data.icon ? data.icon.replace(/ /g, '_') : '';
                        let iconHtml = icon ? `<p>Icon link: ${escapeHtml(icon)}</p><p>Icon: <img id="imgEdit" src="../images/${escapeHtml(icon)}" alt="Race Icon"></p>` : '<p>Icon does not exist</p>';
                        let contentHtml = data.content ? `<p>Content: <br>${nl2br(escapeHtml(data.content))}</p>` : '<p>Content does not exist</p>';
                        let lifespanHtml = data.lifespan ? `<p>Lifespan: ${escapeHtml(data.lifespan)}</p>` : '<p>Lifespan does not exist</p>';
                        let homeworldHtml = data.homeworld ? `<p>Homeworld: ${escapeHtml(data.homeworld)}</p>` : '<p>Homeworld does not exist</p>';
                        let countryHtml = data.country ? `<p>Country: ${escapeHtml(data.country)}</p>` : '<p>Country does not exist</p>';
                        let habitatHtml = data.habitat ? `<p>Habitat: ${escapeHtml(data.habitat)}</p>` : '<p>Habitat does not exist</p>';

                        // Display the race information in the raceInfo div
                        document.getElementById('raceInfo').innerHTML = correspondenceHtml + iconHtml + lifespanHtml + homeworldHtml + countryHtml + habitatHtml + contentHtml;
                    } else {
                        console.warn("Backend error message:", data.message); // Log the error message from the backend
                        document.getElementById('raceInfo').innerHTML = '<p style="color:red;">' + escapeHtml(data.message) + '</p>';
                    }
                })
                .catch(error => {
                    console.error("Fetch operation error:", error); // Log any errors that occur during the fetch operation
                    document.getElementById('raceInfo').innerHTML = '<p style="color:red;">Error fetching race info</p>';
                });
        } else {
            console.warn("No race selected"); // Log a warning if no race is selected
            document.getElementById('raceInfo').innerHTML = '<p style="color:red;">Please select a race</p>';
        }
    }

    function fetchUserInfo() { // Fonction for getting and display the information of the user selected in the option of the select in the form thanks to the button Fetch Info
        var userId = document.querySelector('select[name="User"]').value; // Get the selected user ID

        if (userId) {
            fetch('scriptes/fetch_user_info.php?user=' + encodeURIComponent(userId)) // Make a GET request to the backend
                .then(response => {
                    if (!response.ok) { // Check if the response is not OK
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse the response as JSON
                })
                .then(data => {
                    if (data.success) { // If the backend indicates success
                        // Build the HTML for the user information
                        let iconHtml = data.icon ? `<p>Icon link: ${escapeHtml(data.icon)}</p><p>Icon: <img id="imgEdit" src="../images/${escapeHtml(data.icon)}" alt="User Icon"></p>` : '<p>Icon does not exist</p>';
                        let idHtml = data.id ? `<p>ID: ${data.id}</p>` : '<p>ID does not exist</p>';
                        let usernameHtml = data.username ? `<p>Username: ${escapeHtml(data.username)}</p>` : '<p>Username does not exist</p>';
                        let blockedHtml = data.blocked ? `<p>Is Blocked</p>` : "<p>Isn't Blocked</p>";
                        let adminHtml = data.admin ? `<p>Is Admin</p>` : "<p>Isn't Admin</p>";
                        let emailHtml = data.email ? `<p>Email: ${escapeHtml(data.email)}</p>` : '<p>Email does not exist</p>';
                        // Format the created_at and last_updated_at fields
                        data.created_at = new Date(data.created_at).toLocaleString('fr-FR', { timeZone: 'UTC' });
                        data.last_updated_at = new Date(data.last_updated_at).toLocaleString('fr-FR', { timeZone: 'UTC' });
                        // Format style "lundi 01 janvier 2023 12:00:00"
                        data.created_at = data.created_at.replace(/(\d{1,2})\/(\d{1,2})\/(\d{4})/, function(match, p1, p2, p3) {
                            const date = new Date(p3, p2 - 1, p1);
                            return date.toLocaleString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        });
                        data.last_updated_at = data.last_updated_at.replace(/(\d{1,2})\/(\d{1,2})\/(\d{4})/, function(match, p1, p2, p3) {
                            const date = new Date(p3, p2 - 1, p1);
                            return date.toLocaleString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        });
                        
                        let createdAtHtml = data.created_at ? `<p>Created at: ${escapeHtml(data.created_at)}</p>` : '<p>Created at does not exist</p>';
                        let lastUpdatedAtHtml = data.last_updated_at ? `<p>Last updated at: ${escapeHtml(data.last_updated_at)}</p>` : '<p>Last updated at does not exist</p>';

                        // Display the user information in the userInfo div
                        document.getElementById('userInfo').innerHTML = idHtml + usernameHtml + iconHtml + blockedHtml + adminHtml + emailHtml + createdAtHtml + lastUpdatedAtHtml;
                    } else {
                        console.warn("Backend error message:", data.message); // Log the error message from the backend
                        document.getElementById('userInfo').innerHTML = '<p style="color:red;">' + escapeHtml(data.message) + '</p>';
                    }
                })
                .catch(error => {
                    console.error("Fetch operation error:", error); // Log any errors that occur during the fetch operation
                    document.getElementById('userInfo').innerHTML = '<p style="color:red;">Error fetching user info</p>';
                });
        } else {
            console.warn("No user selected"); // Log a warning if no user is selected
            document.getElementById('userInfo').innerHTML = '<p style="color:red;">Please select a user</p>';
        }
        

    }

    function confirmSubmit() { // Fonction to confirm or cancel the submission of the form
        return confirm("Are you sure you want to update it?");
    }

    function confirmSpecieDelete() { // Fonction to confirm or cancel the deletion of the Specie
        if (confirm("Are you sure you want to delete the specie?")) {
            var specieName = document.querySelector('select[name="SpecieName"]').value;
            if (specieName) {
                fetch('scriptes/delete_specie.php?specie=' + encodeURIComponent(specieName))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Specie deleted successfully");
                            window.location.reload();
                        } else {
                            alert("Error deleting specie: " + data.message);
                        }
                    })
                    .catch(error => {
                        alert("Error deleting specie");
                    });
            } else {
                alert("Please select a specie");
            }
        }
    }

    function confirmRaceDelete() { // Fonction to confirm or cancel the deletion of the Race
        if (confirm("Are you sure you want to delete the race?")) {
            var raceName = document.querySelector('select[name="Race_name"]').value;
            if (raceName) {
                fetch('scriptes/delete_race.php?race=' + encodeURIComponent(raceName))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("race deleted successfully");
                            window.location.reload();
                        } else {
                            alert("Error deleting race: " + data.message);
                        }
                    })
                    .catch(error => {
                        alert("Error deleting race");
                    });
            } else {
                alert("Please select a race");
            }
        }
    }

    function confirmUserDelete() {
        if (confirm("Are you sure you want to delete the user?")) {
            var userId = document.querySelector('select[name="User"]').value;
            if (userId) {
                fetch('scriptes/delete_user.php?user=' + encodeURIComponent(userId))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("User deleted successfully");
                            window.location.reload();
                        } else {
                            alert("Error deleting user: " + data.message);
                        }
                    })
                    .catch(error => {
                        alert("Error deleting user");
                    });
            } else {
                alert("Please select a user");
            }
        }
    }
    
</script>
