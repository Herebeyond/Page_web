<?php
require_once "./blueprints/page_init.php";
require_once "./blueprints/gl_ap_start.php";

// Number of species per page
$perPage = 8;

// Calculate the total number of pages
$totalSpeciesQuery = $pdo->prepare("SELECT COUNT(*) FROM species");
$totalSpeciesQuery->execute();
$totalSpecies = $totalSpeciesQuery->fetchColumn(); // fetch the total number of species
$totalPages = ceil($totalSpecies / $perPage); // calculate the total number of pages

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

<div id="mainText"> <!-- Right div -->
    <button id="Return" onclick="window.history.back()">Return</button><br>
    <div id="mainTextList">
        <?php
            try {
                // Retrieval of data from table species with pagination
                $query = $pdo->prepare("SELECT * FROM species ORDER BY id_specie LIMIT :limit OFFSET :offset");
                $query->bindValue(':limit', $perPage, PDO::PARAM_INT); // bind the limit value
                $query->bindValue(':offset', $offset, PDO::PARAM_INT); // bind the offset value
                $query->execute();

                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $imgPath = isset($row['icon_specie']) ? $row['icon_specie'] : null; // check if the image exists
                    if ($imgPath == null || $imgPath == '') { // if the image doesn't exist or is empty, use a default image
                        $imgPath = '../images/icon_default.png'; // path to the default image
                    } else { // if the image exists and is valid
                        $imgPath = str_replace(" ", "_", "$imgPath"); // replace spaces with underscores for file names
                        $imgPath = "../images/" . sanitize_output($imgPath); // path to the image, sanitize_output escapes special characters in the string (such as ' and ") and prevents them from closing strings
                    } 
                    
                    if (!isImageLinkValid($imgPath)) { // if the image is not valid
                        $imgPath = '../images/icon_invalide.png'; // path to the invalid image
                    }
        
                    // Create a div for each species
                    $specieName = sanitize_output($row["specie_name"]);
                    echo " 
                        <div class='selection'>
                            <div class='classImgSelection'>
                                <img class='imgSelection' src='" . $imgPath . "' onclick=\"window.location.href='./Beings_display.php?specie=" . urlencode(str_replace(" ", "_", $specieName)) . "'\">
                                " . $specieName . "
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

<?php require_once "./blueprints/gl_ap_end.php"; ?>
