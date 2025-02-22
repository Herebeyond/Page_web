<?php
require "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page
require "./blueprints/gl_ap_start.php";
?>
                
<div id="textePrincipal" style="opacity: 100%;"> 
    <?php  
    /// AFFICHAGE DE LA CARTE DES MONDES OUBLIES
    /// il devrait pouvoir prendre le fichier doc3.pptx et afficher les informations dedans mais ne fonctionne pas
    /// à cause de composer
    /*
    echo realpath(__DIR__ . '/../../../../vendor/autoload.php'); 
    require dirname(__DIR__, 4) . '/vendor/autoload.php'; /// N'ARRIVE PAS A TROUVER LE FICHIER 

    use PhpOffice\PhpPresentation\IOFactory;
    use PhpOffice\PhpPresentation\PhpPresentation;
    use PhpOffice\PhpPresentation\Slide\Slide;
    use PhpOffice\PhpPresentation\Writer\PowerPoint2007;

    // Charger le fichier .pptx
    $pptReader = IOFactory::createReader('PowerPoint2007');
    $presentation = $pptReader->load(__DIR__ . '/../../../../BDD/doc3.pptx');

    // Sélectionner la diapositive spécifique (par exemple, la deuxième diapositive)
    $slideIndex = 0; // Les index commencent à 0
    $slide = $presentation->getSlide($slideIndex);

    // Afficher le contenu de la diapositive
    foreach ($slide->getShapeCollection() as $shape) {
        if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
            foreach ($shape->getParagraphs() as $paragraph) {
                foreach ($paragraph->getRichTextElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpPresentation\Shape\RichText\TextElement) {
                        echo $element->getText() . '<br>';
                    }
                }
            }
        }
    } */ /// NE FONCTIONNE PAS
    ?>

    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
    <img src="../images/map/map_monde.png" alt="Carte des Mondes Oubliés" style="width: 100%; height: auto;">
</div>
                

<?php
require "./blueprints/gl_ap_end.php";
?>