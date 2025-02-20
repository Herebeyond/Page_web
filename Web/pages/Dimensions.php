<?php
include "./blueprints/page_init.php";
?>

<html>
    <head>
        <link rel="stylesheet" href="../style/PageStyle.css?ver=<?php echo time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <title>
            Page d'Accueil
        </title>
    </head>
    <body>
        <div id=global>

            <?php include "./blueprints/header.php" ?>
            
            <div id=englobe>
            
                <div class=texteGauche> <!-- Div de gauche -->
                    <div id=enTeteTexteGauche>
                        <?php
                            for($i=0; $i<4; $i++) {
                                echo "<div><img src='../images/Icon.png'></div>";
                            }?> <!-- permet de créer 4 images identiques comme décoration du texte de gauche-->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>
                
                <div class="textePrincipal" style="opacity: 100%;"> <!-- Div de droite -->
                    <div>
                        <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
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
                            } elseif (($user["admin"]) == null || ($user["admin"]) == '') {
                            } else {
                                echo "erreur dans la colonne admin<br>";
                            }
                        }

                        sort($pages); // tri le tableau par ordre alphabétique
                        $frstLetter = ""; // initialise la variable $frstLetter
                        echo "<span>$frstLetter</span>"; // affiche la première lettre

                        // Début de la liste non ordonnée
                        echo "<ul>";

                        // Parcours du tableau et affichage des éléments dans la liste
                        foreach ($pages as $page) { // pour chaque élément du tableau $pages on affiche un lien vers la page correspondante
                            
                            if (((isset($_SESSION['user']) && ($autorisation[$page] == 'admin' && ($user['admin']) == 1)) || $autorisation[$page] == 'all') && $type[$page] == 'Dimensions') { // si l'utilisateur est connecté et qu'il est admin ou que la page est public
                                // si la première lettre de l'élément est différente de la première lettre du premier élément du tableau $pages on ferme la liste et on en ouvre une nouvelle
                                // ça permet de regrouper les éléments par première lettre
                                if(mb_substr($page, 0, 1) != $frstLetter) { 
                                    echo "</ul>";
                                    $frstLetter = mb_substr($page, 0, 1);
                                    echo "<span>$frstLetter</span>";
                                    echo "<ul>";
                                }
                                echo "<li><a href='./" . $page . ".php'>$page</a></li>"; // lien vers la page correspondante aux éléments du tableau $pages
                            
                            }
                            
                        }
                        

                        // Fin de la liste
                        echo "</ul>";
                        ?>
                    </div>
                </div>
                

            </div>

        </div>

    </body>
</html>