<?php
require "./blueprints/page_init.php";
require "./blueprints/gl_ap_start.php";

// Number of races per page
$perPage = 8;

// Calculate the total number of pages
$totalRacesQuery = $pdo->prepare("SELECT COUNT(*) FROM races");
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
    <a id=Return onclick='window.history.back()'> Return </a><br>
    <div id="mainTextList">
        <?php
            try {
                // Retrieval of data from table races with pagination
                $query = $pdo->prepare("SELECT * FROM races ORDER BY correspondence LIMIT :limit OFFSET :offset");
                $query->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $query->bindValue(':offset', $offset, PDO::PARAM_INT);
                $query->execute();

                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $imgPath = isset($row['icon_race']) ? $row['icon_race'] : null; // check if the image exists
                    if ($imgPath == null || $imgPath == '') { // if the image doesn't exist or is empty, use a default image
                        $imgPath = '../images/icon_default.png'; // path to the default image
                    } else { // if the image exists
                        $imgPath = str_replace(" ", "_", "$imgPath"); // replace spaces with underscores for file names
                        $imgPath = "../images/" . sanitize_output($imgPath); // path to the image, sanitize_output escapes special characters in the string (such as ' and ") and prevents them from closing strings
                    } 
                    
                    if (!isImageLinkValid($imgPath)) { // if the image is not valid
                        $imgPath = '../images/icon_invalide.png'; // path to the invalid image
                    }

                    // Create a div for each race
                    $raceName = sanitize_output($row["race_name"]);
                    $specieName = sanitize_output($row["correspondence"]);
                    $idrace = sanitize_output($row["id_race"]);
                    echo " 
                        <div class='selection'>
                            <div id='$idrace' class='classImgSelection' onclick=\"window.location.href='./Affichage_specie.php?race=" . urlencode(str_replace(" ", "_", $raceName)) . "&specie=" . urlencode(str_replace(" ", "_", $specieName)) . "'\">
                                <img class='imgSelection' src='" . $imgPath . "'>
                                <span>" . $raceName . "</span>
                                <span> [" . $specieName . "] </span>
                            </div>
                        </div>
                    ";
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