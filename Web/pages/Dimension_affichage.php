<?php
require "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page
include "./blueprints/gl_ap_start.php";
?>

                
<div id="textePrincipal" style="opacity: 100%;"> <!-- Div de droite -->
    <div>
        <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
        <img src="../images/map/dimensions.png" alt="Dimensions de l'univers" style="width: 100%; height: auto;">
    </div>
</div>
                

<?php
include "./blueprints/gl_ap_end.php";
?>