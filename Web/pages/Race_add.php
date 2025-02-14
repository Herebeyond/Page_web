<?php
include "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page
require '../login/db.php'; // Connexion à la base
include './scriptes/autorisation.php'; // inclut le fichier autorisation.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $Race_name = isset($_POST['Nom']) ? trim($_POST['Nom']) : ''; // Nettoyage des entrées utilisateur
    $Race_Icon = isset($_POST['icon_race']) ? trim($_POST['icon_race']) : '';
    $Race_content = isset($_POST['Race_text']) ? trim($_POST['Race_text']) : '';


    // Récupérer le nom de la race depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM races WHERE nom_race = ?"); 
    $stmt->execute([$Race_name]);
    $race = $stmt->fetch();

    // Vérifier si la race existe déjà dans la base, si non alors intégrer la race à la base de données
    if ($race) {
        $_SESSION['error'] = "Race already exists";
        header('Location: race_add.php');
        exit;
    } else {
        // Insérer la nouvelle race dans la base de données
        $stmt = $pdo->prepare("INSERT INTO races (nom_race, icon_race, content_race) VALUES (?, ?, ?)");
        $stmt->execute([$Race_name, $Race_Icon, $Race_content]);
        $_SESSION['success'] = "Race added successfully";
        header('Location: race_add.php');
        exit;
    }
}
?>



<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?>
        <link rel="stylesheet" href= "<?php echo $chemin_absolu . "style/PageStyle.css?ver=" . time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- <link rel="stylesheet" href="../style/styleScript.css?ver=<?php // echo time(); ?>"> le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <?php include "./scriptes/pages_generator.php" ?>
        <?php //include "./scriptes/pages_factions_generator.php" ?>
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

                    <h2> Ajouter une Race </h2><br>
                    <form method="POST" action="Race_add.php">
                        <label for="Race_name">Race Name</label>
                        <input type="text" name="Nom" required><br>
                        <label for="Race_icon">Race Icon</label>
                        <input type="text" name="icon_race"><br>
                        <label for="Race_text">Race content</label>
                        <input type="text" name="Race_text"><br>
                        <button type="submit">Submit</button>
                    </form><br>



                </div>

            </div>

        </div>

    </body>
</html>