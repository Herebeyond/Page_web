<div id=header>
    <img id=icon src='../images/Eye.jpg'> <!-- Display the header icon -->
    <div id=divTitre>
        <span class=Title> Les Chroniques de la Faille </span> <!-- Main title -->
        <span class=Title> Les mondes oubliés </span> <!-- Subtitle -->
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
                                        <li>
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
                                // Make the query for eache races restart from the start for each species
                                $queryR = $pdo->prepare("SELECT * FROM Races WHERE correspondence = ? ORDER BY id_race;");
                                $queryR->execute([$rowS["specie_name"]]);

                                // Check if there are any races for the species
                                $hasRaces = $queryR->rowCount() > 0;
                                
                                // Create a list item for each species
                                $specieName = sanitize_output($rowS["specie_name"]);
                                echo ' 
                                    <li class="dropdownFirstLevel">
                                        <div class=liIntro onclick=window.location.href="./Affichage_specie.php?specie=' . urlencode($specieName) . '">
                                            <span> ' . $specieName . '</span>';
                                if ($hasRaces) { // If the species has no race to show, doesn't put the small arrow next to their name
                                    echo '
                                            <img class="small-icon" src=../images/small_img/fleche-deroulante.png>';
                                }                                        
                                    echo'
                                        </div>
                                        <div>
                                            <ul class="dropdown2">';
                                    while ($rowR = $queryR->fetch(PDO::FETCH_ASSOC)) { // For each races in the table

                                        // Create a list item for each races corresponding to the specie
                                        $raceName = sanitize_output($rowR["race_name"]);
                                        if ($rowR["correspondence"] == $specieName) { // if the race has the same correspondence as the specie it is added
                                        echo '
                                            
                                                <li>
                                                    <div class=liIntro onclick=window.location.href="./Affichage_specie.php?specie=' . sanitize_output($specieName) . '&race=' . sanitize_output($raceName) . '">
                                                        <span>' . sanitize_output($raceName) . '</span>
                                                    </div>
                                                </li>
                                            ';
                                        }
                                    }
                                    echo '
                                            </ul>
                                        </div>
                                    </li>
                                '; // The onclick=window.location. allows me to remove the blue underlined link style
                            }
                        } catch (Exception $e) {
                            echo "". sanitize_output($e->getMessage()) ."";
                        }
                    ?>
                </ul>
            </li>
        </ul> 
        <ul class=menu>
            <li class=menu-item>
                <div id=liRaces class=divLi onclick=window.location.href='./Races.php'>
                    <span> Races </span>
                    <img class=small-icon src="../images/small_img/fleche-deroulante.png" > <!-- Dropdown arrow icon -->
                </div>
                <ul class="dropdown">
                    <?php // Display all races
                        try {
                            // Retrieve data from the Races table
                            $query = $pdo->prepare("SELECT * FROM Races ORDER BY id_race;");
                            $query->execute();

                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                
                                // Create a list item for each race
                                $nomRace = sanitize_output($row["race_name"]);
                                $correspondence = sanitize_output($row["correspondence"]);
                                echo ' 
                                    <li>
                                        <div class=liIntro onclick=window.location.href="./Affichage_specie.php?specie=' . urlencode(str_replace(" ", "_", $correspondence)) . '&race=' . urlencode(str_replace(" ", "_", $nomRace)) . '">
                                            <span> ' . $nomRace . '</span>
                                        </div>
                                    </li>
                                ';
                            }
                        } catch (Exception $e) {
                            echo "". sanitize_output($e->getMessage()) ."";
                        }
                    ?>
                </ul>
            </li>
        </ul>

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

                echo '<ul class="dropdown">';
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
                echo '</ul></li></ul>';

            } elseif (($user["admin"]) == null) {
            } else {
                echo "error in the admin column<br>";
            }
        }
            
        ?>
    </nav>
    
    <div id=divConnect>
        <?php
        include './scriptes/authorisation.php'; // Include the authorisation.php file

        if (isset($_SESSION['user'])) { // If the user is logged in, display their name
            echo '<div id="LoginCo">'; // Div for the username and logout link
            // Retrieve the username from the database
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']]);
            $user = $stmt->fetch(); 
            echo "<span>Welcome, " . sanitize_output($user['username']) . "!</span>"; // Display the username
            echo '<a href="../login/logout.php">Disconnect</a>'; // Logout link
            echo '</div>';
        } else {
            echo '<div id="LoginDeco">'; // Div for the login and register links
            echo '<a href="../login/login.php">Sign In</a> <span> &nbsp | &nbsp </span> <a href="../login/register.php">Register</a>'; // Login and register links
            echo '</div>';
        }
        ?>
        <div id=Connect onclick=window.location.href="./Homepage.php">
            <a> La Grande Librairie </a> <!-- Link to the home page -->
        </div>
    </div>
</div>