<?php

// ============================================================================
// CONSTANTS - Common String Literals
// ============================================================================

// Path constants
const IMAGES_PLACES_PATH = 'images/places/';
const REDIRECT_LOCATION_LOGIN = 'Location: login.php';
const REDIRECT_LOCATION_CHARACTER_ADD = 'Location: Character_add.php';
const REDIRECT_LOCATION_RACE_ADD = 'Location: Race_add.php';
const REDIRECT_LOCATION_SPECIE_ADD = 'Location: Specie_add.php';
const REDIRECT_LOCATION_MAP_VIEW = 'Location: map_view.php';
const LOCATION_MAP_VIEW = 'Location: map_view.php';

// Display constants
const NOT_SPECIFIED = 'Not specified';
const ACCESS_DENIED_ADMIN_REQUIRED = 'Access denied - Admin required';
const INVALID_SLUG_FORMAT = 'Invalid slug format';
const INVALID_DIRECTORY_PATH = 'Invalid directory path';
const DATABASE_ERROR_PREFIX = 'Database error: ';

// Database query constants
const SQL_SELECT_PLACE_NAME_BY_ID = 'SELECT name_IP FROM interest_points WHERE id_IP = ?';

// ============================================================================
// SECURITY FUNCTIONS - Path Traversal Prevention
// ============================================================================

/**
 * Validates and sanitizes a slug to prevent path traversal attacks
 * 
 * @param string $slug The slug to validate
 * @return string|false Returns sanitized slug or false if invalid
 */
function validateAndSanitizeSlug($slug) {
    if (empty($slug) || !is_string($slug)) {
        return false;
    }
    
    // Length validation to prevent buffer overflow attacks
    if (strlen($slug) > 100) {
        return false;
    }
    
    // Remove any path traversal sequences and dangerous characters
    $slug = str_replace(['../', '.\\', '..\\', './', '\\', '~'], '', $slug);
    
    // Only allow safe characters: letters, numbers, hyphens, underscores
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $slug)) {
        return false;
    }
    
    return $slug;
}

/**
 * Constructs a safe path within a base directory to prevent directory traversal
 * 
 * @param string $slug The validated slug
 * @param string $baseDir The base directory path
 * @return string|false Returns safe path or false if invalid
 */
function constructSafePlacePath($slug, $baseDir) {
    $sanitizedSlug = validateAndSanitizeSlug($slug);
    if ($sanitizedSlug === false) {
        return false;
    }
    
    $targetPath = $baseDir . DIRECTORY_SEPARATOR . $sanitizedSlug;
    $realBaseDir = realpath($baseDir);
    
    if ($realBaseDir === false) {
        return false;
    }
    
    // Handle case where directory doesn't exist yet
    if (is_dir($targetPath)) {
        $realTargetPath = realpath($targetPath);
        if ($realTargetPath === false || strpos($realTargetPath, $realBaseDir) !== 0) {
            return false;
        }
    } else {
        // For non-existent paths, verify parent directory is safe
        $parentPath = dirname($targetPath);
        if (realpath($parentPath) !== $realBaseDir) {
            return false;
        }
    }
    
    return $targetPath;
}

/**
 * Creates a secure slug from a name (for place names, etc.)
 * 
 * @param string $name The name to convert to slug
 * @return string|false Returns secure slug or false if invalid
 */
function createSecureSlug($name) {
    if (empty($name) || !is_string($name)) {
        return false;
    }
    
    // Convert to lowercase, replace spaces and special chars with hyphens
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug); // Remove multiple consecutive hyphens
    $slug = trim($slug, '-'); // Remove leading/trailing hyphens
    
    // Additional validation for security
    return validateAndSanitizeSlug($slug);
}

/**
 * Validates file extension for safe file uploads
 * 
 * @param string $filename The filename to validate
 * @param array $allowedExtensions Array of allowed extensions
 * @return bool Returns true if extension is safe
 */
function validateFileExtension($filename, $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowedExtensions);
}

/**
 * Securely parses JSON input with error handling
 * 
 * @param string $jsonInput Raw JSON input
 * @return array|false Returns parsed array or false on error
 */
