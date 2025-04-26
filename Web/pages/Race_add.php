<?php
require_once "./blueprints/page_init.php"; // Includes the page initialization file

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    if (isset($_POST['Race_name_Input'])) {
        $RaceName = isset($_POST['Race_name_Input']) ? trim($_POST['Race_name_Input']) : null; // Trim whitespace from user input
    } else {
        $RaceName = isset($_POST['Race_name']) ? trim($_POST['Race_name']) : null;
    }
    $cor = $pdo->prepare("SELECT id_specie FROM species WHERE specie_name = ?");
    $cor->execute([$_POST['correspondence']]);
    $correspondence = $cor->fetchColumn(); // Get the id_specie of the selected correspondence
    if ($correspondence === false) {
        $_SESSION['error'] = "Correspondence error: Specie not found";
    }
    $RaceId = $_GET['race'] ?? null;
    if ($RaceId) {
        $selected = $pdo->prepare("SELECT race_name FROM races WHERE id_race = ?");
        $selected->execute([$RaceId]);
        $SelectedRace = $selected->fetchColumn();
    } else {
        $SelectedRace = null;
    }

    $RaceContent = isset($_POST['Race_text']) ? trim($_POST['Race_text']) : null;
    $Lifespan = isset($_POST['Lifespan']) ? trim($_POST['Lifespan']) : null;
    $Homeworld = isset($_POST['Homeworld']) ? trim($_POST['Homeworld']) : null;
    $Country = isset($_POST['Country']) ? trim($_POST['Country']) : null;
    $Habitat = isset($_POST['Habitat']) ? trim($_POST['Habitat']) : null;


    // Handle file upload for the race icon
    $RaceIcon = null;
    if (isset($_FILES['icon_race']) && $_FILES['icon_race']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../images/'; // Define the target directory (relative to the current file)
        $uniqueName = uniqid() . '_' . basename($_FILES['icon_race']['name']); // Generate a unique name
        $uploadFile = $uploadDir . $uniqueName; // Define the target file path

        // Ensure the images directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['icon_race']['tmp_name'], $uploadFile)) { // Check if the file was moved successfully
            $RaceIcon = $uniqueName; // Save the unique name to the database
        } else {
            $_SESSION['error'] = "Failed to upload the race icon.";
            header('Location: Race_add.php');
            exit;
        }
    }


    // Retrieve the race name from the database
    $stmt = $pdo->prepare("SELECT * FROM races WHERE race_name = ?");
    $stmt->execute([$RaceName]);
    $Race = $stmt->fetch();

    // To update the race
    if (isset($_POST['Race_name']) && ($_POST['Race_name'] != "" || $_POST['Race_name'] != null)) {
        $fields = [];
        $params = [];
        $RaceName = $_POST['Race_name'];

        if ($correspondence !== '' && $correspondence != null) {
            $fields[] = 'correspondence = ?';
            $params[] = $correspondence;
        }
        if ($RaceIcon !== '' && $RaceIcon != null) {
            $fields[] = 'icon_race = ?';
            $params[] = $RaceIcon;
        }
        if ($RaceContent !== '' && $RaceContent != null) {
            $fields[] = 'content_Race = ?';
            $params[] = $RaceContent;
        }
        if ($Lifespan !== '' && $Lifespan != null) {
            $fields[] = 'lifespan = ?';
            $params[] = $Lifespan;
        }
        if ($Homeworld !== '' && $Homeworld != null) {
            $fields[] = 'homeworld = ?';
            $params[] = $Homeworld;
        }
        if ($Country !== '' && $Country != null) {
            $fields[] = 'country = ?';
            $params[] = $Country;
        }
        if ($Habitat !== '' && $Habitat != null) {
            $fields[] = 'habitat = ?';
            $params[] = $Habitat;
        }

        $params[] = $RaceName;

        if (!empty($fields)) { // Check if there are fields to update
            // Update only the fields that have been filled in
            $sql = "UPDATE races SET " . implode(', ', $fields) . " WHERE race_name = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Set success message
            $_SESSION['success'] = "Race updated successfully";
        } else {
            $_SESSION['error'] = "No fields to update";
        }

        header('Location: Race_add.php');
        exit;
    }

    // Check if the race already exists in the database, if not then add the race to the database
    if ($RaceName == null || $RaceName == "") {
        $_SESSION['error'] = "Please enter a name or select one";
        header('Location: Race_add.php'); // Redirect to the race add page
        exit;
    } else if ($Race) {
        $_SESSION['error'] = "Race already exists";
        header('Location: Race_add.php'); // Redirect to the race add page
        exit;
    } else {
        // Insert the new race into the database
        $stmt = $pdo->prepare("INSERT INTO races (race_name, correspondence, icon_race, content_Race, lifespan, homeworld, country, habitat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$RaceName, $correspondence, $RaceIcon, $RaceContent, $Lifespan, $Homeworld, $Country, $Habitat]);
        $_SESSION['success'] = "Race added successfully";
        header('Location: Race_add.php');
        exit;
    }
}

require_once "./blueprints/gl_ap_start.php";
?>

<div id="mainText"> <!-- Right div -->
    <button id="Return" onclick="window.history.back()">Return</button><br>

    <h2> Add a Race </h2><br>
    <form method="POST" action="Race_add.php" enctype="multipart/form-data" onsubmit="return confirmSubmit()">
        <label>Race Name</label>
        <input type="text" name="Race_name_Input"> <!-- To name a new race being created -->
        <select name="Race_name"> <!-- Dropdown selection to choose a race to modify -->
            <option value="">Select a race</option>
            <?php 
                // Retrieve the names of the races from the database and display them in a dropdown list
                $stmt = $pdo->prepare("SELECT * FROM races ORDER BY race_name;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Check if the current race matches SelectedRace
                    $selected = ($row['race_name'] === $SelectedRace) ? 'selected' : '';
                    echo '<option value="' . sanitize_output($row['race_name']) . '" ' . $selected . '>' . sanitize_output($row['race_name']) . '</option>';
                }
            ?>
        </select><br>
        <label for="correspondence">correspondence</label>
        <select name="correspondence" id="correspondence" required > <!-- Dropdown list to select the race's correspondence with which specie -->
            <option value="">Select a specie</option>
            <?php // Retrieve the names of the species from the database and display them in a dropdown list
                $stmt = $pdo->prepare("SELECT * FROM species ORDER BY specie_name;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . sanitize_output($row['specie_name']) . '">' . sanitize_output($row['specie_name']) . '</option>';
                }
            ?>
        </select><br>
        <label>Race Icon</label>
        <input type="file" name="icon_race"><br>
        <label>Lifespan</label>
        <input type="text" name="Lifespan"><br>
        <label>Homeworld</label>
        <input type="text" name="Homeworld"><br>
        <label>Country</label>
        <input type="text" name="Country"><br>
        <label>Habitat</label>
        <input type="text" name="Habitat"><br>
        <label>Race content</label><br>
        <textarea type="text" name="Race_text" id="content_input"></textarea><br><br>
        <button type="submit">Submit</button>
        <button type="button" onclick="fetchRaceInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmRaceDelete()">Delete Race</button>
    </form><br>
    <div id="raceInfo"></div>

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