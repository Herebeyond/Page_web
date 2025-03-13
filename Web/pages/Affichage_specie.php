<?php
require "./blueprints/page_init.php"; // includes the page initialization file
require "./blueprints/gl_ap_start.php"; // includes the start of the general page file

if (isset($_GET['specie'])) {
    // Retrieve and sanitize the 'race' and 'specie' parameters
    $specie = sanitize_output($_GET['specie']);

    // Prepare and execute the query to retrieve specie information
    try {
        $stmt = $pdo->prepare("SELECT * FROM species WHERE specie_name = ?");
        $stmt->execute([$specie]);
        $specieInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (isset($_GET['race'])) {
            $race = sanitize_output($_GET['race']);

            // Prepare and execute the query to retrieve race information
            $stmt = $pdo->prepare("SELECT * FROM races WHERE race_name = ?");
            $stmt->execute([$race]);
            $raceInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Error handling
        echo "Connection error: " . $e->getMessage();
    }
}
?>

<div id='mainText'> <!-- Right div -->
    <a id=Return onclick='window.history.back()'> Return </a><br>
    <span class='title'> <?php echo sanitize_output($specie); ?> </span> <!-- display the specie name as header -->
    <?php
        try {
            // Retrieve data from the races table
            $stmt = $pdo->prepare("SELECT * FROM races as r LEFT JOIN species as s ON r.correspondence = s.specie_name WHERE specie_name = ? ORDER BY r.id_race;");
            $stmt->execute([$specie]);
            $queryR = $pdo->prepare("SELECT * FROM species WHERE specie_name = ?");
            $queryR->execute([$specie]);

            // Generate the main text of the page
            $rowR = $queryR->fetch(PDO::FETCH_ASSOC);
            if ($rowR) { // the if instead of while allows retrieving a single row
                if ($rowR['content_specie'] != '' && $rowR['content_specie'] != null) {
                    echo "<p>" . nl2br(sanitize_output($rowR['content_specie'])) . "</p>";
                } else {
                    echo "No content found for the $specie Specie.";
                }
            } else {
                echo "No data found for the $specie Specie.";
            }

            echo '<br><br>';

            // Generate the divs for each race
            $divsSelec = '';
            while ($rowF = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $imgPath = isset($rowF['icon_race']) ? $rowF['icon_race'] : null; // check if the image exists
                if ($imgPath == null || $imgPath == '') { // if the image doesn't exist or is empty, use a default image
                    $imgPath = '../images/icon_default.png'; // path to the default image
                } else { // if the image exists, use it
                    $imgPath = str_replace(" ", "_", "$imgPath"); // replace spaces with underscores for file names
                    $imgPath = "../images/" . sanitize_output($imgPath); // path to the image, sanitize_output escapes special characters in the string (such as ' and ") and prevents them from closing strings
                }

                if (!isImageLinkValid($imgPath)) { // if the image is not valid
                    $imgPath = '../images/icon_invalide.png'; // path to the invalid image
                }

                $lifespan = $rowF['lifespan'];
                if ($lifespan == null || $lifespan == '') {
                    $lifespan = 'Not specified';
                } elseif ($lifespan == 'Immortal' || $lifespan == 'immortal') {
                    $lifespan = 'Immortal'; // if the lifespan is 'Immortal', leave it as is
                } else {
                    $lifespan = $lifespan . ' years'; // add 'years' to the end of the lifespan
                }

                $homeworld = $rowF['homeworld'];
                if ($homeworld == null || $homeworld == '') {
                    $homeworld = 'Not specified';
                }

                $country = $rowF['country'];
                if ($country == null || $country == '') {
                    $country = 'Not specified';
                }

                $habitat = $rowF['habitat'];
                if ($habitat == null || $habitat == '') {
                    $habitat = 'Not specified';
                }

                // Create a div for each race
                $divsSelec .= " 
                        <div class='selectionRace fadeIn' id=" . sanitize_output(str_replace(" ", "_", $rowF['race_name'])) . ">
                            <div class=infobox>
                                <div class='classImgSelectionRace'>
                                    <img class='imgSelectionRace' src='" . $imgPath . "'>
                                    <span class='
                                    ";
                                    if ($rowF['race_is_unique'] == "0") {
                                        $divsSelec .= 'raceNameMulti';
                                    } else {
                                        $divsSelec .= 'raceNameUnique';
                                    }

                                    $divsSelec .=
                                "'>" . sanitize_output($rowF['race_name']) . "</span>
                                </div>
                                <div class=infos>
                                    <div>
                                        <p class=infosP> Lifespan: </p>
                                        <p class=infosT>" . sanitize_output($lifespan) . "</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Homeworld: </p>
                                        <p class=infosT>" . sanitize_output($homeworld) . "</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Country: </p>
                                        <p class=infosT>" . sanitize_output($country) . "</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Habitat: </p>
                                        <p class=infosT>" . sanitize_output($habitat) . "</p>
                                    </div>";

                                    if ($rowF['race_is_unique'] == "1") {
                                        $divsSelec .=
                                        "<div>
                                            <p class=infosT> Only of its Race </p>
                                        </div>";
                                    }
                                    $divsSelec .=
                                "</div>
                            </div>
                            <div class='texteSelection'>
                                <p>" . nl2br(sanitize_output($rowF['content_race'] ?? '')) . "</p>
                            </div>
                        </div>
                ";
            }
            
        } catch (PDOException $e) {
            $divsSelec = "Connection error: " . $e->getMessage();
        }
        
        echo '<div class=sous_section>';
        echo $divsSelec;
        echo '</div>';
    ?>
</div>
</div>
</div>
<script>
    // scroll to the race selected if selected in the URL
    document.addEventListener("DOMContentLoaded", function() { // wait for the document to load before executing the script
        const urlParams = new URLSearchParams(window.location.search);
        const race = urlParams.get('race'); 
        if (race) { // if the race variable is in the URL
            const raceElement = document.getElementById(race);
            if (raceElement) { // find the div with the id
                raceElement.scrollIntoView({ behavior: 'smooth' }); // scroll to the div with the race id
            }
        }
    });

    // Intersection Observer to fade in the elements when they are in the viewport
    // Check if the elements of the class .fadeIn are actually visible to activate the fade-in effect
    document.addEventListener("DOMContentLoaded", function() {
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                        observer.unobserve(entry.target); // stop observing the element once it's visible
                    }
                });
            });

            document.querySelectorAll(".fadeIn").forEach(element => {
                observer.observe(element);
            });
        });
</script>
</body>
</html>

