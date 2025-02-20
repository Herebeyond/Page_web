<?php
require 'blueprints/page_init.php'; // inclut le fichier page_init.php


if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $Specie_name = isset($_POST['Specie_name']) ? trim($_POST['Specie_name']) : ''; // Nettoyage des entrées utilisateur
    $Specie_Icon = isset($_POST['icon_Specie']) ? trim($_POST['icon_Specie']) : '';
    $Specie_content = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : '';

    // Préparer la requête SQL dynamique
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
    $params[] = $Specie_name;

    if (!empty($fields)) {
        $sql = "UPDATE species SET " . implode(', ', $fields) . " WHERE nom_Specie = ?";
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
?>

<html>
    <head>
        <link rel="stylesheet" href="../style/PageStyle.css?ver=<?php echo time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
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
                <div id='add_specie' class="textePrincipal"> <!-- Div de droite -->
                    <?php require 'scriptes/functions.php'; // inclut le fichier functions.php ?>
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
                        <label for="Specie_text">Specie content :</label><br>
                        <textarea type="text" name="Specie_text" id="content_input" value="<?php echo htmlspecialchars($selectedSpecie['content_specie'] ?? ''); ?>"></textarea><br><br>
                        <button type="submit">Submit</button> 
                        <button type="button" onclick="fetchSpecieInfo()">Fetch Info</button><br><br>
                        <button type="button" onclick="confirmSpecieDelete()">Delete Specie</button>
                    </form><br>
                    <!-- Afficher le text et l'icon de la Specie sélectionnée -->
                    <div id="specieInfo"></div>
                </div>
            </div>
        </div>
    </body>
</html>