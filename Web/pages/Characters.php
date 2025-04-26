<?php
require_once "./blueprints/start_display_page_specie.php";


try {
    $queryS = $pdo->prepare("SELECT * FROM species ORDER BY id_specie LIMIT :limit OFFSET :offset");
    $queryS->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $queryS->bindValue(':offset', $offset, PDO::PARAM_INT);
    $queryS->execute();

    while ($rowS = $queryS->fetch(PDO::FETCH_ASSOC)) {
        $specieName = sanitize_output($rowS["specie_name"]);
        $idspecie = sanitize_output($rowS["id_specie"]);
        echo "<div class='species-item'>";
        echo "<span id='specie-$idspecie' class='species-name'>" . $specieName . "</span>";

        $queryR = $pdo->prepare("SELECT * FROM races WHERE correspondence = ? ORDER BY id_race");
        $queryR->execute([$idspecie]);

        echo "<ul class='races-list'>"; // Vertical list for races
        while ($rowR = $queryR->fetch(PDO::FETCH_ASSOC)) {
            $raceName = sanitize_output($rowR["race_name"]);
            $idrace = sanitize_output($rowR["id_race"]);
        
            // Vérifiez si la race a des personnages
            $queryC = $pdo->prepare("SELECT COUNT(*) FROM characters WHERE correspondence = ?");
            $queryC->execute([$idrace]);
            $hasCharacters = $queryC->fetchColumn() > 0;
        
            echo "<li class='race-item' data-race-id='$idrace'>";
            echo "<span id='race-$idrace' class='race-name'>" . $raceName . "</span>";
        
            // Ajoutez une flèche si la race a des personnages
            if ($hasCharacters) {
                echo "<img class='small-icon-list' src='../images/small_img/fleche-deroulante.png'>";
            }
        
            echo "<ul class='characters-list' id='characters-$idrace' style='display: none;'></ul>"; // Placeholder for characters
            echo "</li>";
        }
        echo "</ul>"; // End of races list
        echo "</div>"; // End of species item
    }
} catch (PDOException $e) {
    echo "Insertion error: " . sanitize_output($e->getMessage());
}

require_once "./blueprints/end_display_page_specie.php";
?>

<script>
    // Function to display the list of characters when clicking on a race
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.race-item').forEach(function (raceItem) {
            raceItem.addEventListener('click', function (event) {
                event.stopPropagation(); // Prevent click event to propagate to parent elements

                // Check if the clicked element has a child with class 'small-icon-list'
                const hasArrow = this.querySelector('.small-icon-list');
                if (!hasArrow) {
                    return; // Prevent action if no arrow is present
                }

                const raceId = this.getAttribute('data-race-id');
                const charactersList = document.getElementById(`characters-${raceId}`);

                // Toggle visibility
                if (charactersList.style.display === 'none') {
                    // Fetch characters via AJAX
                    fetch(`./scriptes/fetch_characters.php?race_id=${raceId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                charactersList.innerHTML = `<li>${data.error}</li>`;
                            } else {
                                charactersList.innerHTML = data.map(character => `
                                    <div class="character-item" onclick="window.location.href='./Character_display.php?character_id=${character.id_character}&race=${character.race_name}'">
                                        <span>${character.character_name}</span>
                                    </div>
                                `).join('');
                            }
                            charactersList.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Fetch error:', error); // Handle fetch error
                        });
                } else {
                    charactersList.style.display = 'none';
                }
            });
        });
    });
</script>
