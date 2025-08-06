<?php

require_once "./blueprints/page_init.php"; // Includes the page initialization file

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    $characterName = isset($_POST['character_name_Input']) ? trim($_POST['character_name_Input']) : null; // Trim whitespace from user input
    $cor = $pdo->prepare("SELECT id_race FROM races WHERE race_name = ?");
    $cor->execute([$_POST['correspondence']]);
    $correspondence = $cor->fetchColumn(); // Get the id_race of the selected correspondence

    if ($correspondence === false) {
        $_SESSION['error'] = "Correspondence error: Race not found";
        header('Location: Character_add.php');
        exit;
    }

    $characterId = $_GET['character'] ?? null;
    if ($characterId) {
        $selected = $pdo->prepare("SELECT character_name FROM characters WHERE id = ?");
        $selected->execute([$characterId]);
        $selectedCharacter = $selected->fetchColumn();
    } else {
        $selectedCharacter = null;
    }

    $characterAge = isset($_POST['age']) ? trim($_POST['age']) : null;
    $characterCountry = isset($_POST['country']) ? trim($_POST['country']) : null;
    $characterHabitat = isset($_POST['habitat']) ? trim($_POST['habitat']) : null;

    // Handle file upload for the character icon
    $characterIcon = null;
    if (isset($_FILES['icon_character']) && $_FILES['icon_character']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../images/'; // Define the target directory (relative to the current file)
        $fileInfo = pathinfo($_FILES['icon_character']['name']); // Get file information
        $extension = strtolower($fileInfo['extension']); // Get the file extension and convert it to lowercase
        $fileNameWithoutExtension = basename($fileInfo['filename']); // Get the filename without the extension
        $uniqueName = $fileNameWithoutExtension . '_' . uniqid() . '.' . $extension; // Generate a unique name with the same extension
        $uploadFile = $uploadDir . $uniqueName; // Define the target file path

        // Ensure the images directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['icon_character']['tmp_name'], $uploadFile)) { // Check if the file was moved successfully
            $characterIcon = $uniqueName; // Save the unique name to the database
        } else {
            $_SESSION['error'] = "Failed to upload the character icon.";
            header('Location: Character_add.php');
            exit;
        }

        if (isset($_FILES['icon_character'])) {
            if ($_FILES['icon_character']['error'] === UPLOAD_ERR_INI_SIZE) {
                $_SESSION['error'] = "The uploaded file exceeds the maximum allowed size.";
                header('Location: Character_add.php');
                exit;
            } elseif ($_FILES['icon_character']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "An error occurred during the file upload.";
                header('Location: Character_add.php');
                exit;
            }
        }
    }

    // Retrieve the character name from the database
    $stmt = $pdo->prepare("SELECT * FROM characters WHERE character_name = ?");
    $stmt->execute([$characterName]);
    $character = $stmt->fetch();

    // To update the character
    if (isset($_POST['character_name']) && ($_POST['character_name'] != "" || $_POST['character_name'] != null)) {
        $fields = [];
        $params = [];
        $characterName = $_POST['character_name'];

        if ($correspondence !== '' && $correspondence != null) {
            $fields[] = 'correspondence = ?';
            $params[] = $correspondence;
        }
        if ($characterIcon !== '' && $characterIcon != null) {
            $fields[] = 'icon_character = ?';
            $params[] = $characterIcon;
        }
        if ($characterAge !== '' && $characterAge != null) {
            $fields[] = 'age = ?';
            $params[] = $characterAge;
        }
        if ($characterCountry !== '' && $characterCountry != null) {
            $fields[] = 'country = ?';
            $params[] = $characterCountry;
        }
        if ($characterHabitat !== '' && $characterHabitat != null) {
            $fields[] = 'habitat = ?';
            $params[] = $characterHabitat;
        }

        $params[] = $characterName;

        if (!empty($fields)) { // Check if there are fields to update
            // Update only the fields that have been filled in
            $sql = "UPDATE characters SET " . implode(', ', $fields) . " WHERE character_name = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Set success message
            $_SESSION['success'] = "Character updated successfully";
        } else {
            $_SESSION['error'] = "No fields to update";
        }

        header('Location: Character_add.php');
        exit;
    }

    // Check if the character already exists in the database, if not then add the character to the database
    if ($characterName == null || $characterName == "") {
        $_SESSION['error'] = "Please enter a name or select one";
        header('Location: Character_add.php'); // Redirect to the character add page
        exit;
    } else if ($character) {
        $_SESSION['error'] = "Character already exists";
        header('Location: Character_add.php'); // Redirect to the character add page
        exit;
    } else {
        // Insert the new character into the database
        $stmt = $pdo->prepare("INSERT INTO characters (character_name, correspondence, icon_character, age, country, habitat) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$characterName, $correspondence, $characterIcon, $characterAge, $characterCountry, $characterHabitat]);
        $_SESSION['success'] = "Character added successfully";
        header('Location: Character_add.php');
        exit;
    }
}

