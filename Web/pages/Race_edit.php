<?php
require './blueprints/page_init.php'; // inclut le fichier page_init.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $RaceName = isset($_POST['Race_name']) ? trim($_POST['Race_name']) : ''; // Nettoyage des entrées utilisateur
    $Correspondance = isset($_POST['Correspondance']) ? trim($_POST['Correspondance']) : '';
    $RaceIcon = isset($_POST['icon_Race']) ? trim($_POST['icon_Race']) : '';
    $IconTypeRace = isset($_POST['icon_Type_Race']) ? trim($_POST['icon_Type_Race']) : '';
    $RaceContent = isset($_POST['Race_text']) ? trim($_POST['Race_text']) : '';
    $Lifespan = isset($_POST['Lifespan']) ? trim($_POST['Lifespan']) : '';
    $Homeworld = isset($_POST['Homeworld']) ? trim($_POST['Homeworld']) : '';
    $Country = isset($_POST['Country']) ? trim($_POST['Country']) : '';
    $Habitat = isset($_POST['Habitat']) ? trim($_POST['Habitat']) : '';

    // Préparer la requête SQL dynamique
    $fields = [];
    $params = [];

    if ($Correspondance !== '' && $RaceIcon != null) {
        $fields[] = 'correspondance = ?';
        $params[] = $Correspondance;
    }
    if ($RaceIcon !== '' && $RaceIcon != null) {
        $fields[] = 'icon_Race = ?';
        $params[] = $RaceIcon;
    }
    if ($IconTypeRace !== '' && $IconTypeRace != null) {
        $fields[] = 'icon_Type_Race = ?';
        $params[] = $IconTypeRace;
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

    if (!empty($fields)) {
        $sql = "UPDATE races SET " . implode(', ', $fields) . " WHERE nom_Race = ?";
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

<div id="textePrincipal"> <!-- Div de droite -->
    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
    <?php // Afficher les messages d'erreur ou de succès après la soumission du formulaire
        if (isset($_SESSION['error'])) {
            echo '<p style="color:red;">' . sanitize_output($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color:green;">' . sanitize_output($_SESSION['success']) . '</p>';
            unset($_SESSION['success']);
        }
    ?>
    <h2> Modifie a Race </h2><br>
    <form method="POST" action="Race_edit.php" onsubmit="return confirmSubmit()">
        <label for="Race_name">Race Name</label>
        <select name="Race_name" required>
            <option value="">Select a race</option>
            <?php // Récupérer les noms des Race depuis la base de données et les afficher dans une liste déroulante
                $stmt = $pdo->prepare("SELECT * FROM races ORDER BY nom_Race;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . sanitize_output($row['nom_race']) . '">' . sanitize_output($row['nom_race']) . '</option>';
                }
            ?>
        </select><br>
        <label for="Correspondance">Correspondance</label>
        <select name="Correspondance" required>
            <option value="">Select a specie</option>
            <?php // Récupérer les noms des Specie depuis la base de données et les afficher dans une liste déroulante
                $stmt = $pdo->prepare("SELECT * FROM species ORDER BY nom_specie;");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . sanitize_output($row['nom_specie']) . '">' . sanitize_output($row['nom_specie']) . '</option>';
                }
            ?>
        </select><br>
        <label for="Race_icon">Race Icon</label>
        <input type="text" name="icon_Race"><br>
        <label for="Icon_Type_Race">Race Icon Type</label>
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
        <button type="button" onclick="fetchRaceInfo()">Fetch Info</button><br><br>
        <button type="button" onclick="confirmRaceDelete()">Delete Specie</button>
    </form><br>
    <!-- Afficher le text et l'icon de la Race sélectionnée -->
    <div id="raceInfo"></div>
</div>


<?php
require "./blueprints/gl_ap_end.php";
?>