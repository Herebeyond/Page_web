<?php
require "./blueprints/page_init.php";
require "./blueprints/gl_ap_start.php";

// Nombre de races par page
$perPage = 8;

// Calculer le nombre total de pages
$totalRacesQuery = $pdo->query("SELECT COUNT(*) FROM races");
$totalRaces = $totalRacesQuery->fetchColumn();
$totalPages = ceil($totalRaces / $perPage);

// Obtenir la page actuelle à partir de l'URL, par défaut 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

// Calculer l'offset pour la requête SQL
$offset = ($page - 1) * $perPage;
?>

<div id="textePrincipal"> <!-- Div de droite -->
    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
    <div id="textePrincipalList">
        <?php
            try {
                // Retrieval of data from table races with pagination
                $query = $pdo->prepare("SELECT * FROM races ORDER BY correspondance LIMIT :limit OFFSET :offset");
                $query->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $query->bindValue(':offset', $offset, PDO::PARAM_INT);
                $query->execute();

                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $imgPath = isset($row['icon_race']) ? $row['icon_race'] : null; // vérifie si l'image existe
                    if ($imgPath == null || $imgPath == '') { // si l'image n'existe pas ou est vide, on met une image par défaut
                        $imgPath = '../images/icon_default.png'; // chemin de l'image par défaut
                    } else { // si l'image existe
                        $imgPath = str_replace(" ", "_", "$imgPath"); // remplace les espaces par des _ pour les noms de fichiers
                        $imgPath = "../images/" . sanitize_output($imgPath) . "." . sanitize_output($row["icon_type_race"]); // chemin de l'image, le sanitize_output permet d'échapper les caractères spéciaux dans la chaîne de caractères (tel que les ' et ") et ainsi les empêche de fermer des chaines de caractères
                    } 
                    
                    if (!isImageLinkValid($imgPath)) { // si l'image n'est pas valide
                        $imgPath = '../images/icon_invalide.png'; // chemin de l'image invalide
                    }

                    // Création d'une div pour chaque race
                    $nomRace = sanitize_output($row["nom_race"]);
                    $nomSpecie = sanitize_output($row["correspondance"]);
                    $idrace = sanitize_output($row["id_race"]);
                    echo " 
                        <div class='selectionAccueil'>
                            <div id='$idrace' class='classImgSelectionAccueil' onclick=\"window.location.href='./Affichage_specie.php?race=" . urlencode(str_replace(" ", "_", $nomRace)) . "&specie=" . urlencode(str_replace(" ", "_", $nomSpecie)) . "'\">
                                <img class='imgSelectionAccueil' src='" . $imgPath . "'>
                                <span>" . $nomRace . "</span>
                                <span> [" . $nomSpecie . "] </span>
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


    <!-- Afficher les liens de pagination -->
    <div id="pagination">
        <?php if ($page > 1): ?> <!-- If page is greater than 1, display the first page link -->
            <a href="?page=1">&lt;&lt;</a>
        <?php endif; ?>

        <?php if ($page > 1): ?> <!-- If page is greater than 1, display the previous link -->
            <a href="?page=<?php echo $page - 1; ?>">&lt;</a> 
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?> <!-- Display the page numbers -->
            <a href="?page=<?php echo $i; ?>"<?php if ($i == $page) echo ' class="active"'; ?>><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?> <!-- If page is less than the total pages, display the next link -->
            <a href="?page=<?php echo $page + 1; ?>">&gt;</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?> <!-- If page is less than the total pages, display the last page link -->
            <a href="?page=<?php echo $totalPages; ?>">&gt;&gt;</a>
        <?php endif; ?>

    </div>


</div>

<?php
require "./blueprints/gl_ap_end.php";
?>