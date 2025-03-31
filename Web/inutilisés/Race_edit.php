<?php
require './blueprints/page_init.php'; // includes the page initialization file

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    $RaceName = isset($_POST['Race_name']) ? trim($_POST['Race_name']) : null; // Trim whitespace from user input
    $correspondence = isset($_POST['correspondence']) ? trim($_POST['correspondence']) : null;
    $RaceIcon = isset($_POST['icon_Race']) ? trim($_POST['icon_Race']) : null;
    $RaceContent = isset($_POST['Race_text']) ? trim($_POST['Race_text']) : null;
    $Lifespan = isset($_POST['Lifespan']) ? trim($_POST['Lifespan']) : null;
    $Homeworld = isset($_POST['Homeworld']) ? trim($_POST['Homeworld']) : null;
    $Country = isset($_POST['Country']) ? trim($_POST['Country']) : null;
    $Habitat = isset($_POST['Habitat']) ? trim($_POST['Habitat']) : null;
    $Unique = isset($_POST['Unique']) ? trim($_POST['Unique']) : 0;

    // Prepare the dynamic SQL query
    $fields = [];
    $params = [];

    if ($correspondence !== '' && $RaceIcon != null) {
        $fields[] = 'correspondence = ?';
        $params[] = $correspondence;
    }
    if ($RaceIcon !== '' && $RaceIcon != null) {
        $fields[] = 'icon_Race = ?';
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
    if ($Unique !== '' && $Unique != null) {
        $fields[] = 'race_is_unique = ?';
        $params[] = $Unique;
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

    header('Location: Race_edit.php');
    exit;
}

require "./blueprints/gl_ap_start.php";
?>

<div id="mainText"> <!-- Right div -->
    <a id=Return onclick='window.history.back()'> Return </a><br>
    <?php // Display error or success messages after form submission
        if (isset($_SESSION['error'])) {
            echo '<p style="color:red;">' . sanitize_output($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color:green;">' . sanitize_output($_SESSION['success']) . '</p>';
            unset($_SESSION['success']);
        }
    ?>
    <h2> Edit a Race </h2><br>
    <form method="POST" action="Race_edit.php" onsubmit="return confirmSubmit()">
        <label for="Race_name">Race Name</label>
        <select name="Race_name" required>
            <option value="">Select a race</option>
            <?php // Retrieve the names of the races from the database and display them in a dropdown list
                $stmt = $pdo->prepare("SELECT * FROM races ORDER BY race_name;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . sanitize_output($row['race_name']) . '">' . sanitize_output($row['race_name']) . '</option>';
                }
            ?>
        </select><br>
        <label for="correspondence">correspondence</label>
        <select name="correspondence">
            <option value="">Select a specie</option>
            <?php // Retrieve the names of the species from the database and display them in a dropdown list
                $stmt = $pdo->prepare("SELECT * FROM species ORDER BY specie_name;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . sanitize_output($row['specie_name']) . '">' . sanitize_output($row['specie_name']) . '</option>';
                }
            ?>
        </select><br>
        <label for="Race_icon">Race Icon</label>
        <input type="file" name="icon_Race"><br>
        <label for="Lifespan">Lifespan</label>
        <input type="text" name="Lifespan"><br>
        <label for="Homeworld">Homeworld</label>
        <input type="text" name="Homeworld"><br>
        <label for="Country">Country</label>
        <input type="text" name="Country"><br>
        <label for="Habitat">Habitat</label>
        <input type="text" name="Habitat"><br>
        <input type="radio" name="Unique" value="1">Unique<br>
        <input type="radio" name="Unique" value="0">Multiple<br>
        <label for="Race_text">Race content</label><br>
        <textarea type="text" name="Race_text" id="content_input"></textarea><br><br>
        <button type="submit">Submit</button>
        <button type="button" onclick="fetchRaceInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmRaceDelete()">Delete Race</button>
    </form><br>
    <!-- Display the text and icon of the selected race -->
    <div id="raceInfo"></div>
</div>

<?php
require "./blueprints/gl_ap_end.php";
?>