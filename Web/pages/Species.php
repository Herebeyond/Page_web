<?php
require "./blueprints/page_init.php";
require "./blueprints/gl_ap_start.php";
?>



<div id="textePrincipalList"> <!-- Div de droite -->
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

            // Récupération des données du tableau species
            $query = $pdo->query("SELECT * FROM species ORDER BY id_specie;");

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $imgPath = isset($row['icon_specie']) ? $row['icon_specie'] : null; // vérifie si l'image existe
                if ($imgPath == null || $imgPath == '') { // si l'image n'existe pas ou est vide, on met une image par défaut
                    $imgPath = '../images/icon_default.png'; // chemin de l'image par défaut
                } else { // si l'image existe et est valide
                    $imgPath = str_replace(" ", "_", "$imgPath"); // remplace les espaces par des _ pour les noms de fichiers
                    $imgPath = "../images/" . sanitize_output($imgPath); // chemin de l'image, le sanitize_output permet d'échapper les caractères spéciaux dans la chaîne de caractères (tel que les ' et ") et ainsi les empêche de fermer des chaines de caractères
                } 
                
                if (!isImageLinkValid($imgPath)) { // si l'image n'est pas valide
                    $imgPath = '../images/icon_invalide.png'; // chemin de l'image invalide
                }
        
                // Création d'une div pour chaque species
                $nomSpecie = sanitize_output($row["nom_specie"]);
                echo " 
                    <div class='selectionAccueil'>
                        <div class='classImgSelectionAccueil'>
                            <img class='imgSelectionAccueil' src='" . $imgPath . "' onclick=\"window.location.href='./Affichage_specie.php?specie=" . urlencode(str_replace(" ", "_", $nomSpecie)) . "'\">
                            " . $nomSpecie . "
                        </div>
                    </div>
                ";
            }

        } catch (PDOException $e) {
            // Gestion des erreurs
            echo "Erreur d'insertion : " . sanitize_output($e->getMessage());
        }
    ?>
</div>

<?php require "./blueprints/gl_ap_end.php"; ?>