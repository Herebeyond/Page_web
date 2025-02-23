<?php
require "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page
include "./blueprints/gl_ap_start.php";
?>

<div id='textePrincipal'> <!-- Div de droite -->
    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
    <span class='Titre'> Dimensions </span> <!-- affiche le nom de la specie en entête -->
    <?php
    try {
        // génère le texte principal de la page
        echo '<span>' . nl2br(sanitize_output(file_get_contents("../texte/dimensions.txt"))) . '</span>';
        echo '<br><br>';

        $dimensionsInfos = $pdo->prepare("SELECT * FROM dimensions");
        $dimensionsInfos->execute();
        while ($row = $dimensionsInfos->fetch(PDO::FETCH_ASSOC)) {
            // génère les divs éventuelles pour chaque races
            $divsSelec = '';
                $DimensionName = sanitize_output($row['nom_dimension']);
                $DimensionType = sanitize_output($row['type']);
                if ($DimensionType == null || $DimensionType == '') {
                    $DimensionType = 'Not specified';
                }

                $Reality = sanitize_output($row['reality']);
                if ($Reality == null || $Reality == '') {
                    $Reality = 'Not specified';
                }

                $GodHome = sanitize_output($row['god_home']);
                if ($GodHome == null || $GodHome == '') {
                    $GodHome = 'Not specified';
                }

                $Content = sanitize_output($row['content']);
                if ($Content == null || $Content == '') {
                    $Content = 'Not specified';
                }

                // Création d'une div pour chaque race
                $divsSelec .= '
                        <span class=nomDimension> ' . $DimensionName . '</span>
                        <div class="selection">
                            <div class=infobox>
                                <div class=infos>
                                    <div>
                                        <p class=infosP> Dimension type : </p>
                                        <p class=infosT>' . $DimensionType . '</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Which reality : </p>
                                        <p class=infosT>' . $Reality . '</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Which Gods live here : </p>
                                        <p class=infosT>' . $GodHome . '</p>
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
        echo "Erreur de connexion : " . sanitize_output($e->getMessage());
    }
    ?>
</div>

<?php
include "./blueprints/gl_ap_end.php";
?>