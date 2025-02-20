<?php
include "./blueprints/page_init.php";
?>

<html>
    <head>
        <link rel="stylesheet" href="../style/PageStyle.css?ver=<?php echo time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <title>
            Dimension List
        </title>
    </head>
    
    <body>
        <div id='global'>

            <?php include "./blueprints/header.php"; ?>
            <?php include "./scriptes/functions.php"; ?>

            <div id='englobe'>
                <div class=texteGauche> <!-- Div de gauche -->
                    <div id=enTeteTexteGauche>
                        <?php
                            for($i=0; $i<4; $i++) {
                                echo "<div><img src='../images/Icon.png'></div>";
                            }?> <!-- permet de créer 4 images identiques et espacées correctement -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>

                <div id='affichage_specie'> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <span class='Titre'> Dimensions </span> <!-- affiche le nom de la specie en entête -->
                    <?php
                    try {
                        // génère le texte principal de la page
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/dimensions.txt"))) . '</span>';
                        echo '<br><br>';

                        $dimensionsInfos = $pdo->query("SELECT * FROM dimensions");
                        while ($row = $dimensionsInfos->fetch(PDO::FETCH_ASSOC)) {
                            // génère les divs éventuelles pour chaque races
                            $divsSelec = '';
                                $Dimension_name = $row['nom_dimension'];
                                $Dimension_type = $row['type'];
                                if ($Dimension_type == null || $Dimension_type == '') {
                                    $Dimension_type = 'Not specified';
                                }

                                $Reality = $row['reality'];
                                if ($Reality == null || $Reality == '') {
                                    $Reality = 'Not specified';
                                }

                                $God_home = $row['god_home'];
                                if ($God_home == null || $God_home == '') {
                                    $God_home = 'Not specified';
                                }

                                $Content = $row['content'];
                                if ($Content == null || $Content == '') {
                                    $Content = 'Not specified';
                                }



                                // Création d'une div pour chaque race
                                $divsSelec .= '
                                        <span class=nomDimension> ' . $row["nom_dimension"] . '</span>
                                        <div class="selection">
                                            <div class=infobox>
                                                <div class=infos>
                                                    <div>
                                                        <p class=infosP> Dimension type : </p>
                                                        <p class=infosT>' . $Dimension_type . '</p>
                                                    </div>
                                                    <div>
                                                        <p class=infosP> Which reality : </p>
                                                        <p class=infosT>' . $Reality . '</p>
                                                    </div>
                                                    <div>
                                                        <p class=infosP> Which Gods live here : </p>
                                                        <p class=infosT>' . $God_home . '</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="texteSelection">
                                                <span>' . $Content . '</span>
                                            </div>
                                        </div>
                                ';
                                
                                $row["nom_dimension"];
                                
                            
                            echo '<div class=sous_section>';
                            echo $divsSelec;
                            echo '</div>';
                        }
                    } catch (PDOException $e) {
                        // Gestion des erreurs
                        echo "Erreur de connexion : " . $e->getMessage();
                    }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>

