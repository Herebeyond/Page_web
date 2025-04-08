<?php
require "./blueprints/page_init.php";
require "./blueprints/gl_ap_start.php";

// Number of species per page
$perPage = 8;

// Calculate the total number of pages
$totalSpeciesQuery = $pdo->prepare("SELECT COUNT(DISTINCT s.id_specie) FROM species s JOIN races r ON s.id_specie = r.correspondence WHERE race_is_unique = 1");
$totalSpeciesQuery->execute();
$totalSpecies = $totalSpeciesQuery->fetchColumn();
$totalPages = ceil($totalSpecies / $perPage);

// Get the current page from the URL, default is page 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

// Calculate the offset for the SQL query
$offset = ($page - 1) * $perPage;
?>

<div id="mainRacesText"> <!-- Right div -->
    <a id="Return" onclick="window.history.back()">Return</a><br><br>
    <div id="mainRacesList">
        <?php
            try {
                // Retrieval of data from table races with pagination
                $queryS = $pdo->prepare("SELECT DISTINCT s.* FROM species s JOIN races r ON s.id_specie = r.correspondence WHERE race_is_unique = 1 LIMIT :offset, :perPage");
                $queryS->bindValue(':offset', $offset, PDO::PARAM_INT);
                $queryS->bindValue(':perPage', $perPage, PDO::PARAM_INT);
                $queryS->execute();

                while ($rowS = $queryS->fetch(PDO::FETCH_ASSOC)) {
                    echo "
                        <div class='selection'>
                            <div id='" . sanitize_output($rowS["id_specie"]) . "' class='classSpecieSelection' onclick=\"window.location.href='./Character_display_species.php?specie=" . urlencode(str_replace(" ", "_", $rowS["specie_name"])) . "'\">
                                <span>" . sanitize_output($rowS["specie_name"]) . "</span>
                            </div>
                            <ul class='listRaces'>
                    ";
                    $queryR = $pdo->prepare("SELECT * FROM races WHERE correspondence = ? AND race_is_unique = 1");
                    $queryR->execute([$rowS["specie_name"]]);

                    while ($rowR = $queryR->fetch(PDO::FETCH_ASSOC)) {
                        echo "
                                <li>
                                    <div id='" . sanitize_output($rowS["id_specie"]) . "' class='classRaceSelection' onclick=\"window.location.href='./Character_display_species.php?specie=" . urlencode(str_replace(" ", "_", $rowS["specie_name"])) . "&race=" . urlencode(str_replace(" ", "_", $rowR["race_name"])) . "'\">
                                        <span>" . sanitize_output($rowR["race_name"]) . "</span>
                                    </div>
                                </li>
                        ";
                    }
                    echo "
                            </ul>
                        </div>
                    ";
                }
            } catch (PDOException $e) {
                echo "Insertion error: " . sanitize_output($e->getMessage());
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

<?php
require "./blueprints/gl_ap_end.php";
?>