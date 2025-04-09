<?php
require "./blueprints/page_init.php"; // includes the page initialization file

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    if (isset($_POST['SpecieNameInput'])) {
        $SpecieName = isset($_POST['SpecieNameInput']) ? trim($_POST['SpecieNameInput']) : null; // Trim whitespace from user input
    } else {
        $SpecieName = isset($_POST['SpecieName']) ? trim($_POST['SpecieName']) : null;
    }
    $Specie_Icon = isset($_POST['icon_Specie']) ? trim($_POST['icon_Specie']) : null;
    $Specie_content = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : null;
    $Unique = isset($_POST['Unique']) ? trim($_POST['Unique']) : 0; // Default to 0 if not set, making it Multiple by default

    // Retrieve the specie name from the database
    $stmt = $pdo->prepare("SELECT * FROM species WHERE specie_name = ?"); 
    $stmt->execute([$SpecieName]);
    $Specie = $stmt->fetch();

    // To update the specie
    if (isset($_POST['SpecieName']) && ($_POST['SpecieName'] != "" || $_POST['SpecieName'] != null)) {
        $fields = [];
        $params = [];
        $SpecieName = $_POST['SpecieName'];

        if ($Specie_Icon !== '' && $Specie_Icon != null) {
            $fields[] = 'icon_Specie = ?';
            $params[] = $Specie_Icon;
        }
        if ($Specie_content !== '' && $Specie_content != null) {
            $fields[] = 'content_Specie = ?';
            $params[] = $Specie_content;
        }


        if (count($fields) > 0) {
            $fields = implode(', ', $fields);
            $params[] = $SpecieName;
            $stmt = $pdo->prepare("UPDATE species SET $fields WHERE specie_name = ?");
            $stmt->execute($params);
            $_SESSION['success'] = "Specie updated successfully";
        } else {
            $_SESSION['error'] = "No fields to update";
        }
        header('Location: Specie_add.php');
        exit;
    }

    // Check if the specie already exists in the database, if not then add the specie to the database
    if ($Specie) {
        $_SESSION['error'] = "Specie already exists";
        header('Location: Specie_add.php');
        exit;
    } else {
        // Insert the new specie into the database
        $stmt = $pdo->prepare("INSERT INTO species (specie_name, icon_Specie, content_Specie) VALUES (?, ?, ?)");
        $stmt->execute([$SpecieName, $Specie_Icon, $Specie_content]);
        $_SESSION['success'] = "Specie added successfully";
        header('Location: Specie_add.php');
        exit;
    }
}

require "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>

<div id="mainText"> <!-- Right div -->
    <a id=Return onclick='window.history.back()'> Return </a><br>
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

    <h2> Add a Specie </h2><br>
    <form method="POST" action="Specie_add.php">
        <label for="SpecieName">Specie Name</label>
        <input type="text" name="SpecieNameInput"> <!-- To name a new specie being created -->
        <select name="SpecieName"> <!-- To select an existing specie to modify -->
            <option value="">Select a specie</option>
            <?php // Retrieve the names of the species from the database and display them in a dropdown list
                $stmt = $pdo->prepare("SELECT * FROM species ORDER BY specie_name;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . sanitize_output($row['specie_name']) . '">' . sanitize_output($row['specie_name']) . '</option>';
                }
            ?>
        </select><br>
        <label for="Specie_icon">Specie Icon</label>
        <input type="file" name="icon_Specie"><br>

        <label for="Specie_text">Specie content</label><br>
        <input type="text" name="Specie_text" id="content_input"><br><br>
        <button type="submit">Submit</button>
        <button type="button" onclick="fetchSpecieInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmSpecieDelete()">Delete Specie</button>
    </form><br>
    <div id="specieInfo"></div>
</div>

<?php require "./blueprints/gl_ap_end.php"; // includes the end of the general page file ?>