<div id=enTete>
    <img id=icon src= <?php echo $chemin_absolu . 'images/Eye.jpg' ?>>
    <div id=divTitre>
        <span class=TitrePrincipal> Les Chroniques de la Faille </span>
        <span class=TitrePrincipal> Les mondes oubliés </span>
    </div>

    <nav id=nav>
        <ul class="menu">
            <li class="menu-item">
                <div id="liNavigation" class="divLi" onclick="window.location.href='./Accueil.php'">
                    <a> Navigation </a>
                    <img class="small-icon" src= <?php echo $chemin_absolu . "images/petite_img/fleche-deroulante.png" ?>>
                </div>
                <ul class="dropdown">
                    <?php // affichage de toutes les pages
                        try {
                            // Lire les noms de fichiers dans le dossier pages
                            $pages = [];
                            $dir = "../pages";
                            if (is_dir($dir)) { // si le dossier existe
                                if ($dh = opendir($dir)) { // ouvre le dossier en lecture
                                    while (($file = readdir($dh)) !== false) { // lit les fichiers du dossier
                                        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php' && $file != "Accueil.php") { // si le fichier n'est pas un dossier et a pour extension php on l'ajoute au tableau $pages
                                            $pages[] = pathinfo($file, PATHINFO_FILENAME);
                                        }
                                    }
                                    closedir($dh); // ferme le dossier en lecture
                                }
                            }

                            // Parcours du tableau et affichage des éléments dans la liste
                            foreach ($pages as $page) { // pour chaque élément du tableau $pages on affiche un lien vers la page correspondante
                                if ($autorisation[$page] == 'all' && $type[$page] == 'common' ) { // si la page est public et fait partie du groupe principal
                                    echo '
                                        <li>
                                            <div class=liIntro onclick=window.location.href="' . $chemin_absolu . 'pages/' . $page . '.php">
                                                <span> ' . $page . '</span>';
                                                foreach ($pages as $page2) {
                                                    if ($type[$page2] == $page) {
                                                        echo '
                                                <img class="small-icon" src=' . $chemin_absolu . 'images/petite_img/fleche-deroulante.png>
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
                                                    <div class=liIntro onclick=window.location.href="' . $chemin_absolu . 'pages/' . $page2 . '.php">
                                                        <span>' . $page3 . '</span>
                                                    </div>
                                                </li>
                                            ';
                                        }
                                    }
                                    echo '
                                            </ul>
                                        </li>'; // lien vers la page correspondante aux éléments du tableau $pages
                                    // le onclick=window.location. me permet d'enlever le style de police bleu souligné des liens
                                }
                            }
                        } catch (Exception $e) {
                            echo "". $e->getMessage() ."";
                        }
                    ?>
                </ul>
            </li>
        </ul>
        <ul class=menu>
            <li class=menu-item>
                <div id=liSpecies class=divLi onclick=window.location.href='./Species.php'>
                    <span> Species </span>
                    <img class=small-icon src= <?php echo $chemin_absolu . "images/petite_img/fleche-deroulante.png" ?>>
                </div>
                <ul class="dropdown">
                    <?php // affichage de toutes les species
                        try {
                            // Récupération des données du tableau Species
                            $query = $pdo->query("SELECT * FROM Species ORDER BY id_specie;");

                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                
                                // Création d'un li pour chaque species
                                $nomSpecie = $row["nom_specie"];
                                echo ' 
                                    <li>
                                        <div class=liIntro onclick=window.location.href="' . $chemin_absolu . 'pages/Affichage_specie.php?specie=' . urlencode($nomSpecie) . '">
                                            <span> ' . $nomSpecie . '</span>
                                        </div>
                                    </li>
                                '; // le onclick=window.location. me permet d'enlever le style de police bleu souligné des liens
                            }
                        } catch (Exception $e) {
                            echo "". $e->getMessage() ."";
                        }
                    ?>
                </ul>
            </li>
        </ul> 
        <ul class=menu>
            <li class=menu-item>
                <div id=liRaces class=divLi onclick=window.location.href='./Races.php'>
                    <span> Races </span>
                    <img class=small-icon src= <?php echo $chemin_absolu . "images/petite_img/fleche-deroulante.png" ?>>
                </div>
                <ul class="dropdown">
                    <?php // affichage de toutes les races
                        try {
                            // Récupération des données du tableau Races
                            $query = $pdo->query("SELECT * FROM Races ORDER BY id_race;");

                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                
                                // Création d'un li pour chaque races
                                $nomRace = $row["nom_race"];
                                $Correspondance = $row["correspondance"];
                                echo ' 
                                    <li>
                                        <div class=liIntro onclick=window.location.href="' . $chemin_absolu . 'pages/Affichage_specie.php?specie=' . urlencode(str_replace(" ", "_", $Correspondance)) . '&race=' . urlencode(str_replace(" ", "_", $nomRace)) . '">
                                            <span> ' . $nomRace . '</span>
                                        </div>
                                    </li>
                                '; // le onclick=window.location. me permet d'enlever le style de police bleu souligné des liens
                            }
                        } catch (Exception $e) {
                            echo "". $e->getMessage() ."";
                        }
                    ?>
                </ul>
            </li>
        </ul>

        <?php

        // Lire les noms de fichiers dans le dossier pages
        $pages = [];
        $dir = "../pages";
        if (is_dir($dir)) { // si le dossier existe
            if ($dh = opendir($dir)) { // ouvre le dossier en lecture
                while (($file = readdir($dh)) !== false) { // lit les fichiers du dossier
                    if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php' && $file != "Accueil.php") { // si le fichier n'est pas un dossier et a pour extension php on l'ajoute au tableau $pages
                        $pages[] = pathinfo($file, PATHINFO_FILENAME);
                    }
                }
                closedir($dh); // ferme le dossier en lecture
            }
        }


        /// VERIFICATION ADMIN
        if (isset($_SESSION['user'])) {
            // Récupérer le nom d'utilisateur depuis la base de données
            $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']]);
            $user = $stmt->fetch();
            
            // vérifie si l'utilisateur est admin ou non
            if (($user['admin']) == 1 ) { 
                echo '
                    <ul class=menu>
                        <li class=menu-item>
                            <div id=liAdmin class=divLi>
                                <span> Admin </span>
                                <img class=small-icon src="' . $chemin_absolu . 'images/petite_img/fleche-deroulante.png">
                            </div>
                ';

                echo '<ul class="dropdown">';
                // Parcours du tableau et affichage des éléments dans la liste
                foreach ($pages as $page) { // pour chaque élément du tableau $pages on affiche un lien vers la page correspondante
                    if ($autorisation[$page] == 'admin') {
                        echo '
                            <li>
                                <div class=liIntro onclick=window.location.href="' . $chemin_absolu . 'pages/' . $page . '.php">
                                    <span>' . $page . '</span>
                                </div>
                            </li>'; // lien vers la page correspondante aux éléments du tableau $pages
                            // le onclick=window.location. me permet d'enlever le style de police bleu souligné des liens
                    }
                }
                echo '</ul></li></ul>';

            } elseif (($user["admin"]) == null) {
            } else {
                echo "erreur dans la colonne admin<br>";
            }
        }
            
        ?>
    </nav>

    <div id=divacceuil>
        
                <?php
                include '../pages/scriptes/autorisation.php'; // inclut le fichier autorisation.php

                if (isset($_SESSION['user'])) { // si l'utilisateur est connecté on affiche son nom
                    echo '<div id="LoginCo">'; // div pour le nom de l'utilisateur et le lien de déconnexion
                    // Récupérer le nom d'utilisateur depuis la base de données
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user']]);
                    $user = $stmt->fetch(); 
                    echo "<span>Welcome, " . htmlspecialchars($user['username']) . "!</span>"; // affiche le nom de l'utilisateur
                    echo '<a href="' . $chemin_absolu . 'login/logout.php">Disconnect</a>'; // lien de déconnexion
                } else {
                    echo '<div id="LoginDeco">'; // div pour les liens de connexion et d'inscription
                    echo '<a href="' . $chemin_absolu . 'login/login.php">Sign In</a> <span> &nbsp | &nbsp </span> <a href="' . $chemin_absolu . 'login/register.php">Register</a>'; // liens de connexion et d'inscription
                }
                ?>
        </div>
        <div id=acceuil onclick=window.location.href='<?php echo $chemin_absolu . "pages/Accueil.php" ?>'>
            <a> La Grande Librairie </a>
        </div>
    </div>
</div>