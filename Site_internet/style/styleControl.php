
<?php
    // Vérifiez si le nombre d'éléments a été envoyé
    if (isset($_POST['count'])) {
        $count = (int) $_POST['count']; // Récupérer le nombre d'éléments
        
        // Définir le contenu CSS dynamique basé sur ce nombre
        $cssContent = "
.textePrincipal {
    height: calc(140px + " . 270*ceil($count/4) . "px); /* hauteur des divs + */
}
        ";
        
        // Écrire le CSS dans un fichier style.css (ou tout autre fichier CSS)
        file_put_contents('styleScript.css', $cssContent);
    }
?>