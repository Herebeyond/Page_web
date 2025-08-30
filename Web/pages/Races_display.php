<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file

if (isset($_SESSION['user'])) {
    // Retrieve the user role from the database to check if the user is an admin
    $stmt = $pdo->prepare("SELECT r.id as role_id, r.name as role_name FROM users u 
                          LEFT JOIN user_roles ur ON u.id = ur.user_id 
                          LEFT JOIN roles r ON ur.role_id = r.id 
                          WHERE u.id = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();
} else {
    $user = null; // if the user is not logged in, set user to null
}


$error_msg = ""; // initialize the error message variable

if (isset($_GET['specie'])) {
    // Retrieve and sanitize the 'race' and 'specie' parameters
    $specie = sanitize_output($_GET['specie']);
    // Convert underscores back to spaces for database lookup
    $specieForDB = str_replace("_", " ", $specie);

    // Prepare and execute the query to retrieve specie information
    try {
        $stmt = $pdo->prepare("SELECT * FROM species WHERE specie_name = ?");
        $stmt->execute([$specieForDB]);
        $specieInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$specieInfo) {
            $error_msg = "Specie '$specieForDB' not found.";
        } else {
            $id_specie = $specieInfo['id_specie']; // retrieve the id_specie of the specie
        }

        if (isset($_GET['race']) && $specieInfo) {
            $race = sanitize_output($_GET['race']);

            // Prepare and execute the query to retrieve race information
            $stmt = $pdo->prepare("SELECT * FROM races WHERE race_name = ?");
            $stmt->execute([$race]);
            $raceInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Error handling
        $error_msg = "Connection error: " . $e->getMessage();
    }
} else {
    $error_msg = "No specie selected.";
}
?>

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

<div id='mainText'> <!-- Right div -->
    <button id="Return" onclick="window.history.back()">Return</button><br><br>



    <?php if ($error_msg) { // display the error message if there is one
        echo "<span class='title'>" . sanitize_output($error_msg) . "</span>";
        exit; // exit the script if there is an error message
    }?>
    <span class='title'> <?php echo sanitize_output(str_replace("_", " ", $specie)); ?> </span> <!-- display the specie name as header -->
    <?php
        if (isset($id_specie)) { // Only proceed if specie was found
        try {
            // Retrieve data from the races table
            $stmt = $pdo->prepare("SELECT * FROM races as r JOIN species as s ON r.correspondence = s.id_specie WHERE id_specie = ? ORDER BY r.id_race;");
            $stmt->execute([$id_specie]);
            $queryR = $pdo->prepare("SELECT * FROM species WHERE specie_name = ?");
            $queryR->execute([$specieForDB]);



            // Generate the main text of the page
            $rowR = $queryR->fetch(PDO::FETCH_ASSOC);
            if ($rowR) { // the if instead of while allows retrieving a single row
                // Try different possible column names for content
                $content = null;
                if (isset($rowR['content_specie'])) {
                    $content = $rowR['content_specie'];
                } elseif (isset($rowR['content_Specie'])) {
                    $content = $rowR['content_Specie'];
                } elseif (isset($rowR['specie_content'])) {
                    $content = $rowR['specie_content'];
                } elseif (isset($rowR['content'])) {
                    $content = $rowR['content'];
                }
                
                if ($content != '' && $content != null) {
                    echo "<p>" . nl2br(sanitize_output($content)) . "</p>";
                } else {
                    echo "No content found for the " . str_replace("_", " ", $specie) . " Specie.";
                }
            } else {
                echo "No data found for the " . str_replace("_", " ", $specie) . " Specie.";
            }

            // If the user is logged and is an admin, display the edit button
            if (isset($_SESSION['user']) && $user && $user['role_id'] == 1 && $rowR) { // if the user is logged and is an admin (role_id = 1) and specie found
                echo "<div class='editButton'>
                        <a href='Specie_add.php?specie_id=" . sanitize_output($rowR['id_specie']) . "'>Edit</a>
                    </div>";
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
                // The id is important for the Intersection Observer to work (scroll to the div when a race selected in the URL)
                $divsSelec .= " 
                        <div class='selectionRace fadeIn' id=" . str_replace(" ", "_", sanitize_output($rowF['race_name'])) . ">
                            <div class=infobox>
                                <div class='infosTitle'>
                                    <img class='imgSelectionRace' src='" . $imgPath . "'>
                                    <span class='
                                    '>" . sanitize_output($rowF['race_name']) . "</span>
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
                                    </div>
                                </div>
                            </div>
                            <div class='texteSelection'>
                                <p>" . nl2br(sanitize_output($rowF['content_race'] ?? '')) . "</p>
                            </div>";
                            // If the user is logged and is an admin, display the edit button
                            if (isset($_SESSION['user']) && $user && $user['role_id'] == 1) {
                                $divsSelec .= "
                                    <div class='editButton'>
                                        <a href='Race_add.php?race_id=" . $rowF['id_race'] . "'>Edit</a>
                                    </div>";
                            }
                        
                        $divsSelec .= " 
                        </div>
                ";
            }
            
        } catch (PDOException $e) {
            $divsSelec = "Connection error: " . $e->getMessage();
        }
        
        echo '<div class=sous_section>';
        echo $divsSelec;
        echo '</div>';
        } // End of if (isset($id_specie)) condition
    ?>
</div>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>
