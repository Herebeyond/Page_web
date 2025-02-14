<?php
include "./blueprints/page_init.php";
include './scriptes/autorisation.php'; // inclut le fichier autorisation.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['Identification']); // Nettoyage des entrées utilisateur
    $password = trim($_POST['psw']); 

    // Vérification que les champs ne sont pas vides
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "please fill in all fields";
        header('Location: login.php');
        exit;
    }


    // Récupérer le nom de la racce depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM races WHERE nom_race = ?"); 
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Vérifier si le mot de passe correspond au mot de passe haché dans la base
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['id']; // Connecte l'utilisateur
        header('Location: page.php');
        exit;
    } else {
        $_SESSION['error'] = "username or password incorrect";
    }
}
?>



<html>
    <head>
        <?php $chemin_absolu = 'http://localhost/test/Web/';?>
        <link rel="stylesheet" href= "<?php echo $chemin_absolu . "style/PageStyle.css?ver=" . time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <!-- <link rel="stylesheet" href="../style/styleScript.css?ver=<?php // echo time(); ?>"> le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <?php include "./scriptes/pages_generator.php" ?>
        <?php //include "./scriptes/pages_factions_generator.php" ?>
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
                



                <div class="textePrincipal"> <!-- Div de droite -->
                    <a id=retourArriere onclick='window.history.back()'> Retour </a><br>
                    <?php
                        // Informations de connexion à la base de données MySQL
                        $host = 'db';
                        $dbname = 'univers';
                        $username = 'root';
                        $password = 'root_password';

                        try {
                            // Connexion à la base de données MySQL
                            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            // Récupération des données du tableau Races
                            $query = $pdo->query("SELECT * FROM Races ORDER BY id_race;");

                            
                            
                            
                            

                        } catch (PDOException $e) {
                            // Gestion des erreurs
                            echo "Erreur d'insertion : " . $e->getMessage();
                        }
                    
                    
                        
                        
                    ?>
                </div>

            </div>

        </div>

    </body>
</html>