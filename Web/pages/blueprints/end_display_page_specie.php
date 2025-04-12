
                        
    </div> <!-- End of mainTextList -->

    <!-- Display pagination links -->
    <div id="pagination">
        <?php if ($page > 1): ?> <!-- If page is greater than 1, display the first page link -->
            <a href="?page=1">&lt;&lt;</a>
        <?php endif; ?>

        <?php if ($page > 1): ?> <!-- If page is greater than 1, display the previous link -->
            <a href="?page=<?php echo $page - 1; ?>">&lt;</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?> <!-- Display the page numbers -->
            <a href="?page=<?php echo $i; ?>"<?php if ($i == $page) {echo ' class="active"';} ?>><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?> <!-- If page is less than the total pages, display the next link -->
            <a href="?page=<?php echo $page + 1; ?>">&gt;</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?> <!-- If page is less than the total pages, display the last page link -->
            <a href="?page=<?php echo $totalPages; ?>">&gt;&gt;</a>
        <?php endif; ?>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.race-item').forEach(function (raceItem) {
            raceItem.addEventListener('click', function (event) {
                event.stopPropagation(); // Empêche les événements parent

                // Vérifiez si la race a une flèche (donc des personnages)
                const hasArrow = this.querySelector('.small-icon-list');
                if (!hasArrow) {
                    return; // Ignorez les races sans flèche
                }

                const raceId = this.getAttribute('data-race-id');
                const charactersList = document.getElementById(`characters-${raceId}`);

                // Toggle visibility
                if (charactersList.style.display === 'none') {
                    // Fetch characters via AJAX
                    fetch(`./scriptes/fetch_characters.php?race_id=${raceId}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log(data); // Affiche la réponse dans la console
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
                            console.error('Fetch error:', error); // Affiche l'erreur dans la console
                        });
                } else {
                    charactersList.style.display = 'none';
                }
            });
        });
    });
</script>


<?php
require_once "./blueprints/gl_ap_end.php";
?>