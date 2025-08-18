<?php
require_once "./blueprints/start_display_page_specie.php";

try {
    // Retrieval of data from table species with pagination
    $queryS = $pdo->prepare("SELECT * FROM species ORDER BY id_specie LIMIT :limit OFFSET :offset");
    $queryS->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $queryS->bindValue(':offset', $offset, PDO::PARAM_INT);
    $queryS->execute();

    while ($rowS = $queryS->fetch(PDO::FETCH_ASSOC)) {
        $specieName = sanitize_output($rowS["specie_name"]);
        $idspecie = sanitize_output($rowS["id_specie"]);
        echo "<div class='species-item' onclick=\"window.location.href='./Races_display.php?specie=" . urlencode(str_replace(" ", "_", $specieName)) . "'\">";
        echo "<span id='specie-$idspecie' class='species-name'>" . $specieName . "</span>";

        // Retrieve data from table races for the current species
        $queryR = $pdo->prepare("SELECT * FROM races WHERE correspondence = ? ORDER BY id_race");
        $queryR->execute([$idspecie]);

        echo "<ul class='races-list'>"; // Vertical list for races
        while ($rowR = $queryR->fetch(PDO::FETCH_ASSOC)) {
            $raceName = sanitize_output($rowR["race_name"]);
            $idrace = sanitize_output($rowR["id_race"]);
            echo "<li class='race-item' onclick=\"event.stopPropagation(); window.location.href='./Races_display.php?specie=" . urlencode(str_replace(" ", "_", $specieName)) . "&race=" . urlencode(str_replace(" ", "_", $raceName)) . "'\">";
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


require_once "./blueprints/end_display_page_specie.php";
?>
