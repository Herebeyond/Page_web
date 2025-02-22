<?php
require "./blueprints/page_init.php";
require "./blueprints/gl_ap_start.php";
?>


<div id="textePrincipalList"> <!-- Div de droite -->
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

<?php
require "./blueprints/gl_ap_end.php";
?>