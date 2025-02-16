<?php
require 'blueprints/page_init.php'; // inclut le fichier page_init.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $Specie_name = isset($_POST['Specie_name']) ? trim($_POST['Specie_name']) : ''; // Nettoyage des entrées utilisateur
    $Specie_Icon = isset($_POST['icon_Specie']) ? trim($_POST['icon_Specie']) : '';
    $Specie_content = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : '';

    // Modifie la Specie dans la base de données
    $stmt = $pdo->prepare("UPDATE species SET icon_Specie = ?, content_Specie = ? WHERE nom_Specie = ?");
    $stmt->execute([$Specie_Icon, $Specie_content, $Specie_name]);

    // Set success message
    $_SESSION['success'] = "Specie updated successfully";
    header('Location: Specie_edit.php');
    exit;
}
?>

<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?>
        <link rel="stylesheet" href= "<?php echo $chemin_absolu . "style/PageStyle.css?ver=" . time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- <link rel="stylesheet" href="../style/styleScript.css?ver=<?php // echo time(); ?>"> le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <?php // include "./scriptes/pages_generator.php" pour le moment je ne souhaite pas l'utiliser?>
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
                <div id='add_specie' class="textePrincipal"> <!-- Div de droite -->
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
                    <h2> Modifie a Specie </h2><br>
                    <form method="POST" action="Specie_edit.php" onsubmit="return confirmSubmit()">
                        <label for="Specie_name">Specie Name</label>
                        <select name="Specie_name" required>
                            <option value="">Select a specie</option>
                            <?php // Récupérer les noms des Specie depuis la base de données et les afficher dans une liste déroulante
                                $stmt = $pdo->query("SELECT * FROM species ORDER BY nom_Specie;");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    //$selected = ($row['nom_specie'] == $Specie_name) ? 'selected' : ''; // servait lorsque j'essaiyais de récupérer les données de la Specie sélectionnée automatiquement je crois
                                    echo '<option value="' . $row['nom_specie'] . '">' . $row['nom_specie'] . '</option>';
                                }
                            ?>
                        </select><br>
                        <label for="Specie_icon">Specie Icon</label>
                        <input type="text" name="icon_Specie" value="<?php echo htmlspecialchars($selectedSpecie['icon_specie'] ?? ''); ?>"><br>
                        <label for="Specie_text">Specie content</label>
                        <input type="text" name="Specie_text" value="<?php echo htmlspecialchars($selectedSpecie['content_specie'] ?? ''); ?>"><br><br>
                        <button type="submit">Submit</button> <button type="button" onclick="fetchSpecieInfo()">Fetch Info</button><br>
                    </form><br>
                    <!-- Afficher le text et l'icon de la Specie sélectionnée -->
                    <div id="specieInfo"></div>
                </div>
            </div>
        </div>
        <script>
            function fetchSpecieInfo() { // Fonction pour récupérer et afficher les informations de la Specie sélectionnée dans l'option du select dans la form
                var specieName = document.querySelector('select[name="Specie_name"]').value;
                if (specieName) {
                    fetch('scriptes/fetch_specie_info.php?specie=' + encodeURIComponent(specieName))
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                let iconHtml = data.icon ? `<p>Icon link: ${data.icon}</p><p>Icon: <img id="imgEdit" src="../images/${data.icon}" alt="Specie Icon"></p>` : '<p>Icon does not exist</p>';
                                let contentHtml = data.content ? `<p>Content: ${data.content}</p>` : '<p>Content does not exist</p>';
                                document.getElementById('specieInfo').innerHTML = iconHtml + contentHtml;
                            } else {
                                document.getElementById('specieInfo').innerHTML = '<p style="color:red;">' + data.message + '</p>';
                            }
                        })
                        .catch(error => {
                            document.getElementById('specieInfo').innerHTML = '<p style="color:red;">Error fetching specie info</p>';
                        });
                } else {
                    document.getElementById('specieInfo').innerHTML = '<p style="color:red;">Please select a specie</p>';
                }
            }

            function confirmSubmit() { // Fonction pour confirmer ou annuler la soumission du formulaire
                return confirm("Are you sure you want to update the specie?");
            }
        </script>
    </body>
</html>