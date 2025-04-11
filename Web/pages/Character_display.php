<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file

// Retrieve the username from the database to check if the user is an admin
if (isset($_SESSION['user'])) {
    $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();
} else {
    $user = null; // if the user is not logged in, set user to null
}

$error_msg = ""; // initialize the error message variable

if (isset($_GET['race'])) {
    // Retrieve and sanitize the 'race' parameter
    $race = sanitize_output($_GET['race']);

    // Prepare and execute the query to retrieve race information
    try {
        $stmt = $pdo->prepare("SELECT * FROM races WHERE race_name = ?");
        $stmt->execute([$race]);
        $raceInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_race = $raceInfo['id_race']; // retrieve the id_race of the race

        // Fetch characters of the selected race
        $stmt = $pdo->prepare("SELECT * FROM characters WHERE correspondence = ?");
        $stmt->execute([$id_race]);
        $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Error handling
        $error_msg = "Connection error: " . $e->getMessage();
    }
} else {
    $error_msg = "No race selected.";
}
?>

<script>
    // Intersection Observer to fade in the elements when they are in the viewport
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
    <span class='title'> <?php echo sanitize_output($race); ?> </span> <!-- display the race name as header -->
    <?php
        try {
            // Generate the main text of the page
            if ($raceInfo['content_race'] != '' && $raceInfo['content_race'] != null) {
                echo "<p>" . nl2br(sanitize_output($raceInfo['content_race'])) . "</p>";
            } else {
                echo "No content found for the $race Race.";
            }

            echo '<br><br>';

            // Generate the divs for each character
            $divsSelec = '';
            foreach ($characters as $character) {
                $imgPath = isset($character['icon_character']) ? $character['icon_character'] : null; // check if the image exists
                if ($imgPath == null || $imgPath == '') { // if the image doesn't exist or is empty, use a default image
                    $imgPath = '../images/icon_default.png'; // path to the default image
                } else { // if the image exists, use it
                    $imgPath = str_replace(" ", "_", "$imgPath"); // replace spaces with underscores for file names
                    $imgPath = "../images/" . sanitize_output($imgPath)."<br>"; // path to the image, sanitize_output escapes special characters in the string (such as ' and ") and prevents them from closing strings
                }

                if (!isImageLinkValid($imgPath)) { // if the image is not valid
                    $imgPath = '../images/icon_invalide.png'; // path to the invalid image
                }

                $age = $character['age'];
                if ($age == null || $age == '') {
                    $age = 'Not specified';
                } else {
                    $age = $age . ' years'; // add 'years' to the end of the age
                }

                $habitat = $character['habitat'];
                if ($habitat == null || $habitat == '') {
                    $habitat = 'Not specified';
                }

                $country = $character['country'];
                if ($country == null || $country == '') {
                    $country = 'Not specified';
                }

                // Create a div for each character
                $divsSelec .= " 
                        <div class='selectionRace fadeIn' id=" . str_replace(" ", "_", sanitize_output($character['character_name'])) . ">
                            <div class=infobox>
                                <div class='infosTitle'>
                                    <img class='imgSelectionRace' src='" . $imgPath . "'>
                                    <span class='
                                    '>" . sanitize_output($character['character_name']) . "</span>
                                </div>
                                <div class=infos>
                                    <div>
                                        <p class=infosP> Age: </p>
                                        <p class=infosT>" . sanitize_output($age) . "</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Origin: </p>
                                        <p class=infosT>" . sanitize_output($habitat) . "</p>
                                    </div>
                                    <div>
                                        <p class=infosP> Origin: </p>
                                        <p class=infosT>" . sanitize_output($country) . "</p>
                                    </div>
                                </div>
                            </div>
                            <div class='texteSelection'>
                                <p>" . nl2br(sanitize_output($character['content_character'] ?? '')) . "</p>
                            </div>";
                            // If the user is logged and is an admin, display the edit button
                            if (isset($_SESSION['user']) && $user['admin'] == 1) {
                                $divsSelec .= "
                                    <div class='editButton'>
                                        <a href='Character_add.php?character_id=" . $character['id_character'] . "'>Edit</a>
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
    ?>
</div>

<?php
require_once "./blueprints/gl_ap_end.php"; // includes the end of the general page file
?>
