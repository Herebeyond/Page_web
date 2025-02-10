<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?>
        <link rel="stylesheet" href= "<?php echo $chemin_absolu . "style/PageStyleAccueil.css?ver=" . time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
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
                            }?> <!-- permet de créer 4 images identiques comme décoration du texte de gauche-->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>
                



                
                <div class="textePrincipal"> <!-- Div de droite -->
                    <div>
                        <?php
                        // Création d'un tableau avec des éléments à afficher dans la liste
                        $pages = ["Races", "Subraces"];

                        sort($pages); // tri le tableau par ordre alphabétique
                        $frstLetter = mb_substr($pages[0], 0, 1); // récupère la première lettre du premier élément du tableau
                        echo "<span>$frstLetter</span>"; // affiche la première lettre

                        // Début de la liste non ordonnée
                        echo "<ul>";

                        // Parcours du tableau et affichage des éléments dans la liste
                        
                        foreach ($pages as $page) {
                            

                            if(mb_substr($page, 0, 1) != $frstLetter) {
                                echo "</ul>";
                                $frstLetter = mb_substr($page, 0, 1);
                                echo "<span>$frstLetter</span>";
                                echo "<ul>";
                            }

                            echo "<li><a href=" . $chemin_absolu . "pages/" . $page . ".php>$page</a></li>";
                        }

                        // Fin de la liste
                        echo "</ul>";
                        ?>
                    </div>
                </div>
                

            </div>

        </div>

    </body>
</html>