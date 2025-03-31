<?php

function isImageLinkValid($url) {
    // Convert relative URL to absolute path
    $absolutePath = realpath(__DIR__ . '/../' . $url);

    // Check if the file exists and is an image
    if ($absolutePath && file_exists($absolutePath)) {
        $fileInfo = getimagesize($absolutePath);
        return $fileInfo !== false;
    }
    return false;
}

// Function to sanitize output
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}



?>
<script>

    function escapeHtml(text) { // Fonction pour échapper les caractères spéciaux en HTML dans fetchSpecieInfo et fetchRaceInfo
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function nl2br(str) { // Fonction pour remplacer les sauts de ligne par des balises <br> dans fetchSpecieInfo et fetchRaceInfo
        return str.replace(/\n/g, '<br>');
    }

    function fetchSpecieInfo() { // Fonction pour récupérer et afficher les informations de la Specie sélectionnée dans l'option du select dans la form grace au bouton Fetch Info
        var specieName = document.querySelector('select[name="SpecieName"]').value;
        if (specieName) {
            fetch('scriptes/fetch_specie_info.php?specie=' + encodeURIComponent(specieName))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        let iconHtml = data.icon ? `<p>Icon link: ${escapeHtml(data.icon)}</p><p>Icon: <img id="imgEdit" src="../images/${escapeHtml(data.icon)}" alt="Specie Icon"></p>` : '<p>Icon does not exist</p>';
                        let contentHtml = data.content ? `<p>Content: <br>${nl2br(escapeHtml(data.content))}</p>` : '<p>Content does not exist</p>';
                        let uniqueHtml = Boolean(data.unique) ? `<p>Is Unique</p>` : "<p>Isn't Unique</p>";
                        document.getElementById('specieInfo').innerHTML = iconHtml + uniqueHtml + contentHtml;
                    } else {
                        document.getElementById('specieInfo').innerHTML = '<p style="color:red;">' + escapeHtml(data.message) + '</p>';
                    }
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                    document.getElementById('specieInfo').innerHTML = '<p style="color:red;">Error fetching specie info</p>';
                });
        } else {
            document.getElementById('specieInfo').innerHTML = '<p style="color:red;">Please select a specie</p>';
        }
    }

    function fetchRaceInfo() { // Fonction pour récupérer et afficher les informations de la Race sélectionnée dans l'option du select dans la form grace au bouton Fetch Info
        var raceName = document.querySelector('select[name="Race_name"]').value;
        if (raceName) {
            fetch('scriptes/fetch_race_info.php?race=' + encodeURIComponent(raceName))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        let correspondenceHtml = data.correspondence ? `<p>correspondence: ${escapeHtml(data.correspondence)}</p>` : '<p>correspondence does not exist</p>';
                        let icon = data.icon ? data.icon.replace(/ /g, '_') : '';
                        let iconHtml = icon ? `<p>Icon link: ${escapeHtml(icon)}</p><p>Icon: <img id="imgEdit" src="../images/${escapeHtml(icon)}" alt="Race Icon"></p>` : '<p>Icon does not exist</p>';
                        let contentHtml = data.content ? `<p>Content: <br>${nl2br(escapeHtml(data.content))}</p>` : '<p>Content does not exist</p>';
                        let lifespanHtml = data.lifespan ? `<p>Lifespan: ${escapeHtml(data.lifespan)}</p>` : '<p>Lifespan does not exist</p>';
                        let homeworldHtml = data.homeworld ? `<p>Homeworld: ${escapeHtml(data.homeworld)}</p>` : '<p>Homeworld does not exist</p>';
                        let countryHtml = data.country ? `<p>Country: ${escapeHtml(data.country)}</p>` : '<p>Country does not exist</p>';
                        let habitatHtml = data.habitat ? `<p>Habitat: ${escapeHtml(data.habitat)}</p>` : '<p>Habitat does not exist</p>';
                        let uniqueHtml = data.unique ? `<p>Is Unique</p>` : "<p>Isn't Unique</p>";
                        document.getElementById('raceInfo').innerHTML = correspondenceHtml + iconHtml + lifespanHtml + homeworldHtml + countryHtml + habitatHtml + uniqueHtml + contentHtml; // permet l'affichage des infos
                    } else {
                        document.getElementById('raceInfo').innerHTML = '<p style="color:red;">' + escapeHtml(data.message) + '</p>';
                    }
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                    document.getElementById('raceInfo').innerHTML = '<p style="color:red;">Error fetching race info</p>';
                });
        } else {
            document.getElementById('raceInfo').innerHTML = '<p style="color:red;">Please select a race</p>';
        }
    }

    function confirmSubmit() { // Fonction pour confirmer ou annuler la soumission du formulaire
        return confirm("Are you sure you want to update it?");
    }

    function confirmSpecieDelete() { // Fonction pour confirmer ou annuler la suppression de la Specie
        if (confirm("Are you sure you want to delete the specie?")) {
            var specieName = document.querySelector('select[name="SpecieName"]').value;
            if (specieName) {
                fetch('scriptes/delete_specie.php?specie=' + encodeURIComponent(specieName))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Specie deleted successfully");
                            window.location.reload();
                        } else {
                            alert("Error deleting specie: " + data.message);
                        }
                    })
                    .catch(error => {
                        alert("Error deleting specie");
                    });
            } else {
                alert("Please select a specie");
            }
        }
    }

    function confirmRaceDelete() { // Fonction pour confirmer ou annuler la suppression de la Specie
        if (confirm("Are you sure you want to delete the race?")) {
            var raceName = document.querySelector('select[name="Race_name"]').value;
            if (raceName) {
                fetch('scriptes/delete_race.php?race=' + encodeURIComponent(raceName))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("race deleted successfully");
                            window.location.reload();
                        } else {
                            alert("Error deleting race: " + data.message);
                        }
                    })
                    .catch(error => {
                        alert("Error deleting race");
                    });
            } else {
                alert("Please select a race");
            }
        }
    }

    
</script>