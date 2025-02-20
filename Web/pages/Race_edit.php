<?php
require 'blueprints/page_init.php'; // inclut le fichier page_init.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $Race_name = isset($_POST['Race_name']) ? trim($_POST['Race_name']) : ''; // Nettoyage des entrées utilisateur
    $Correspondance = isset($_POST['Correspondance']) ? trim($_POST['Correspondance']) : '';
    $Race_Icon = isset($_POST['icon_Race']) ? trim($_POST['icon_Race']) : '';
    $Icon_Type_Race = isset($_POST['icon_Type_Race']) ? trim($_POST['icon_Type_Race']) : '';
    $Race_content = isset($_POST['Race_text']) ? trim($_POST['Race_text']) : '';
    $Lifespan = isset($_POST['Lifespan']) ? trim($_POST['Lifespan']) : '';
    $Homeworld = isset($_POST['Homeworld']) ? trim($_POST['Homeworld']) : '';
    $Country = isset($_POST['Country']) ? trim($_POST['Country']) : '';
    $Habitat = isset($_POST['Habitat']) ? trim($_POST['Habitat']) : '';

    // Préparer la requête SQL dynamique
    $fields = [];
    $params = [];

    if ($Correspondance !== '' && $Race_Icon != null) {
        $fields[] = 'correspondance = ?';
        $params[] = $Correspondance;
    }
    if ($Race_Icon !== '' && $Race_Icon != null) {
        $fields[] = 'icon_Race = ?';
        $params[] = $Race_Icon;
    }
    if ($Icon_Type_Race !== '' && $Icon_Type_Race != null) {
        $fields[] = 'icon_Type_Race = ?';
        $params[] = $Icon_Type_Race;
    }
    if ($Race_content !== '' && $Race_content != null) {
        $fields[] = 'content_Race = ?';
        $params[] = $Race_content;
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
    $params[] = $Race_name;

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
?>

<html>
    <head>
        <link rel="stylesheet" href="../style/PageStyle.css?ver=<?php echo time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <title>
            Page d'Accueil
        </title>
    </head>
    <body>
        <div id=global>
            <?php include "./blueprints/header.php" ?>
            <div id=englobe>
                <div class=texteGauche> <!-- Div de gauche -->
                    <div id=enTeteTexteGauche>
                        <?php
                            for($i=0; $i<4; $i++) {
                                echo "<div><img src='../images/Icon.png'></div>";
                            }?> <!-- permet de créer 3 images identiques -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>
                <div id='add_race' class="textePrincipal"> <!-- Div de droite -->
                    <?php include "scriptes/functions.php"?>
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <?php // Afficher les messages d'erreur ou de succès après la soumission du formulaire
                        if (isset($_SESSION['error'])) {
                            echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
                            unset($_SESSION['error']);
                        }
                        if (isset($_SESSION['success'])) {
                            echo '<p style="color:green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
                            unset($_SESSION['success']);
                        }
                    ?>
                    <h2> Modifie a Race </h2><br>
                    <form method="POST" action="Race_edit.php" onsubmit="return confirmSubmit()">
                        <label for="Race_name">Race Name</label>
                        <select name="Race_name" required>
                            <option value="">Select a race</option>
                            <?php // Récupérer les noms des Race depuis la base de données et les afficher dans une liste déroulante
                                $stmt = $pdo->query("SELECT * FROM races ORDER BY nom_Race;");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $row['nom_race'] . '">' . $row['nom_race'] . '</option>';
                                }
                            ?>
                        </select><br>
                        <label for="Correspondance">Correspondance</label>
                        <select name="Correspondance" required>
                            <option value="">Select a specie</option>
                            <?php // Récupérer les noms des Specie depuis la base de données et les afficher dans une liste déroulante
                                $stmt = $pdo->query("SELECT * FROM species ORDER BY nom_specie;");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $row['nom_specie'] . '">' . $row['nom_specie'] . '</option>';
                                }
                            ?>
                        </select><br>
                        <label for="Race_icon">Race Icon</label>
                        <input type="text" name="icon_Race" value="<?php echo htmlspecialchars($selectedRace['icon_race'] ?? ''); ?>"><br>
                        <label for="Icon_Type_Race">Race Icon Type</label>
                        <input type="text" name="icon_Type_Race" value="<?php echo htmlspecialchars($selectedRace['icon_type_race'] ?? ''); ?>"><br>
                        <label for="Lifespan">Lifespan</label>
                        <input type="text" name="Lifespan" value="<?php echo htmlspecialchars($selectedRace['lifespan'] ?? ''); ?>"><br>
                        <label for="Homeworld">Homeworld</label>
                        <input type="text" name="Homeworld" value="<?php echo htmlspecialchars($selectedRace['homeworld'] ?? ''); ?>"><br>
                        <label for="Country">Country</label>
                        <input type="text" name="Country" value="<?php echo htmlspecialchars($selectedRace['country'] ?? ''); ?>"><br>
                        <label for="Habitat">Habitat</label>
                        <input type="text" name="Habitat" value="<?php echo htmlspecialchars($selectedRace['habitat'] ?? ''); ?>"><br>
                        <label for="Race_text">Race content :</label><br>
                        <textarea type="text" name="Race_text" id="content_input"><?php echo htmlspecialchars($selectedRace['content_race'] ?? ''); ?></textarea><br><br>
                        <button type="submit">Submit</button> 
                        <button type="button" onclick="fetchRaceInfo()">Fetch Info</button><br><br>
                        <button type="button" onclick="confirmRaceDelete()">Delete Specie</button>
                    </form><br>
                    <!-- Afficher le text et l'icon de la Race sélectionnée -->
                    <div id="raceInfo"></div>
                </div>
            </div>
        </div>
    </body>
</html>
