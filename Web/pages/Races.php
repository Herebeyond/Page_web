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
            <?php include "./scriptes/functions.php"; ?>
            <div id=englobe>
                <div class=texteGauche> <!-- Div de gauche -->
                    <div id=enTeteTexteGauche>
                        <?php
                            for($i=0; $i<4; $i++) {
                                echo "<div><img src='../images/Icon.png'></div>";
                            }?> <!-- permet de créer 3 images identiques -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>
                <div class="textePrincipal"> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <?php
                        try {
                            // Récupération des données du tableau races
                            $query = $pdo->query("SELECT * FROM races ORDER BY correspondance;");

                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                $imgPath = isset($row['icon_race']) ? $row['icon_race'] : null; // vérifie si l'image existe
                                if ($imgPath == null || $imgPath == '') { // si l'image n'existe pas ou est vide, on met une image par défaut
                                    $imgPath = '../images/icon_default.png'; // chemin de l'image par défaut
                                } else { // si l'image existe
                                    $imgPath = str_replace(" ", "_", "$imgPath"); // remplace les espaces par des _ pour les noms de fichiers
                                    $imgPath = "../images/" . htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8') . "." . $row["icon_type_race"]; // chemin de l'image, le htmlspecialchars permet d'échapper les caractères spéciaux dans la chaîne de caractères (tel que les ' et ") et ainsi les empêche de fermer des chaines de caractères
                                } 
                                
                                if (!isImageLinkValid($imgPath)) { // si l'image n'est pas valide
                                    $imgPath = '../images/icon_invalide.png'; // chemin de l'image invalide
                                }

                                // Création d'une div pour chaque race
                                $nomRace = $row["nom_race"];
                                $nomSpecie = $row["correspondance"];
                                $idrace = $row["id_race"];
                                echo " 
                                    <div class='selectionAccueil'>
                                        <div id='$idrace' class='classImgSelectionAccueil' onclick=\"window.location.href='./Affichage_specie.php?race=" . urlencode(str_replace(" ", "_", $nomRace)) . "&specie=" . urlencode(str_replace(" ", "_", $nomSpecie)) . "'\">
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