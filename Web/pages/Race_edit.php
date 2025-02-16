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

    // Modifie la Race dans la base de données
    $stmt = $pdo->prepare("UPDATE races SET correspondance = ?, icon_Race = ?, icon_Type_Race = ?, content_Race = ?, lifespan = ?, homeworld = ?, country = ?, habitat = ? WHERE nom_Race = ?");
    $stmt->execute([$Correspondance, $Race_Icon, $Icon_Type_Race, $Race_content, $Lifespan, $Homeworld, $Country, $Habitat, $Race_name]);

    // Set success message
    $_SESSION['success'] = "Race updated successfully";
    header('Location: Race_edit.php');
    exit;
}
?>

<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?>
        <link rel="stylesheet" href= "<?php echo $chemin_absolu . "style/PageStyle.css?ver=" . time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
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
                                echo "<div><img src=" . $chemin_absolu . "images/Icon.png></div>";
                            }?> <!-- permet de créer 3 images identiques -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>
                <div id='add_race' class="textePrincipal"> <!-- Div de droite -->
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
                        <label for="Race_text">Race content</label>
                        <input type="text" name="Race_text" value="<?php echo htmlspecialchars($selectedRace['content_race'] ?? ''); ?>"><br>
                        <label for="Lifespan">Lifespan</label>
                        <input type="text" name="Lifespan" value="<?php echo htmlspecialchars($selectedRace['lifespan'] ?? ''); ?>"><br>
                        <label for="Homeworld">Homeworld</label>
                        <input type="text" name="Homeworld" value="<?php echo htmlspecialchars($selectedRace['homeworld'] ?? ''); ?>"><br>
                        <label for="Country">Country</label>
                        <input type="text" name="Country" value="<?php echo htmlspecialchars($selectedRace['country'] ?? ''); ?>"><br>
                        <label for="Habitat">Habitat</label>
                        <input type="text" name="Habitat" value="<?php echo htmlspecialchars($selectedRace['habitat'] ?? ''); ?>"><br><br>
                        <button type="submit">Submit</button> <button type="button" onclick="fetchRaceInfo()">Fetch Info</button><br>
                    </form><br>
                    <!-- Afficher le text et l'icon de la Race sélectionnée -->
                    <div id="raceInfo"></div>
                </div>
            </div>
        </div>
<script>
    function fetchRaceInfo() { // Fonction pour récupérer et afficher les informations de la Race sélectionnée dans l'option du select dans la form
        var raceName = document.querySelector('select[name="Race_name"]').value;
        if (raceName) {
            fetch('scriptes/fetch_race_info.php?race=' + encodeURIComponent(raceName))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        let correspondanceHtml = data.correspondance ? `<p>Correspondance: ${data.correspondance}</p>` : '<p>Correspondance does not exist</p>';
                        let icon = data.icon ? data.icon.replace(/ /g, '_') : '';
                        let iconHtml = icon ? `<p>Icon link: ${icon}.${data.icon_type}</p><p>Icon: <img id="imgEdit" src="../images/${icon}.${data.icon_type}" alt="Race Icon"></p>` : '<p>Icon does not exist</p>';
                        let contentHtml = data.content ? `<p>Content: ${data.content}</p>` : '<p>Content does not exist</p>';
                        let lifespanHtml = data.lifespan ? `<p>Lifespan: ${data.lifespan}</p>` : '<p>Lifespan does not exist</p>';
                        let homeworldHtml = data.homeworld ? `<p>Homeworld: ${data.homeworld}</p>` : '<p>Homeworld does not exist</p>';
                        let countryHtml = data.country ? `<p>Country: ${data.country}</p>` : '<p>Country does not exist</p>';
                        let habitatHtml = data.habitat ? `<p>Habitat: ${data.habitat}</p>` : '<p>Habitat does not exist</p>';
                        document.getElementById('raceInfo').innerHTML = correspondanceHtml + iconHtml + contentHtml + lifespanHtml + homeworldHtml + countryHtml + habitatHtml; // permet l'affichage des infos
                    } else {
                        document.getElementById('raceInfo').innerHTML = '<p style="color:red;">' + data.message + '</p>';
                    }
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                    document.getElementById('raceInfo').innerHTML = '<p style="color:red;">Error fetching race info</p>';
                });
        } else {
            document.getElementById('raceInfo').innerHTML = '<p style="color:red;">Please select a race</p>';
        }
    }

    function confirmSubmit() { // Fonction pour confirmer ou annuler la soumission du formulaire
        return confirm("Are you sure you want to update the race?");
    }
</script>
    </body>
</html>
