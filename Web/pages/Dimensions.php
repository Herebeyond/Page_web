<?php
require "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page
require "./blueprints/gl_ap_start.php";
?>
                
<div id="textePrincipal"> <!-- Div de droite -->

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

        // in case the first page is for a page that the user doesn't have access to
        if (((isset($_SESSION['user']) && ($autorisation[$page] == 'admin' && ($user['admin']) == 1)) || $autorisation[$page] == 'all') && $type[$page] == 'Dimensions') { // si l'utilisateur est (connecté et qu'il est admin ou que la page est public) et que le type de la page est Dimensions
            $frstLetter = mb_substr($pages[0], 0, 1); // récupère la première lettre du premier élément du tableau $pages
            echo "<span>$frstLetter</span>"; // affiche la première lettre
            // Début de la liste non ordonnée ici sinon ça crée un espace pas très esthétique 
            echo "<ul>"; 
        } 

        // Parcours du tableau et affichage des éléments dans la liste
        foreach ($pages as $page) { // pour chaque élément du tableau $pages on affiche un lien vers la page correspondante
            
            if (((isset($_SESSION['user']) && ($autorisation[$page] == 'admin' && ($user['admin']) == 1)) || $autorisation[$page] == 'all') && $type[$page] == 'Dimensions') { // si l'utilisateur est (connecté et qu'il est admin ou que la page est public) et que le type de la page est Dimensions
                // si la première lettre de l'élément est différente de la première lettre du premier élément du tableau $pages on ferme la liste et on en ouvre une nouvelle
                // ça permet de regrouper les éléments par première lettre
                if(mb_substr($page, 0, 1) != $frstLetter) { 
                    echo "</ul>"; // even if the list isn't open, the closing tag won't cause any problem
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
                

<?php
require "./blueprints/gl_ap_end.php";
?>