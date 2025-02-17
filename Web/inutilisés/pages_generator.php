<?php
include "./scriptes/functions.php";
// Informations de connexion à la base de données MySQL
$host = 'db'; // Le nom du service MySQL dans docker-compose.yml
$dbname = 'univers'; // Le nom de la base de données
$username = 'root'; // Le nom d'utilisateur
$password = 'root_password'; // Le mot de passe

try {
    // Connexion à la base de données MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupération des données
    $query = $pdo->query("SELECT * FROM Species");

    // Vérifier si la requête a retourné des résultats
    if ($query) {
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // Nom de la Specie pour le nom du fichier
            $SpecieName = $row['nom_specie'];
            $filePath = "./species/" . $SpecieName . ".php";

            // Contenu du fichier PHP
            $content = "<?php
session_start();
require '../../login/db.php'; // Connexion à la base
include '../scriptes/autorisation.php'; // inclut le fichier autorisation.php
?>

<html>
    <head>
        <?php \$chemin_absolu = 'http://localhost/test/Web/';?> <!-- défini le chemin absolu commun à tous les chemins, le reste du chemin sera écrit pour chaque cas -->
        <link rel='stylesheet' href='<?php echo \$chemin_absolu . '/style/PageStyle.css?ver=' . time()?>' > <!-- le ?ver=' . time() permet de générer une version différente à chaque fois et ainsi recharger le css à chaque fois que la page est rechargée car il ignore le cache -->
        <!-- <link rel='stylesheet' href='../style/styleScript.css?ver=<?php // echo time(); ?>'> -->
        <title>
            $SpecieName
        </title>
    </head>
    
    <body>
        <div id='global'>

            <?php include \"../blueprints/header.php\" ?>
            <?php include \"../scriptes/functions.php\"; ?>

            <div id='englobe'>
                <div class=texteGauche> <!-- Div de gauche -->
                    <div id=enTeteTexteGauche>
                        <?php
                            for(\$i=0; \$i<4; \$i++) {
                                echo '<div><img src=' . \$chemin_absolu . 'images/Icon.png></div>';
                            }?> <!-- permet de créer 4 images identiques et espacées correctement -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents(\"\" . \$chemin_absolu . \"texte/mondes_oubliés.txt\"))) . '</span>';
                    ?>
                </div>

                <div class='textePrincipal'> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <p class='Titre'> $SpecieName </p>
                    <?php
                        \$host = 'db';
                        \$dbname = 'univers';
                        \$username = 'root';
                        \$password = 'root_password';
                        try {
                            // Connexion à la base de données MySQL
                            \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8\", \$username, \$password);
                            \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


                            // Récupération des données du tableau races
                            \$nom_specie = '$SpecieName';
                            \$queryF = \$pdo->query(\"SELECT * FROM races as r LEFT JOIN Species as s ON r.correspondance = s.nom_specie WHERE nom_specie = '\$nom_specie' ORDER BY r.id_race;\");
                            \$queryR = \$pdo->query(\"SELECT * FROM Species WHERE nom_specie = '\$nom_specie';\");
                            

                            


                            // génère le texte principal de la page
                            \$rowR = \$queryR->fetch(PDO::FETCH_ASSOC); 
                            if (\$rowR) { // le if au lieu de while permet de récupérer une seule ligne
                                echo \"<span>\" . \$rowR['content_specie'] . \"</span>\"; // reconverti le la variable en UTF-8 pour l'afficher correctement en html
                            } else {
                                echo \"Aucune donnée trouvée pour la Specie '\$Specie'.\";
                            }

                            echo '<br><br>';
                           

                            // génère les divs éventuelles pour chaque races
                            \$divsSelec = '';
                            while (\$rowF = \$queryF->fetch(PDO::FETCH_ASSOC)) {
                                \$imgPath = isset(\$rowF['icon_race']) ? \$rowF['icon_race'] : null; // vérifie si l'image existe
                                if (\$imgPath == null || \$imgPath == '') { // si l'image n'existe pas ou est vide, on met une image par défaut
                                    \$imgPath = \$chemin_absolu . 'images/icon_default.png'; // chemin de l'image par défaut
                                } else { // si l'image existe, on la met
                                    \$imgPath = str_replace(\" \", \"_\", \"\$imgPath\"); // remplace les espaces par des _ pour les noms de fichiers
                                    \$imgPath = \$chemin_absolu . \"images/\" . htmlspecialchars(\$imgPath, ENT_QUOTES, 'UTF-8') . \".\" . \$rowF['icon_type_race']; // chemin de l'image, le htmlspecialchars permet d'échapper les caractères spéciaux dans la chaîne de caractères (tel que les ' et \") et ainsi les empêche de fermer des chaines de caractères
                                }







                                \$lifespan = \$rowF['lifespan'];
                                if (\$lifespan == null || \$lifespan == '') {
                                    \$lifespan = 'Not specified';
                                } elseif (\$lifespan == 'Immortel') {
                                    \$lifespan = 'Immortel'; // si la durée de vie est 'Immortel', on la laisse telle quelle
                                } else {
                                    \$lifespan = \$lifespan . ' years'; // ajoute 'years' à la fin de la durée de vie
                                }

                                \$homeworld = \$rowF['homeworld'];
                                if (\$homeworld == null || \$homeworld == '') {
                                    \$homeworld = 'Not specified';
                                }

                                \$country = \$rowF['country'];
                                if (\$country == null || \$country == '') {
                                    \$country = 'Not specified';
                                }

                                \$habitat = \$rowF['habitat'];
                                if (\$habitat == null || \$habitat == '') {
                                    \$habitat = 'Not specified';
                                }



                                // Création d'une div pour chaque race
                                \$divsSelec .= \" 
                                        <div class='selection'>
                                            <div class=infobox>
                                                <div class='classImgSelection'>
                                                    <img class='imgSelection' src='\" . \$imgPath . \"'>
                                                    \" . \$rowF['nom_race'] . \"
                                                </div>
                                                <div class=infos>
                                                    <div>
                                                        <p class=infosP> Espérance de vie : </p>
                                                        <p class=infosT>\" . \$lifespan . \"</p>
                                                    </div>
                                                    <div>
                                                        <p class=infosP> Plan de résidence : </p>
                                                        <p class=infosT>\" . \$homeworld . \"</p>
                                                    </div>
                                                    <div>
                                                        <p class=infosP> Pays de résidence : </p>
                                                        <p class=infosT>\" . \$country . \"</p>
                                                    </div>
                                                    <div>
                                                        <p class=infosP> Habitat : </p>
                                                        <p class=infosT>\" . \$habitat . \"</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='texteSelection'>
                                                <p>\" . \$rowF['content_race'] . \"</p>
                                            </div>
                                        </div>
                                \";
                                
                                \$rowF['nom_race'];
                            }
                            
                        } catch (PDOException \$e) {
                            \$divsSelec = \"Erreur de connexion : \" . \$e->getMessage();
                        }
                        
                        echo '<div class=sous_section>';
                        echo \$divsSelec;
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
            
            ";

            // Créez le fichier PHP avec le contenu généré
            file_put_contents($filePath, $content);
        }
    } else {
        echo "Aucune donnée trouvée dans la base.";
    }
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
