<div id=enTete>
    <img id=icon src= <?php echo $chemin_absolu . 'images/Eye.jpg' ?>>
    <div id=divTitre>
        <p class=TitrePrincipal> Les Chroniques de la Faille </p>
        <p class=TitrePrincipal> Les mondes oubliés </p>
    </div>

    <nav id=nav>
        <ul class=menu>
            <li class=menu-item>
                <div id=liSpecies class=divLi onclick=window.location.href='./Species.php'>
                    <a> Species </a>
                    <img class=small-icon src= <?php echo $chemin_absolu . "images/petite_img/fleche-deroulante.png" ?>>
                </div>
                <ul class="dropdown">
                    <?php // affichage de toutes les species
                        try {
                            // Connexion à la base de données MySQL
                            $host = 'db';
                            $dbname = 'univers';
                            $username = 'root';
                            $password = 'root_password';
                            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            // Récupération des données du tableau Species
                            $query = $pdo->query("SELECT * FROM Species ORDER BY id_specie;");

                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                
                                // Création d'un li pour chaque species
                                $nomSpecie = $row["nom_specie"];
                                echo ' 
                                    <li>
                                        <div class=liIntro>
                                            <a onclick=window.location.href="' . $chemin_absolu . 'pages/Affichage_specie.php?specie=' . urlencode($nomSpecie) . '"> ' . $nomSpecie . '</a>
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
                    <a> Races </a>
                    <img class=small-icon src= <?php echo $chemin_absolu . "images/petite_img/fleche-deroulante.png" ?>>
                </div>
                <ul class="dropdown">
                    <?php // affichage de toutes les races
                        try {
                            // Connexion à la base de données MySQL
                            $host = 'db';
                            $dbname = 'univers';
                            $username = 'root';
                            $password = 'root_password';
                            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            // Récupération des données du tableau Races
                            $query = $pdo->query("SELECT * FROM Races ORDER BY id_race;");

                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                
                                // Création d'un li pour chaque races
                                $nomRace = $row["nom_race"];
                                $Correspondance = $row["correspondance"];
                                echo ' 
                                    <li>
                                        <div class=liIntro>
                                            <a onclick=window.location.href="' . $chemin_absolu . 'pages/Affichage_specie.php?specie=' . urlencode(str_replace(" ", "_", $Correspondance)) . '&race=' . urlencode(str_replace(" ", "_", $nomRace)) . '"> ' . $nomRace . '</a>
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
                                <a> Admin </a>
                                <img class=small-icon src="' . $chemin_absolu . 'images/petite_img/fleche-deroulante.png">
                            </div>
                ';

                echo '<ul class="dropdown">';
                // Parcours du tableau et affichage des éléments dans la liste
                foreach ($pages as $page) { // pour chaque élément du tableau $pages on affiche un lien vers la page correspondante
                    if ($autorisation[$page] == 'admin') {
                        echo '
                            <li>
                                <div class=liIntro>
                                    <a onclick=window.location.href="' . $chemin_absolu . 'pages/' . $page . '.php">' . $page . '</a>
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