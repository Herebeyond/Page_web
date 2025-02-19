<?php
session_start();
require '../login/db.php'; // Connexion à la base
include './scriptes/autorisation.php'; // inclut le fichier autorisation.php


if (isset($_GET['specie'])) {
    // Récupère les valeurs des paramètres 'race' et 'specie' et les nettoie
    $specie = htmlspecialchars(trim($_GET['specie']), ENT_QUOTES, 'UTF-8');

    // Vous pouvez maintenant utiliser les variables $race et $specie pour effectuer des opérations, comme des requêtes à la base de données
    // Exemple de requête à la base de données pour récupérer les informations de la race et de la specie
    try {

        if(isset($_GET['race'])) {
            $race = htmlspecialchars(trim($_GET['race']), ENT_QUOTES, 'UTF-8');

            try {
                // Prépare et exécute la requête pour récupérer les informations de la race
                $stmt = $pdo->prepare("SELECT * FROM races WHERE nom_race = ?");
                $stmt->execute([$race]);
                $raceInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                // Gestion des erreurs
                echo "Erreur de connexion : " . $e->getMessage();
            }
        }


        // Prépare et exécute la requête pour récupérer les informations de la specie
        $stmt = $pdo->prepare("SELECT * FROM species WHERE nom_specie = ?");
        $stmt->execute([$specie]);
        $specieInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Gestion des erreurs
        echo "Erreur de connexion : " . $e->getMessage();
    }
}
?>

<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?> <!-- défini le chemin absolu commun à tous les chemins, le reste du chemin sera écrit pour chaque cas -->
        <link rel='stylesheet' href='<?php echo $chemin_absolu . '/style/PageStyle.css?ver=' . time()?>' > <!-- le ?ver=' . time() permet de générer une version différente à chaque fois et ainsi recharger le css à chaque fois que la page est rechargée car il ignore le cache -->
        <!-- <link rel='stylesheet' href='../style/styleScript.css?ver=<?php // echo time(); ?>'> -->
        <title>
            Angels
        </title>
    </head>
    
    <body>
        <div id='global'>

            <?php include "./blueprints/header.php"; ?>
            <?php include "./scriptes/functions.php"; ?>

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

                <div id='affichage_specie'> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <span class='Titre'> <?php echo $specie ?> </span> <!-- affiche le nom de la specie en entête -->
                    <?php
                        $host = 'db';
                        $dbname = 'univers';
                        $username = 'root';
                        $password = 'root_password';
                        try {
                            // Connexion à la base de données MySQL
                            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


                            // Récupération des données du tableau races
                            $queryF = $pdo->query("SELECT * FROM races as r LEFT JOIN species as s ON r.correspondance = s.nom_specie WHERE nom_specie = '$specie' ORDER BY r.id_race;");
                            $queryR = $pdo->query("SELECT * FROM species WHERE nom_specie = '$specie';");
                            

                            


                            // génère le texte principal de la page
                            $rowR = $queryR->fetch(PDO::FETCH_ASSOC); 
                            echo $queryR->fetch(PDO::FETCH_ASSOC);
                            if ($rowR) { // le if au lieu de while permet de récupérer une seule ligne
                                echo "<span>" . $rowR['content_specie'] . "</span>"; // reconverti le la variable en UTF-8 pour l'afficher correctement en html
                            } else {
                                echo "Aucune donnée trouvée pour la Specie '$specie'.";
                            }

                            echo '<br><br>';
                           

                            // génère les divs éventuelles pour chaque races
                            $divsSelec = '';
                            while ($rowF = $queryF->fetch(PDO::FETCH_ASSOC)) {
                                $imgPath = isset($rowF['icon_race']) ? $rowF['icon_race'] : null; // vérifie si l'image existe
                                if ($imgPath == null || $imgPath == '') { // si l'image n'existe pas ou est vide, on met une image par défaut
                                    $imgPath = $chemin_absolu . 'images/icon_default.png'; // chemin de l'image par défaut
                                } else { // si l'image existe, on la met
                                    $imgPath = str_replace(" ", "_", "$imgPath"); // remplace les espaces par des _ pour les noms de fichiers
                                    $imgPath = $chemin_absolu . "images/" . htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') . "." . $rowF['icon_type_race']; // chemin de l'image, le htmlspecialchars permet d'échapper les caractères spéciaux dans la chaîne de caractères (tel que les ' et ") et ainsi les empêche de fermer des chaines de caractères
                                }

                                if (!isImageLinkValid($imgPath)) { // si l'image n'est pas valide
                                    $imgPath = $chemin_absolu . 'images/icon_invalide.png'; // chemin de l'image invalide
                                }





                                $lifespan = $rowF['lifespan'];
                                if ($lifespan == null || $lifespan == '') {
                                    $lifespan = 'Not specified';
                                } elseif ($lifespan == 'Immortal' || $lifespan == 'immortal') {
                                    $lifespan = 'Immortal'; // si la durée de vie est 'Immortel', on la laisse telle quelle
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



                                // Création d'une div pour chaque race
                                $divsSelec .= " 
                                        <div class='selection' id=" . str_replace(" ", "_",$rowF['nom_race']) . ">
                                            <div class=infobox>
                                                <div class='classImgSelection'>
                                                    <img class='imgSelection' src='" . $imgPath . "'>
                                                    " . $rowF['nom_race'] . "
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
                                                <p>" . $rowF['content_race'] . "</p>
                                            </div>
                                        </div>
                                ";
                                
                                $rowF['nom_race'];
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
        <script>
            document.addEventListener("DOMContentLoaded", function() { // attend que le document soit chargé pour exécuter le script
                const urlParams = new URLSearchParams(window.location.search);
                const race = urlParams.get('race'); 
                if (race) { // si la variable race se trouve dans l'url
                    const raceElement = document.getElementById(race);
                    if (raceElement) { // cherche la div qui possède l'id
                        raceElement.scrollIntoView({ behavior: 'smooth' }); // scroll vers la div qui possède l'id de la race
                    }
                }
            });
        </script>
    </body>
</html>

