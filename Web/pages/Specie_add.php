<?php
require "./blueprints/page_init.php"; // includes the page initialization file

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the form has been submitted
    $Specie_name = isset($_POST['Specie_name']) ? trim($_POST['Specie_name']) : ''; // Trim whitespace from user input
    $Specie_Icon = isset($_POST['icon_Specie']) ? trim($_POST['icon_Specie']) : '';
    $Specie_content = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : '';

    // Retrieve the specie name from the database
    $stmt = $pdo->prepare("SELECT * FROM species WHERE specie_name = ?"); 
    $stmt->execute([$Specie_name]);
    $Specie = $stmt->fetch();

    // Check if the specie already exists in the database, if not then add the specie to the database
    if ($Specie) {
        $_SESSION['error'] = "Specie already exists";
        header('Location: Specie_add.php');
        exit;
    } else {
        // Insert the new specie into the database
        $stmt = $pdo->prepare("INSERT INTO species (specie_name, icon_Specie, content_Specie) VALUES (?, ?, ?)");
        $stmt->execute([$Specie_name, $Specie_Icon, $Specie_content]);
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
        <label for="Specie_name">Specie Name</label>
        <input type="text" name="Specie_name" required><br>
        <label for="Specie_icon">Specie Icon</label>
        <input type="text" name="icon_Specie"><br>
        <label for="Specie_text">Specie content</label><br>
        <input type="text" name="Specie_text" id="content_input"><br><br>
        <button type="submit">Submit</button>
    </form><br>
</div>

<?php require "./blueprints/gl_ap_end.php"; // includes the end of the general page file ?>