function parseSecureJsonInput($jsonInput = null) {
    if ($jsonInput === null) {
        $jsonInput = file_get_contents('php://input');
    }
    
    if ($jsonInput === false || empty($jsonInput)) {
        return false;
    }
    
    try {
        $data = json_decode($jsonInput, true, 512, JSON_THROW_ON_ERROR);
        
        if (!is_array($data)) {
            return false;
        }
        
        return $data;
    } catch (JsonException $e) {
        error_log('JSON parsing error: ' . $e->getMessage());
        return false;
    }
}

// ============================================================================
// EXISTING FUNCTIONS
// ============================================================================

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

function updateUserProfile($userId, $username, $email, $password) {
    global $pdo;

    // Sanitize input (already done before, but double check)
    $username = trim($username);
    $email = trim($email);

    // Prepare SQL and parameters
    if (!empty($password)) {
        // If password is provided, hash it and update all fields
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
        $params = [$username, $email, $hashedPassword, $userId];
    } else {
        // If password is empty, update only username and email
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $params = [$username, $email, $userId];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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

    function fetchSpecieInfo() { // Fonction for getting and display the information of the specie selected in the options of the select in the form thanks to the button Fetch Info in Specie_add.php
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

    function fetchRaceInfo() { // Fonction for getting and display the information of the race selected in the options of the select in the form thanks to the button Fetch Info in Race_add.php
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

    function fetchCharacterInfo() { // Fonction for getting and display the information of the character selected in the options of the select in the form thanks to the button Fetch Info in Character_add.php
        var characterName = document.querySelector('select[name="character_name"]').value; // Get the selected character name

        if (characterName) {
            fetch('scriptes/fetch_character_info.php?character=' + encodeURIComponent(characterName)) // Make a GET request to the backend
                .then(response => {
                    console.log("Raw response:", response); // Log the raw response
                    if (!response.ok) { // Check if the response is not OK
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse the response as JSON
                })
                .then(data => {
                    console.log("Parsed data:", data); // Log the parsed JSON data
                    if (data.success) { // If the backend indicates success
                        // Build the HTML for the character information
                        let iconHtml = data.icon ? `<p>Icon link: ${escapeHtml(data.icon)}</p><p>Icon: <img id="imgEdit" src="../images/${escapeHtml(data.icon)}" alt="Display icon error"></p>` : '<p>Icon does not exist</p>';
                        let ageHtml = data.age ? `<p>Age: ${escapeHtml(String(data.age))}</p>` : '<p>Age does not exist</p>';
                        let countryHtml = data.country ? `<p>Country: ${escapeHtml(data.country)}</p>` : '<p>Country does not exist</p>';
                        let habitatHtml = data.habitat ? `<p>Habitat: ${escapeHtml(data.habitat)}</p>` : '<p>Habitat does not exist</p>';
                        let correspondenceHtml = data.correspondence ? `<p>Correspondence: ${escapeHtml(data.correspondence)}</p>` : '<p>Correspondence does not exist</p>';

                        // Display the character information in the characterInfo div
                        document.getElementById('characterInfo').innerHTML = iconHtml + ageHtml + countryHtml + habitatHtml + correspondenceHtml;
                    } else {
                        console.warn("Backend error message:", data.message); // Log the error message from the backend
                        document.getElementById('characterInfo').innerHTML = '<p style="color:red;">' + escapeHtml(data.message) + '</p>';
                    }
                })
                .catch(error => {
                    console.error("Fetch operation error:", error); // Log any errors that occur during the fetch operation
                    document.getElementById('characterInfo').innerHTML = '<p style="color:red;">Error fetching character info</p>';
                });
        } else {
            console.warn("No character selected"); // Log a warning if no character is selected
            document.getElementById('characterInfo').innerHTML = '<p style="color:red;">Please select a character</p>';
        }
    }

    function fetchUserInfo() { // Function to fetch and display user information
        var username = document.getElementById('usernameSearch').value.trim(); // Get the username from the input field

        if (username) {
            fetch('scriptes/fetch_user_info.php?user=' + encodeURIComponent(username)) // Make a GET request to the backend
                .then(response => {
                    if (!response.ok) { // Check if the response is not OK
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse the response as JSON
                })
                .then(data => {
                    if (data.success) { // If the backend indicates success
                        // Build the HTML for the user information
                        let iconHtml = data.icon ? `<p>Icon link: ${escapeHtml(data.icon)}</p><p>Icon: <img id="imgEdit" src="../images/small_icon/${escapeHtml(data.icon)}" alt="User Icon"></p>` : "<p>Doesn't have an icon</p>";
                        let idHtml = data.id ? `<p>ID: ${data.id}</p>` : '<p>Error with the ID data</p>';
                        let usernameHtml = data.username ? `<p>Username: ${escapeHtml(data.username)}</p>` : '<p>Error with the Username data</p>';
                        // Display all roles as a list, similar to User_profil.php
                        let rolesHtml = "<br><div><h3>Roles :</h3><ul>";
                        if (Array.isArray(data.roles) && data.roles.length > 0) {
                            data.roles.forEach(function(role) {
                                rolesHtml += `<li>${escapeHtml(role)}</li>`;
                            });
                        } else {
                            rolesHtml += "<li>Doesn't have any role</li>";
                        }
                        rolesHtml += "</ul></div>";
                        let emailHtml = data.email ? `<p>Email: ${escapeHtml(data.email)}</p>` : "<p>Doesn't have an email</p>";
                        let createdAtHtml = data.created_at ? `<p>&emsp;Created at: ${escapeHtml(data.created_at)}</p>` : '<p>Error with the Created_at data</p>';
                        let lastUpdatedAtHtml = data.last_updated_at ? `<p>&emsp;Last updated at: ${escapeHtml(data.last_updated_at)}</p>` : '<p>Error with the Last_updated_at data</p>';
                        let blockedHtml = data.blocked ? `<p>Is Blocked</p><p>&emsp;The User was Blocked at : ${escapeHtml(data.blocked)}</p>` : "<p>Isn't Blocked</p>";

                        // Display the user information in the userInfo div
                        document.getElementById('userInfo').innerHTML = idHtml + usernameHtml + iconHtml + emailHtml + createdAtHtml + lastUpdatedAtHtml + blockedHtml + rolesHtml;
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
            console.warn("No username entered"); // Log a warning if no username is entered
            document.getElementById('userInfo').innerHTML = '<p style="color:red;">Please enter a username</p>';
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

    function blockUser() { // Function to block the user entered in the input field
        var username = document.getElementById('usernameSearch').value.trim(); // Get the username from the input field

        if (username) {
            // Rechercher l'ID utilisateur correspondant au nom d'utilisateur
            fetch('scriptes/search_user.php?query=' + encodeURIComponent(username))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.users.length > 0) {
                        const userId = data.users[0].id; // Supposons que le premier utilisateur correspond

                        // Envoyer une requête pour bloquer l'utilisateur
                        fetch('scriptes/block_user.php?user=' + encodeURIComponent(userId))
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert("User blocked successfully");
                                    window.location.reload();
                                } else {
                                    alert("Error blocking user: " + data.message);
                                }
                            })
                            .catch(error => {
                                alert("Error blocking user");
                            });
                    } else {
                        alert("User not found");
                    }
                })
                .catch(error => {
                    alert("Error searching for user");
                });
        } else {
            alert("Please enter a username");
        }
    }

    function unblockUser() { // Function to unblock the user entered in the input field
        var username = document.getElementById('usernameSearch').value.trim(); // Get the username from the input field

        if (username) {
            // Rechercher l'ID utilisateur correspondant au nom d'utilisateur
            fetch('scriptes/search_user.php?query=' + encodeURIComponent(username))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.users.length > 0) {
                        const userId = data.users[0].id; // Supposons que le premier utilisateur correspond

                        // Envoyer une requête pour débloquer l'utilisateur
                        fetch('scriptes/unblock_user.php?user=' + encodeURIComponent(userId))
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert("User unblocked successfully");
                                    window.location.reload();
                                } else {
                                    alert("Error unblocking user: " + data.message);
                                }
                            })
                            .catch(error => {
                                alert("Error unblocking user");
                            });
                    } else {
                        alert("User not found");
                    }
                })
                .catch(error => {
                    alert("Error searching for user");
                });
        } else {
            alert("Please enter a username");
        }
    }

    
</script>

