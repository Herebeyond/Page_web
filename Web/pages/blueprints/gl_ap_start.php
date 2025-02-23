<?php
require "./scriptes/functions.php"; // inclut le fichier de fonctions
?>


<html>
    <head>
        <link rel="stylesheet" href= "<?php echo "../style/PageStyle.css?ver=" . time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
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
                                echo "<div><img src=../images/Icon.png></div>";
                            }?> <!-- permet de créer 4 images identiques comme décoration du texte de gauche-->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(sanitize_output(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>