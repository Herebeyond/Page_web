<?php
include "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page
require '../login/db.php'; // Connexion à la base

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $Race_name = isset($_POST['Race_name']) ? trim($_POST['Race_name']) : ''; // Nettoyage des entrées utilisateur
    $Correspondance = isset($_POST['Correspondance']) ? trim($_POST['Correspondance']) : '';
    $Race_Icon = isset($_POST['icon_Race']) ? trim($_POST['icon_Race']) : '';
    $Icon_Type_Race = isset($_POST['icon_Type_Race']) ? trim($_POST['icon_Type_Race']) : '';
    $Race_content = isset($_POST['Race_text']) ? trim($_POST['Race_text']) : '';
    $Lifespan = isset($_POST['Lifespan']) ? trim($_POST['Lifespan']) : '';
    $Homeworld = isset($_POST['Homeworld']) ? trim($_POST['Homeworld']) : '';
    $Country = isset($_POST['Country']) ? trim($_POST['Country']) : '';
    $Habitat = isset($_POST['Habitat']) ? trim($_POST['Habitat']) : '';

    // Récupérer le nom de la Race depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM races WHERE nom_Race = ?"); 
    $stmt->execute([$Race_name]);
    $Race = $stmt->fetch();

    // Vérifier si la Race existe déjà dans la base, si non alors intégrer la Race à la base de données
    if ($Race) {
        $_SESSION['error'] = "Race already exists";
        header('Location: Race_add.php');
        exit;
    } else {
        // Insérer la nouvelle Race dans la base de données
        $stmt = $pdo->prepare("INSERT INTO races (nom_Race, correspondance, icon_Race, icon_Type_Race, content_Race, lifespan, homeworld, country, habitat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$Race_name, $Correspondance, $Race_Icon, $Icon_Type_Race, $Race_content, $Lifespan, $Homeworld, $Country, $Habitat]);
        $_SESSION['success'] = "Race added successfully";
        header('Location: Race_add.php');
        exit;
    }
}
?>

<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?>
        <link rel="stylesheet" href= "<?php echo $chemin_absolu . "style/PageStyle.css?ver=" . time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- <link rel="stylesheet" href="../style/styleScript.css?ver=<?php // echo time(); ?>"> le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
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
                                echo "<div><img src=" . $chemin_absolu . "images/Icon.png></div>";
                            }?> <!-- permet de créer 3 images identiques -->
                    </div> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier mondes_oubliés.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/mondes_oubliés.txt"))) . '</span>';
                    ?>
                </div>
                



                <div id='add_race' class="textePrincipal"> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <?php
                        if (isset($_SESSION['error'])) {
                            echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
                            unset($_SESSION['error']);
                        }
                        if (isset($_SESSION['success'])) {
                            echo '<p style="color:green;">' . htmlspecialchars($_SESSION['success']) . '</p>';
                            unset($_SESSION['success']);
                        }
                    ?>

                    <h2> Add a Race </h2><br>
                    <form method="POST" action="Race_add.php">
                        <label for="Race_name">Race Name</label>
                        <input type="text" name="Race_name" required><br>
                        <label for="Correspondance">Correspondance</label>
                        <select name="Correspondance" required> <!-- Liste déroulante pour sélectionner la correspondance de la race avec quel specie -->
                            <option value="">Select a specie</option>
                            <?php // Récupérer les noms des Specie depuis la base de données et les afficher dans une liste déroulante
                                $stmt = $pdo->query("SELECT * FROM species ORDER BY nom_specie;");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $row['nom_specie'] . '">' . $row['nom_specie'] . '</option>';
                                }
                            ?>
                        </select><br>
                        <label for="Race_icon">Race Icon</label>
                        <input type="text" name="icon_Race"><br>
                        <label for="Icon_Type_Race">Icon Type Race</label>
                        <input type="text" name="icon_Type_Race"><br>
                        <label for="Lifespan">Lifespan</label>
                        <input type="text" name="Lifespan"><br>
                        <label for="Homeworld">Homeworld</label>
                        <input type="text" name="Homeworld"><br>
                        <label for="Country">Country</label>
                        <input type="text" name="Country"><br>
                        <label for="Habitat">Habitat</label>
                        <input type="text" name="Habitat"><br>
                        <label for="Race_text">Race content :</label><br>
                        <textarea type="text" name="Race_text" id="content_input"></textarea><br><br>
                        <button type="submit">Submit</button>
                    </form><br>



                </div>

            </div>

        </div>

    </body>
</html>