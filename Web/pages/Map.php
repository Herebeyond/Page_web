<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php";
?>
                
<div id="mainText" style="opacity: 100%;"> 
    <?php  
    /// DISPLAY THE MAP OF THE FORGOTTEN WORLDS
    /// it should be able to take the doc3.pptx file and display the information inside but it doesn't work
    /// because it needs composer and composer doesn't want to install itself correctly
    /*
    echo realpath(__DIR__ . '/../../../../vendor/autoload.php'); 
    require_once dirname(__DIR__, 4) . '/vendor/autoload.php'; /// CANNOT FIND THE FILE 

    use PhpOffice\PhpPresentation\IOFactory;
    use PhpOffice\PhpPresentation\PhpPresentation;
    use PhpOffice\PhpPresentation\Slide\Slide;
    use PhpOffice\PhpPresentation\Writer\PowerPoint2007;

    // Load the .pptx file
    $pptReader = IOFactory::createReader('PowerPoint2007');
    $presentation = $pptReader->load(__DIR__ . '/../../../../BDD/doc3.pptx');

    // Select the specific slide (for example, the second slide)
    $slideIndex = 0; // Indexes start at 0
    $slide = $presentation->getSlide($slideIndex);

    // Display the content of the slide
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
    } */ /// DOES NOT WORK
    ?>

    <a id=Return onclick='window.history.back()'> Return </a><br>
    <img src="../images/map/map_monde.png" alt="Map of the Forgotten Worlds" style="width: 100%; height: auto;">
</div>
                

<?php
require_once "./blueprints/gl_ap_end.php";
?>