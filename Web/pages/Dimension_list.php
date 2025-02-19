<?php
session_start();
require '../login/db.php'; // Connexion à la base
include './scriptes/autorisation.php'; // inclut le fichier autorisation.php
?>

<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?> <!-- défini le chemin absolu commun à tous les chemins, le reste du chemin sera écrit pour chaque cas -->
        <link rel='stylesheet' href='<?php echo $chemin_absolu . '/style/PageStyle.css?ver=' . time()?>' > <!-- le ?ver=' . time() permet de générer une version différente à chaque fois et ainsi recharger le css à chaque fois que la page est rechargée car il ignore le cache -->
        <title>
            Angels
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
                                echo '<div><img src=' . $chemin_absolu . 'images/Icon.png></div>';
                            }?> <!-- permet de créer 4 images identiques et espacées correctement -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("" . $chemin_absolu . "texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>

                <div id='affichage_specie'> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <span class='Titre'> Dimensions </span> <!-- affiche le nom de la specie en entête -->
                    <?php
                    try {
                        // génère le texte principal de la page
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("" . $chemin_absolu . "texte/dimensions.txt"))) . '</span>';
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
            
            