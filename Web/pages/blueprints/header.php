


<div id=header>
    <img id=icon src='../images/Eye.jpg'> <!-- Display the header icon -->
    <div id=divTitre>
        <span class=Title>Les Chroniques de la Faille</span>
        <span class=Title>Les mondes oubliés</span>

    </div>

    <nav id=nav>
        <ul class="menu">
            <li class="menu-item">
                <div id="liNavigation" class="divLi" onclick="window.location.href='./Homepage.php'">
                    <a> Navigation </a>
                    <img class="small-icon" src="../images/small_img/fleche-deroulante.png" > <!-- Dropdown arrow icon -->
                </div>
                <ul class="dropdown">
                    <?php // Display all pages
                        try {
                            // Read file names in the pages directory
                            $pages = [];
                            $dir = "../pages";
                            if (is_dir($dir)) { // Check if the directory exists
                                if ($dh = opendir($dir)) { // Open the directory for reading
                                    while (($file = readdir($dh)) !== false) { // Read files in the directory
                                        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php' && $file != "Homepage.php") { // If the file is not a directory and has a .php extension, add it to the $pages array
                                            $pages[] = pathinfo($file, PATHINFO_FILENAME);
                                        }
                                    }
                                    closedir($dh); // Close the directory
                                }
                            }

                            // Loop through the array and display the elements in the list
                            foreach ($pages as $page) { // For each element in the $pages array, display a link to the corresponding page
                                if ($authorisation[$page] == 'all' && $type[$page] == 'common' ) { // If the page is public and part of the main group
                                    echo '
                                        <li class="dropdownFirstLevel">
                                            <div class=liIntro onclick=window.location.href="./' . sanitize_output($page) . '.php">
                                                <span> ' . sanitize_output($page) . '</span>';
                                                foreach ($pages as $page2) { // 
                                                    if ($type[$page2] == $page) {
                                                        echo '
                                                <img class="small-icon" src=../images/small_img/fleche-deroulante.png>
                                                            ';
                                                        break;
                                                    }
                                                }
                                    echo    '</div>
                                            <ul class="dropdown2">';
                                    foreach ($pages as $page3) {
                                        if ($type[$page3] == 'Dimensions' && $page == 'Dimensions') {
                                        echo '
                                            
                                                <li>
                                                    <div class=liIntro onclick=window.location.href="./' . sanitize_output($page3) . '.php">
                                                        <span>' . sanitize_output($page3) . '</span>
                                                    </div>
                                                </li>
                                            ';
                                        }
                                    }
                                    echo '
                                            </ul>
                                        </li>'; // Link to the corresponding page in the $pages array
                                }
                            }
                        } catch (Exception $e) {
                            echo "". sanitize_output($e->getMessage()) ."";
                        }
                    ?>
                </ul>
            </li>
        </ul>

        <?php /*
        Stopped using this dropdown menu because it was becoming too long and not very useful

        // Display all species un a dropdown menu
        <ul class=menu>
            <li class=menu-item>
                <div id=liSpecies class=divLi onclick=window.location.href='./Species.php'>
                    <span> Species </span>
                    <img class=small-icon src="../images/small_img/fleche-deroulante.png" > <!-- Dropdown arrow icon -->
                </div>
                <ul class="dropdown">
                    <?php // Display all species
                        try {
                            // Retrieve data from the Species table
                            $queryS = $pdo->prepare("SELECT * FROM Species ORDER BY id_specie;");
                            $queryS->execute();
                        
                            while ($rowS = $queryS->fetch(PDO::FETCH_ASSOC)) { // For each species in the table
                                // Make the query for each race restart from the start for each species
                                $queryR = $pdo->prepare("SELECT * FROM Races WHERE correspondence = ? ORDER BY id_race;");
                                $queryR->execute([$rowS["id_specie"]]);
                        
                                // Check if there are any races for the species
                                $hasRaces = $queryR->rowCount() > 0;
                        
                                // Create a list item for each species
                                $specieName = $rowS["specie_name"];
                                echo ' 
                                    <li class="dropdownFirstLevel">
                                        <div class=liIntro onclick="window.location.href=\'./Beings_display.php?specie=' . sanitize_output($specieName) . '\'">
                                            <span> ' . $specieName . '</span>';
                                if ($hasRaces) {
                                    echo '  <img class="small-icon" src=../images/small_img/fleche-deroulante.png>';
                                }
                                echo '
                                        </div>
                                    </li>';
                        
                                // Create the dropdown2 outside of dropdownFirstLevel
                                echo '<ul class="dropdown2" data-parent="' . sanitize_output($specieName) . '">'; // Dropdown list with limited height and scroll
                                while ($rowR = $queryR->fetch(PDO::FETCH_ASSOC)) {
                                    // For each race in the table
                                    $raceName = $rowR["race_name"];
                                    if ($rowR["correspondence"] == $rowS["id_specie"]) {
                                        echo '
                                            <li>
                                                <div class="liIntro" onclick="window.location.href=\'./Beings_display.php?specie=' . str_replace(" ", "_", sanitize_output($specieName)) . '&race=' . str_replace(" ", "_", sanitize_output($raceName)) . '\'">
                                                    <span>' . sanitize_output($raceName) . '</span>
                                                </div>
                                            </li>';
                                    }
                                }
                                echo '</ul>';
                            }
                        } catch (Exception $e) {
                            echo "". sanitize_output($e->getMessage()) ."";
                        }
                        
                    ?>
                </ul>
            </li>
        </ul> 
        */ ?>
        


        <?php

        // Read file names in the pages directory
        $pages = [];
        $dir = "../pages";
        if (is_dir($dir)) { // Check if the directory exists
            if ($dh = opendir($dir)) { // Open the directory for reading
                while (($file = readdir($dh)) !== false) { // Read files in the directory
                    if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php') { // If the file is not a directory and has a .php extension, add it to the $pages array
                        $pages[] = pathinfo($file, PATHINFO_FILENAME);
                    }
                }
                closedir($dh); // Close the directory
            }
        }

        // ADMIN VERIFICATION
        if (isset($_SESSION['user'])) {
            // Retrieve the username from the database
            $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']]);
            $user = $stmt->fetch();
            
            // Check if the user is an admin
            if (($user['admin']) == 1 ) { 
                echo '
                    <ul class=menu>
                        <li class=menu-item>
                            <div id=liAdmin class=divLi>
                                <span> Admin </span>
                                <img class=small-icon src="../images/small_img/fleche-deroulante.png">
                            </div>
                ';

                echo '      <ul class="dropdown">';
                // Loop through the array and display the elements in the list
                foreach ($pages as $page) { // For each element in the $pages array, display a link to the corresponding page
                    if ($authorisation[$page] == 'admin') {
                        echo '
                            <li>
                                <div class=liIntro onclick=window.location.href="./' . sanitize_output($page) . '.php">
                                    <span>' . sanitize_output($page) . '</span>
                                </div>
                            </li>'; // Link to the corresponding page in the $pages array
                            // The onclick=window.location. allows me to remove the blue underlined link style
                    }
                }
                echo '      </ul>
                        </li>
                    </ul>';

            } elseif (($user["admin"]) == null) {
            } else {
                echo "error in the admin column<br>";
            }
        }
            
        ?>
    </nav>
    
    <div id=divConnect>
        <?php
        require_once './scriptes/authorisation.php'; // Include the authorisation script to check user permissions

        if (isset($_SESSION['user'])) { // If the user is logged in, display their name
            // Retrieve the username and icon from the database
            $stmt = $pdo->prepare("SELECT username, icon FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']]);
            $user = $stmt->fetch();

            if ($user['icon'] == null) { // If the user has no icon, set a default icon
                $user['icon'] = "small_img/default_user_icon.png";
            }


            echo '<div id="Login">'; // Div for the user icon and name   
            echo '  <img id="iconUser" src="../images/' . sanitize_output($user['icon']) . '">'; // Display the user icon

            echo '  <div id="LoginCo">'; // Div for the username and logout link
            echo "      <span>Welcome<br>" . sanitize_output($user['username']) . "!</span>"; // Display the username
            echo '      <a href="../login/logout.php">Disconnect</a>'; // Logout link
            echo '  </div>';
            echo '</div>';
        } else {
            echo '<div id="LoginDeco">'; // Div for the login and register links
            echo '<a href="../login/login.php">Sign In</a> <span> &nbsp | &nbsp </span> <a href="../login/register.php">Register</a>'; // Login and register links
            echo '</div>';
        }
        ?>
    </div>
    <div id=Connect onclick=window.location.href="./Homepage.php">
        <span> La Grande Librairie </span> <!-- Link to the home page -->
    </div>
</div>
<script>
document.querySelectorAll('.dropdownFirstLevel').forEach(item => {
    const specieName = item.querySelector('.liIntro span').textContent.trim();
    const dropdown = document.querySelector(`.dropdown2[data-parent="${specieName}"]`);

    let hoverTimeout; // Variable to track the timeout

    if (dropdown) {
        // Show dropdown2 when hovering over dropdownFirstLevel
        item.addEventListener('mouseenter', () => {
            clearTimeout(hoverTimeout); // Clear any existing timeout
            dropdown.style.display = 'block';
            dropdown.style.position = 'absolute';
            dropdown.style.top = `${item.offsetTop + item.offsetHeight}px`;
            dropdown.style.left = `${item.offsetLeft}px`;
        });

        // Hide dropdown2 when leaving dropdownFirstLevel
        item.addEventListener('mouseleave', () => {
            hoverTimeout = setTimeout(() => {
                dropdown.style.display = 'none';
            }, 10); // Add a small delay to allow hover transition
        });

        // Keep dropdown2 visible when hovering over it
        dropdown.addEventListener('mouseenter', () => {
            clearTimeout(hoverTimeout); // Clear the timeout to prevent hiding
            dropdown.style.display = 'block';
        });

        // Hide dropdown2 when leaving it
        dropdown.addEventListener('mouseleave', () => {
            hoverTimeout = setTimeout(() => {
                dropdown.style.display = 'none';
            }, 10); // Add a small delay to allow hover transition
        });
    }
});
</script>