require_once "./blueprints/gl_ap_start.php";
?>

<div id="mainText"> <!-- Right div -->
    <button id="Return" onclick="window.history.back()">Return</button><br>

    <h2> Add a Character </h2><br>
    <form method="POST" action="Character_add.php" enctype="multipart/form-data" onsubmit="return confirmSubmit()"> <!-- enctype is used to upload files -->
        <label>Character Name</label>
        <input type="text" name="character_name_Input"> <!-- To name a new character being created -->
        <select name="character_name"> <!-- Dropdown selection to choose a character to modify -->
            <option value="">Select a character</option>
            <?php 
                // Retrieve the names of the characters from the database and display them in a dropdown list
                $stmt = $pdo->prepare("SELECT * FROM characters ORDER BY character_name;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Check if the current character matches selectedCharacter or the character_id in the URL
                    $selected = '';
                    if (isset($_GET['character_id']) && $_GET['character_id'] == $row['id_character']) {
                        $selected = 'selected';
                    } elseif ($row['character_name'] === $selectedCharacter) {
                        $selected = 'selected';
                    }
                    echo '<option value="' . sanitize_output($row['character_name']) . '" ' . $selected . '>' . sanitize_output($row['character_name']) . '</option>';
                }
            ?>
        </select><br>
        <label for="correspondence">Correspondence</label>
        <select name="correspondence" id="correspondence" required> <!-- Dropdown list to select the character's correspondence with which race -->
            <option value="">Select a race</option>
            <?php 
                // Retrieve the race corresponding to the character_id from the database
                $SelectedRace = null;
                if (isset($_GET['character_id'])) { // If there is character_id in the URL, fetch the corresponding race
                    $stmt = $pdo->prepare("SELECT race_name FROM characters 
                                        JOIN races ON characters.correspondence = races.id_race
                                        WHERE id_character = ?");
                    $stmt->execute([$_GET['character_id']]);
                    $SelectedRace = $stmt->fetchColumn();
                }


                // Retrieve the names of the races from the database and display them in a dropdown list
                $stmt = $pdo->prepare("SELECT * FROM races ORDER BY race_name;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($row['race_name'] === $SelectedRace) ? 'selected' : '';
                    echo '<option value="' . sanitize_output($row['race_name']) . '" ' . $selected . '>' . sanitize_output($row['race_name']) . '</option>';
                }
            ?>
        </select><br>
        <label>Character Icon (Max 5Mo)</label>
        <input type="file" name="icon_character"><br>
        <label>Age</label>
        <input type="text" name="age"><br>
        <label>Country</label>
        <input type="text" name="country"><br>
        <label>Habitat</label>
        <input type="text" name="habitat"><br><br>
        <button type="submit">Submit</button>
        <button type="button" onclick="fetchCharacterInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmCharacterDelete()">Delete Character</button>
    </form><br>
    <div id="characterInfo"></div>

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

<?php
require_once "./blueprints/gl_ap_end.php";
?>
