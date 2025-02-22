<?php
require "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page


if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $RaceName = isset($_POST['Race_name']) ? trim($_POST['Race_name']) : ''; // Enlève les espaces en début et fin de chaîne
    $Correspondance = isset($_POST['Correspondance']) ? trim($_POST['Correspondance']) : '';
    $RaceIcon = isset($_POST['icon_Race']) ? trim($_POST['icon_Race']) : '';
    $IconTypeRace = isset($_POST['icon_Type_Race']) ? trim($_POST['icon_Type_Race']) : '';
    $RaceContent = isset($_POST['Race_text']) ? trim($_POST['Race_text']) : '';
    $Lifespan = isset($_POST['Lifespan']) ? trim($_POST['Lifespan']) : '';
    $Homeworld = isset($_POST['Homeworld']) ? trim($_POST['Homeworld']) : '';
    $Country = isset($_POST['Country']) ? trim($_POST['Country']) : '';
    $Habitat = isset($_POST['Habitat']) ? trim($_POST['Habitat']) : '';

    // Récupérer le nom de la Race depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM races WHERE nom_Race = ?"); 
    $stmt->execute([$RaceName]);
    $Race = $stmt->fetch();

    // Vérifier si la Race existe déjà dans la base, si non alors intégrer la Race à la base de données
    if ($Race) {
        $_SESSION['error'] = "Race already exists";
        header('Location: Race_add.php'); // Rediriger vers la page d'ajout de 
        exit;
    } else {
        // Insérer la nouvelle Race dans la base de données
        $stmt = $pdo->prepare("INSERT INTO races (nom_Race, correspondance, icon_Race, icon_Type_Race, content_Race, lifespan, homeworld, country, habitat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$RaceName, $Correspondance, $RaceIcon, $IconTypeRace, $RaceContent, $Lifespan, $Homeworld, $Country, $Habitat]);
        $_SESSION['success'] = "Race added successfully";
        header('Location: Race_add.php');
        exit;
    }
}

require "./blueprints/gl_ap_start.php";
?>


<div id="textePrincipal"> <!-- Div de droite -->
    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>


    <h2> Add a Race </h2><br>
    <form method="POST" action="Race_add.php">
        <label for="Race_name">Race Name</label>
        <input type="text" name="Race_name" required><br>
        <label for="Correspondance">Correspondance</label>
        <select name="Correspondance" required> <!-- Liste déroulante pour sélectionner la correspondance de la race avec quel specie -->
            <option value="">Select a specie</option>
            <?php // Récupérer les noms des Specie depuis la base de données et les afficher dans une liste déroulante
                $stmt = $pdo->query("SELECT * FROM species ORDER BY nom_specie;");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $row['nom_specie'] . '">' . $row['nom_specie'] . '</option>';
                }
            ?>
        </select><br>
        <label for="Race_icon">Race Icon</label>
        <input type="text" name="icon_Race"><br>
        <label for="Icon_Type_Race">Icon Type Race</label>
        <input type="text" name="icon_Type_Race"><br>
        <label for="Lifespan">Lifespan</label>
        <input type="text" name="Lifespan"><br>
        <label for="Homeworld">Homeworld</label>
        <input type="text" name="Homeworld"><br>
        <label for="Country">Country</label>
        <input type="text" name="Country"><br>
        <label for="Habitat">Habitat</label>
        <input type="text" name="Habitat"><br>
        <label for="Race_text">Race content :</label><br>
        <textarea type="text" name="Race_text" id="content_input"></textarea><br><br>
        <button type="submit">Submit</button>
    </form><br>


    <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color:green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
            unset($_SESSION['success']);
        }
    ?>
</div>

<?php
require "./blueprints/gl_ap_end.php";
?>