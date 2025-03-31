<?php
require 'blueprints/page_init.php'; // includes the page initialization file

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    $Specie_name = isset($_POST['Specie_name']) ? trim($_POST['Specie_name']) : null; // Trim whitespace from user input
    $Specie_Icon = isset($_POST['icon_Specie']) ? trim($_POST['icon_Specie']) : null;
    $Specie_content = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : null;
    $Unique = isset($_POST['Unique']) ? trim($_POST['Unique']) : 0;

    // Prepare the dynamic SQL query
    $fields = [];
    $params = [];

    if ($Specie_Icon !== '' && $Specie_Icon != null) {
        $fields[] = 'icon_Specie = ?';
        $params[] = $Specie_Icon;
    }
    if ($Specie_content !== '' && $Specie_content != null) {
        $fields[] = 'content_Specie = ?';
        $params[] = $Specie_content;
    }
    if ($Unique !== '' && $Unique != null) {
        $fields[] = 'race_is_unique = ?';
        $params[] = $Unique;
    }
    $params[] = $Specie_name;

    if (!empty($fields)) {
        $sql = "UPDATE species SET " . implode(', ', $fields) . " WHERE specie_name = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Set success message
        $_SESSION['success'] = "Specie updated successfully";
    } else {
        $_SESSION['error'] = "No fields to update";
    }

    header('Location: Specie_edit.php');
    exit;
}
require 'blueprints/gl_ap_start.php'; // includes the start of the general page file
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
    <h2> Edit a Specie </h2><br>
    <form method="POST" action="Specie_edit.php" onsubmit="return confirmSubmit()">
        <label for="Specie_name">Specie Name</label>
        <select name="Specie_name" required>
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
        <input type="radio" name="Unique" value="1">Unique<br>
        <input type="radio" name="Unique" value="0">Multiple<br>
        <label for="Specie_text">Specie content</label><br>
        <textarea type="text" name="Specie_text"></textarea><br><br>
        <button type="submit">Submit</button> 
        <button type="button" onclick="fetchSpecieInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmSpecieDelete()">Delete Specie</button>
    </form><br>
    <!-- Display the text and icon of the selected specie -->
    <div id="specieInfo"></div>
</div>

<?php require 'blueprints/gl_ap_end.php'; // includes the end of the general page file ?>