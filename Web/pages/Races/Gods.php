
<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?> <!-- défini le chemin absolu commun à tous les chemins, le reste du chemin sera écrit pour chaque cas -->
        <link rel='stylesheet' href='<?php echo $chemin_absolu . '/style/PageStyle.css?ver=' . time()?>' > <!-- le ?ver=' . time() permet de générer une version différente à chaque fois et ainsi recharger le css à chaque fois que la page est rechargée car il ignore le cache -->
        <!-- <link rel='stylesheet' href='../style/styleScript.css?ver=<?php // echo time(); ?>'> -->
        <title>
            Gods
        </title>
    </head>
    
    <body>
        <div id='global'>

            <?php include "../blueprints/header.php" ?>
            <?php include "../scriptes/functions.php"; ?>

            <div id='englobe'>
                <div class=texteGauche> <!-- Div de gauche -->
                    <div id=enTeteTexteGauche>
                        <?php
                            for($i=0; $i<4; $i++) {
                                echo '<div><img src=' . $chemin_absolu . 'images/Icon.png></div>';
                            }?> <!-- permet de créer 4 images identiques et espacées correctement -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("" . $chemin_absolu . "texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>

                <div class='textePrincipal'> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <p class='Titre'> Gods </p>
                    <?php
                        $host = 'localhost';
                        $dbname = 'univers';
                        $username = 'root';
                        $password = '';
                        try {
                            // Connexion à la base de données MySQL
                            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


                            // Récupération des données du tableau subraces
                            $nom_race = 'Gods';
                            $queryF = $pdo->query("SELECT * FROM subraces as f LEFT JOIN Races as r ON f.correspondance = r.nom_race WHERE nom_race = '$nom_race' ORDER BY f.id_subrace;");
                            $queryR = $pdo->query("SELECT * FROM Races WHERE nom_race = '$nom_race';");
                            

                            


                            // génère le texte principal de la page
                            $rowR = $queryR->fetch(PDO::FETCH_ASSOC); 
                            if ($rowR) { // le if au lieu de while permet de récupérer une seule ligne
                                echo "<span>" . $rowR['content_race'] . "</span>"; // reconverti le la variable en UTF-8 pour l'afficher correctement en html
                            } else {
                                echo "Aucune donnée trouvée pour la race '$race'.";
                            }

                            echo '<br><br>';
                           

                            // génère les divs éventuelles pour chaque subraces
                            $divsSelec = '';
                            while ($rowF = $queryF->fetch(PDO::FETCH_ASSOC)) {
                                $imgPath = isset($rowF['icon_subrace']) ? $rowF['icon_subrace'] : null; // vérifie si l'image existe
                                if ($imgPath == null || $imgPath == '') { // si l'image n'existe pas ou est vide, on met une image par défaut
                                    $imgPath = $chemin_absolu . 'images/icon_default.png'; // chemin de l'image par défaut
                                } else { // si l'image existe, on la met
                                    $imgPath = $imgPath; // converti le nom de la subrace en UTF-8 pour l'afficher correctement en html
                                    $imgPath = str_replace(" ", "_", "$imgPath"); // remplace les espaces par des _ pour les noms de fichiers
                                    $imgPath = $chemin_absolu . "images/" . htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') . "." . $rowF['icon_type_subrace']; // chemin de l'image, le htmlspecialchars permet d'échapper les caractères spéciaux dans la chaîne de caractères (tel que les ' et ") et ainsi les empêche de fermer des chaines de caractères
                                }







                                $lifespan = $rowF['lifespan'];
                                if ($lifespan == null || $lifespan == '') {
                                    $lifespan = 'Not specified';
                                } elseif ($lifespan == 'Immortel') {
                                    $lifespan = 'Immortel'; // si la durée de vie est 'Immortel', on la laisse telle quelle
                                } else {
                                    $lifespan = $lifespan . ' years'; // ajoute 'years' à la fin de la durée de vie
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



                                // Création d'une div pour chaque subrace
                                $divsSelec .= " 
                                        <div class='selection'>
                                            <div class=infobox>
                                                <div class='classImgSelection'>
                                                    <img class='imgSelection' src='" . $imgPath . "'>
                                                    " . $rowF['nom_subrace'] . "
                                                </div>
                                                <div class=infos>
                                                    <div>
                                                        <p class=infosP> Espérance de vie : </p>
                                                        <p class=infosT>" . $lifespan . "</p>
                                                    </div>
                                                    <div>
                                                        <p class=infosP> Plan de résidence : </p>
                                                        <p class=infosT>" . $homeworld . "</p>
                                                    </div>
                                                    <div>
                                                        <p class=infosP> Pays de résidence : </p>
                                                        <p class=infosT>" . $country . "</p>
                                                    </div>
                                                    <div>
                                                        <p class=infosP> Habitat : </p>
                                                        <p class=infosT>" . $habitat . "</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='texteSelection'>
                                                <span>" . $rowF['content_subrace'] . "</span>
                                            </div>
                                        </div>
                                ";
                                
                                $rowF['nom_subrace'];
                            }
                            
                        } catch (PDOException $e) {
                            $divsSelec = "Erreur de connexion : " . $e->getMessage();
                        }
                        
                        echo '<div class=sous_section>';
                        echo $divsSelec;
                        echo '</div>';
                        ?>
                </div>
            </div>
        </div>

        <!-- <script> // compte le nombre de div 'selection' pour ajuster la taille de 'textePincipal' car le 'flex-wrap: wrap;' dedans peu espacer un peu trop verticalement les divs
            // JavaScript pour compter le nombre d'éléments avec une classe spécifique
            const count = document.querySelectorAll('.selection').length;

            // Utilisez fetch pour envoyer le résultat à un fichier PHP pour traitement
            fetch('../../style/styleControl.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'count=' + count
            })
            .then(response => response.text())
        </script> 

        <script> // recharge la page après un cours délai pour bien intégrer la taille des divs calculé précédemment
            document.addEventListener('DOMContentLoaded', function () {
            // Vérifie si un identifiant unique est déjà enregistré
            if (!sessionStorage.getItem('pageReloaded')) {
                sessionStorage.setItem('pageReloaded', 'true'); // Marque comme rechargée
                setTimeout(function() {
                        location.reload();
                }, 50); // le délai est de 0.05 secondes
            } else 
                sessionStorage.removeItem('pageReloaded'); // Réinitialise pour les prochaines visites
            });
        </script> -->

    </body>
</html>
            
            