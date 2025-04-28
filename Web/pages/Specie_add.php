<?php
require_once "./blueprints/page_init.php"; // Includes the page initialization file

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    if (isset($_POST['SpecieNameInput'])) {
        $SpecieName = isset($_POST['SpecieNameInput']) ? trim($_POST['SpecieNameInput']) : null; // Trim whitespace from user input
    } else {
        $SpecieName = isset($_POST['SpecieName']) ? trim($_POST['SpecieName']) : null;
    }

    $SpecieContent = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : null;

    // Handle file upload for the specie icon
    $SpecieIcon = null;
    if (isset($_FILES['icon_Specie']) && $_FILES['icon_Specie']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../images/'; // Define the target directory (relative to the current file)
        $uniqueName = uniqid() . '_' . basename($_FILES['icon_Specie']['name']); // Generate a unique name
        $uploadFile = $uploadDir . $uniqueName; // Define the target file path

        // Ensure the images directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['icon_Specie']['tmp_name'], $uploadFile)) { // Check if the file was moved successfully
            $SpecieIcon = $uniqueName; // Save the unique name to the database
        } else {
            $_SESSION['error'] = "Failed to upload the specie icon.";
            header('Location: Specie_add.php');
            exit;
        }
    }

    // Retrieve the specie name from the database
    $stmt = $pdo->prepare("SELECT * FROM species WHERE specie_name = ?");
    $stmt->execute([$SpecieName]);
    $Specie = $stmt->fetch();

    // To update the specie
    if (isset($_POST['SpecieName']) && ($_POST['SpecieName'] != "" || $_POST['SpecieName'] != null)) {
        $fields = [];
        $params = [];
        $SpecieName = $_POST['SpecieName'];

        if ($SpecieIcon !== '' && $SpecieIcon != null) {
            $fields[] = 'icon_Specie = ?';
            $params[] = $SpecieIcon;
        }
        if ($SpecieContent !== '' && $SpecieContent != null) {
            $fields[] = 'content_Specie = ?';
            $params[] = $SpecieContent;
        }

        $params[] = $SpecieName;

        if (!empty($fields)) { // Check if there are fields to update
            $sql = "UPDATE species SET " . implode(', ', $fields) . " WHERE specie_name = ?";
            $stmt = $pdo->prepare($sql);
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
        $stmt->execute([$SpecieName, $SpecieIcon, $SpecieContent]);
        $_SESSION['success'] = "Specie added successfully";
        header('Location: Specie_add.php');
        exit;
    }
}

require_once "./blueprints/gl_ap_start.php";
?>

<div id="mainText"> <!-- Right div -->
    <button id="Return" onclick="window.history.back()">Return</button><br>
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
    <form method="POST" action="Specie_add.php" enctype="multipart/form-data" onsubmit="return confirmSubmit()">
        <label for="SpecieName">Specie Name</label>
        <input type="text" name="SpecieNameInput"> <!-- To name a new specie being created -->
        <select name="SpecieName"> <!-- To select an existing specie to modify -->
            <option value="">Select a specie</option>
            <?php 
                // Retrieve the names of the species from the database and display them in a dropdown list
                $stmt = $pdo->prepare("SELECT * FROM species ORDER BY specie_name;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Check if the current specie matches the specie_id in the URL
                    $selected = '';
                    if (isset($_GET['specie_id']) && $_GET['specie_id'] == $row['id_specie']) {
                        $selected = 'selected';
                    }
                    echo '<option value="' . sanitize_output($row['specie_name']) . '" ' . $selected . '>' . sanitize_output($row['specie_name']) . '</option>';
                }
            ?>
        </select><br>
        <label for="Specie_icon">Specie Icon (Max 5Mo)</label>
        <input type="file" name="icon_Specie"><br>
        <label for="Specie_text">Specie content</label><br>
        <input type="text" name="Specie_text" id="content_input"><br><br>
        <button type="submit">Submit</button>
        <button type="button" onclick="fetchSpecieInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmSpecieDelete()">Delete Specie</button>
    </form><br>
    <div id="specieInfo"></div>
</div>

<?php require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file ?>
