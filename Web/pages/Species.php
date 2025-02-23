<?php
require "./blueprints/page_init.php";
require "./blueprints/gl_ap_start.php";

// Number of species per page
$perPage = 8;

// Calculate the total number of pages
$totalSpeciesQuery = $pdo->query("SELECT COUNT(*) FROM species");
$totalSpecies = $totalSpeciesQuery->fetchColumn();
$totalPages = ceil($totalSpecies / $perPage);

// Get the current page from the URL, default is 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

// Calculate the offset for the SQL query
$offset = ($page - 1) * $perPage;
?>

<div id="textePrincipal"> <!-- Div de droite -->
    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
    <div id="textePrincipalList">
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

                // Retrieval of data from table species with pagination
                $query = $pdo->prepare("SELECT * FROM species ORDER BY id_specie LIMIT :limit OFFSET :offset");
                $query->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $query->bindValue(':offset', $offset, PDO::PARAM_INT);
                $query->execute();

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


    <!-- Display pagination links -->
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

<?php require "./blueprints/gl_ap_end.php"; ?>