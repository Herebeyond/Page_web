<?php
require "./blueprints/page_init.php"; // includes the page initialization file
include "./blueprints/gl_ap_start.php";
?>

<div id='mainText'> <!-- Right div -->
    <a id=Return onclick='window.history.back()'> Return </a><br>
    <span class='title'> Dimensions </span> <!-- display the title -->
    <?php
    try {
        // generate the main text of the page
        echo '<span>' . nl2br(sanitize_output(file_get_contents("../texte/dimensions.txt"))) . '</span>';
        echo '<br><br>';

        $dimensionsInfos = $pdo->prepare("SELECT * FROM dimensions");
        $dimensionsInfos->execute();
        while ($row = $dimensionsInfos->fetch(PDO::FETCH_ASSOC)) {
            // generate the divs for each dimension
            $divsSelec = '';
                $DimensionName = sanitize_output($row['dimension_name']);
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

                // Create a div for each dimension
                $divsSelec .= '
                        <span class=dimensionName> ' . $DimensionName . '</span>
                        <div class="selection">
                            <div class=infobox>
                                <div class=infos>
                                    <div>
                                        <p class=infosP> Dimension type: </p>
                                        <p class=infosT>' . $DimensionType . '</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Which reality: </p>
                                        <p class=infosT>' . $Reality . '</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Which Gods live here: </p>
                                        <p class=infosT>' . $GodHome . '</p>
                                    </div>
                                </div>
                            </div>
                            <div class="texteSelection">
                                <span>' . $Content . '</span>
                            </div>
                        </div>
                ';
                
                $row["dimension_name"];
                
            
            echo '<div class=sous_section>';
            echo $divsSelec;
            echo '</div>';
        }
    } catch (PDOException $e) {
        // Error handling
        echo "Connection error: " . sanitize_output($e->getMessage());
    }
    ?>
</div>

<?php
include "./blueprints/gl_ap_end.php";
?>