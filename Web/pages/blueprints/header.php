<div id=enTete>
    <img id=icon src= <?php echo $chemin_absolu . 'images/Eye.jpg' ?>>
    <div id=divTitre>
        <p class=TitrePrincipal> Les Chroniques de la Faille </p>
        <p class=TitrePrincipal> Les mondes oubliés </p>
    </div>

    <nav id=nav>
        <ul class=menu>
            <li class=menu-item>
                <div id=liRaces class=divLi onclick=window.location.href='./Races.php'>
                    <a> Races </a>
                    <img class=small-icon src= <?php echo $chemin_absolu . "/images/petite_img/fleche-deroulante.png" ?>>
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
                                echo " 
                                    <li>
                                        <div class=liIntro onclick=\"window.location.href='$chemin_absolu/pages/Races/" . $nomRace . ".php?race=" . urlencode($nomRace) . "'\">
                                            <a> $nomRace </a>
                                        </div>
                                    </li>
                                ";
                            }
                        } catch (Exception $e) {
                            echo "". $e->getMessage() ."";
                        }
                    ?>
                </ul>
            </li>
        </ul>
    </nav>

    <div id=divacceuil>
        
                <?php
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