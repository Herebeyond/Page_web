<?php
include "./blueprints/page_init.php";
?>

<html>
    <head>
        <link rel="stylesheet" href="../style/PageStyle.css?ver=<?php echo time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <title>
            Page d'Accueil
        </title>
    </head>
    <body>
        <div id=global>

            <?php include "./blueprints/header.php" ?>
            
            <div id=englobe>
            
                <div class=texteGauche> <!-- Div de gauche -->
                    <div id=enTeteTexteGauche>
                        <?php
                            for($i=0; $i<4; $i++) {
                                echo "<div><img src='../images/Icon.png'></div>";
                            }?> <!-- permet de créer 4 images identiques comme décoration du texte de gauche-->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>

                
                <div class="textePrincipal" style="opacity: 100%;"> <!-<?php  
                        /// AFFICHAGE DE LA CARTE DES MONDES OUBLIES
                        /// il devrait pouvoir prendre le fichier doc3.pptx et afficher les informations dedans mais ne fonctionne pas
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
                </div>
                

            </div>

        </div>

    </body>
</html>