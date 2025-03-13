<?php
require "./blueprints/page_init.php"; // includes the page initialization file

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

    // Retrieve the race name from the database
    $stmt = $pdo->prepare("SELECT * FROM races WHERE race_name = ?"); 
    $stmt->execute([$RaceName]);
    $Race = $stmt->fetch();

    // Check if the race already exists in the database, if not then add the race to the database
    if ($Race) {
        $_SESSION['error'] = "Race already exists";
        header('Location: Race_add.php'); // Redirect to the race add page
        exit;
    } else {
        // Insert the new race into the database
        $stmt = $pdo->prepare("INSERT INTO races (race_name, correspondence, icon_Race, content_Race, lifespan, homeworld, country, habitat, race_is_unique) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$RaceName, $correspondence, $RaceIcon, $RaceContent, $Lifespan, $Homeworld, $Country, $Habitat, $Unique]);
        $_SESSION['success'] = "Race added successfully";
        header('Location: Race_add.php');
        exit;
    }
}

require "./blueprints/gl_ap_start.php";
?>

<div id="mainText"> <!-- Right div -->
    <a id=Return onclick='window.history.back()'> Return </a><br>

    <h2> Add a Race </h2><br>
    <form method="POST" action="Race_add.php">
        <label for="Race_name">Race Name</label>
        <input type="text" name="Race_name" required placeholder="Entrez votre nom"><br>
        <label for="correspondence">correspondence</label>
        <select name="correspondence" required> <!-- Dropdown list to select the race's correspondence with which specie -->
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
        <input type="checkbox" name="Unique" value="1">Unique<br>
        <label for="Race_text">Race content</label><br>
        <textarea type="text" name="Race_text" id="content_input"></textarea><br><br>
        <button type="submit">Submit</button>
    </form><br>

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
require "./blueprints/gl_ap_end.php";
?>