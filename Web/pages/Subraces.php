<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?>
        <link rel="stylesheet" href= "<?php echo $chemin_absolu . "style/PageStyle.css?ver=" . time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- <link rel="stylesheet" href="../style/styleScript.css?ver=<?php // echo time(); ?>"> le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <?php include "./scriptes/pages_generator.php" ?>
        <?php //include "./scriptes/pages_factions_generator.php" ?>
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
                                echo "<div><img src=" . $chemin_absolu . "images/Icon.png></div>";
                            }?> <!-- permet de créer 3 images identiques -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>
                



                <div class="textePrincipal"> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <?php
                        // Informations de connexion à la base de données MySQL
                        $host = 'db';
                        $dbname = 'univers';
                        $username = 'root';
                        $password = 'root_password';

                        try {
                            // Connexion à la base de données MySQL
                            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            // Récupération des données du tableau subraces
                            $query = $pdo->query("SELECT * FROM subraces ORDER BY id_subrace;");

                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                $imgPath = isset($row['icon_subrace']) ? $row['icon_subrace'] : null; // vérifie si l'image existe
                                if ($imgPath == null || $imgPath == '') { // si l'image n'existe pas ou est vide, on met une image par défaut
                                    $imgPath = $chemin_absolu . 'images/icon_default.png'; // chemin de l'image par défaut
                                } else { // si l'image existe
                                    $imgPath = str_replace(" ", "_", "$imgPath"); // remplace les espaces par des _ pour les noms de fichiers
                                    $imgPath = $chemin_absolu . "images/" . htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') . "." . $row["icon_type_subrace"]; // chemin de l'image, le htmlspecialchars permet d'échapper les caractères spéciaux dans la chaîne de caractères (tel que les ' et ") et ainsi les empêche de fermer des chaines de caractères
                                } 
                                
                                if (!isImageLinkValid($imgPath)) { // si l'image n'est pas valide
                                    $imgPath = $chemin_absolu . 'images/icon_invalide.png'; // chemin de l'image invalide
                                }

                                // Création d'une div pour chaque subrace
                                $nomRace = $row["nom_subrace"];
                                $idSubrace = "subrace-" . $row["id_subrace"];
                                echo " 
                                    <div class='selectionAccueil'>
                                        <div id='$idSubrace' class='classImgSelectionAccueil' onclick=\"window.location.href='Races/" . $row["correspondance"] . ".php?subrace=" . urlencode($idSubrace) . "'\">
                                            <img class='imgSelectionAccueil' src='" . $imgPath . "'>
                                            <span>" . $nomRace . "</span>
                                            <span> [" . $row["correspondance"] . "] </span>
                                        </div>
                                    </div>
                                ";
                            }
                        } catch (PDOException $e) {
                            // Gestion des erreurs
                            echo "Erreur d'insertion : " . $e->getMessage();
                        }
                    ?>
                </div>

            </div>

        </div>

    </body>
</html>