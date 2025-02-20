<?php
include "./blueprints/page_init.php"; // inclut le fichier d'initialisation de la page
require '../login/db.php'; // Connexion à la base

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis
    $Specie_name = isset($_POST['Specie_name']) ? trim($_POST['Specie_name']) : ''; // Nettoyage des entrées utilisateur
    $Specie_Icon = isset($_POST['icon_Specie']) ? trim($_POST['icon_Specie']) : '';
    $Specie_content = isset($_POST['Specie_text']) ? trim($_POST['Specie_text']) : '';


    // Récupérer le nom de la Specie depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM species WHERE nom_Specie = ?"); 
    $stmt->execute([$Specie_name]);
    $Specie = $stmt->fetch();

    // Vérifier si la Specie existe déjà dans la base, si non alors intégrer la Specie à la base de données
    if ($Specie) {
        $_SESSION['error'] = "Specie already exists";
        header('Location: Specie_add.php');
        exit;
    } else {
        // Insérer la nouvelle Specie dans la base de données
        $stmt = $pdo->prepare("INSERT INTO species (nom_Specie, icon_Specie, content_Specie) VALUES (?, ?, ?)");
        $stmt->execute([$Specie_name, $Specie_Icon, $Specie_content]);
        $_SESSION['success'] = "Specie added successfully";
        header('Location: Specie_add.php');
        exit;
    }
}
?>



<html>
    <head>
        <link rel="stylesheet" href="../style/PageStyle.css?ver=<?php echo time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <title>
            Add Specie
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
                



                <div id='add_specie' class="textePrincipal"> <!-- Div de droite -->
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

                    <h2> Add a Specie </h2><br>
                    <form method="POST" action="Specie_add.php">
                        <label for="Specie_name">Specie Name</label>
                        <input type="text" name="Specie_name" required><br>
                        <label for="Specie_icon">Specie Icon</label>
                        <input type="text" name="icon_Specie"><br>
                        <label for="Specie_text">Specie content :</label><br>
                        <input type="text" name="Specie_text" id="content_input"><br><br>
                        <button type="submit">Submit</button>
                    </form><br>



                </div>

            </div>

        </div>
        <script>
            function confirmSubmit() { // Fonction pour confirmer ou annuler la soumission du formulaire
                return confirm("Are you sure you want to add this specie?");
            }
        </script>
    </body>
</html>