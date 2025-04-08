<?php
require "./blueprints/page_init.php";
require "./blueprints/gl_ap_start.php";

// Number of races per page
$perPage = 8;

// Calculate the total number of pages
$totalRacesQuery = $pdo->prepare("SELECT COUNT(*) FROM species");
$totalRacesQuery->execute();
$totalRaces = $totalRacesQuery->fetchColumn();
$totalPages = ceil($totalRaces / $perPage);

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

<div id="mainText"> <!-- Right div -->
    <a id="Return" onclick='window.history.back()'> Return </a><br>
    <div id="mainTextList" class="species-list"> <!-- Horizontal list for species -->
        <?php
            try {
                // Retrieval of data from table species with pagination
                $queryS = $pdo->prepare("SELECT * FROM species ORDER BY id_specie LIMIT :limit OFFSET :offset");
                $queryS->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $queryS->bindValue(':offset', $offset, PDO::PARAM_INT);
                $queryS->execute();

                while ($rowS = $queryS->fetch(PDO::FETCH_ASSOC)) {
                    $specieName = sanitize_output($rowS["specie_name"]);
                    $idspecie = sanitize_output($rowS["id_specie"]);
                    echo "<div class='species-item' onclick=\"window.location.href='./Specie_display.php?specie=" . urlencode(str_replace(" ", "_", $specieName)) . "'\">";
                    echo "<span id='specie-$idspecie' class='species-name'>" . $specieName . "</span>";

                    // Retrieve data from table races for the current species
                    $queryR = $pdo->prepare("SELECT * FROM races WHERE correspondence = ? ORDER BY id_race");
                    $queryR->execute([$idspecie]);

                    echo "<ul class='races-list'>"; // Vertical list for races
                    while ($rowR = $queryR->fetch(PDO::FETCH_ASSOC)) {
                        $raceName = sanitize_output($rowR["race_name"]);
                        $idrace = sanitize_output($rowR["id_race"]);
                        echo "<li class='race-item' onclick=\"event.stopPropagation(); window.location.href='./Specie_display.php?specie=" . urlencode(str_replace(" ", "_", $specieName)) . "&race=" . urlencode(str_replace(" ", "_", $raceName)) . "'\">";
                        echo "<span id='race-$idrace' class='race-name'>" . $raceName . "</span>";
                        echo "</li>";
                    }
                    echo "</ul>"; // End of races list
                    echo "</div>"; // End of species item
                }
            } catch (PDOException $e) {
                // Error handling
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