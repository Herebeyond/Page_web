

<?php
ob_start();  // Démarre la mise en cache de la sortie
session_start(); // Démarre la session

?>


<html>
    <head>
        <link rel="stylesheet" href="./styles/style.css?ver=<?php echo time(); ?>">


        <title>
            Lettre au Père Noël
        </title>
    </head>
    <body>
        <div id=global>

            <div id=header>
                Joyeux Noël <br>
                Commande ton cadeau ici
            </div>

            <div id=link>
                <a onclick='window.location.href="./Intro.php"'> Vers l'intro </a>
            </div>


            <div id=englobe>
            





                <form method="Post" id=divsPrincipal>
                    <fieldset class=questions>
                        <legend>
                            <h1 class=qheader>
                                Comment t’appelles-tu ?
                            </h1>
                        </legend>

                            <div>
                                <label for="qSurname">Prénom</label>
                                <input type="text" name="qSurname" value="<?php if(!empty($_POST["qSurname"])) { echo $_POST["qSurname"]; } ?>" required>
                                <br>
                                <label for="qName">Nom</label>
                                <input type="text" name="qName" value="<?php if(!empty($_POST["qName"])) { echo $_POST["qName"]; } ?>" required>
                                <br>
                                
                            </div>
                        
                    </fieldset>

                    
                    <fieldset class=questions>
                        <legend>
                            <h1 class=qheader>
                                Quand es-tu né ?
                            </h1>
                        </legend>
                        
                            <div>
                                <label for="qnaissance">Date de naissance</label>
                                <input type="date" name="qnaissance" value="<?php if(!empty($_POST["qnaissance"])) { echo $_POST["qnaissance"]; } ?>" required>
                                <br>
                                
                            </div>
                        
                    </fieldset>

                    <fieldset class=questions>
                        <legend>
                            <h1 class=qheader>
                                Où habites-tu ?
                            </h1>
                        </legend>
                        
                            <div>
                                <label for="qhabitation1">Adresse</label>
                                <br>
                                <input type="text" name="qhabitation1" value="<?php if(!empty($_POST["qhabitation1"])) { echo $_POST["qhabitation1"]; } ?>" required>
                                <br>
                                <input type="text" name="qhabitation2" value="<?php if(!empty($_POST["qhabitation2"])) { echo $_POST["qhabitation2"]; } ?>">
                                <br>
                                <input type="text" name="qhabitation3" value="<?php if(!empty($_POST["qhabitation3"])) { echo $_POST["qhabitation3"]; } ?>">
                                <br>
                                
                            </div>
                        
                    </fieldset>

                    <fieldset class=questions>
                        <legend>
                            <h1 class=qheader>
                                Quel est ton email ?
                            </h1>
                        </legend>
                        
                            <div>
                                <label for="qemail">Email</label>
                                <input type="text" name="qemail" value="<?php if(!empty($_POST["qemail"])) { echo $_POST["qemail"]; } ?>" required>
                                <br>
                                
                            </div>
                        
                    </fieldset>


                    <fieldset class=questions>
                        <legend>
                            <h1 class=qheader>
                                Quel est ton numéro de téléphone ?
                            </h1>
                        </legend>
                        
                            <div>
                                <label for="qtelephone">Téléphone</label>
                                <input type="text" name="qtelephone" value="<?php if(!empty($_POST["qtelephone"])) { echo $_POST["qtelephone"]; } ?>" required>
                                <br>
                                
                            </div>
                        
                    </fieldset>

                    <fieldset class=questions>
                        <legend>
                            <h1 class=qheader>
                                Quel est ton sexe ?
                            </h1>
                        </legend>
                        
                            <div>
                                <label for="qsex">Sexe</label>
                                <select name="qsex" required>
                                    <option value="man">Garçon</option>
                                    <option value="woman">Fille</option>
                                    <option value="other">Autre</option>
                                    <option value="not saying">Ne se prononce pas</option>
                                </select>
                                <br>
                                
                            </div>
                        
                    </fieldset>

                    <fieldset class=questions>
                        <legend>
                            <h1 class=qheader>
                                Quel cadeau aimerais-tu recevoir?
                            </h1>
                        </legend>
                        
                            <div>
                                <label for="qchoice">Cadeau</label>
                                <select name="qchoice" required>

                                <?php 
                                try { // affiche les jouets de la base de données dans une liste déroulante
                                    // Connexion à ma base de données personnel de beaupeyrat
                                    $bd = new PDO('mysql:host=10.187.22.3;dbname=bd3_2024_baillard', "bbaillard", "WYH8rFEHfoLjY0ez");

                                    // Configuration des attributs PDO
                                    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    $bd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                                    // Préparation et exécution de la requête
                                    $texteReq = "SELECT * FROM gift";
                                    $req = $bd->prepare($texteReq);
                                    $req->execute();

                                    // Récupération des résultats
                                    $result = $req->fetchAll();
                                    
                                    // Affichage des jouets
                                    foreach ($result as $row) {
                                        echo "<option value='" . htmlspecialchars($row["Nom_jouet"]) . "'>" . htmlspecialchars($row["Nom_jouet"]) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "Erreur de connexion : " . htmlspecialchars($e->getMessage());
                                }
                                ?>

                                </select>
                                <br>
                                
                            </div>
                        
                    </fieldset>

                    <fieldset class=questions>
                        <legend>
                            <h1 class=qheader>
                                As-tu été sage cette année? <br> Ne mens pas le Père Noël sait tout
                            </h1>
                        </legend>
                        
                            <div>
                                <input class=longInput type="text" name="qsage" value="<?php if(!empty($_POST["qsage"])) { echo $_POST["qsage"]; } ?>" required>
                            </div>
                        
                    </fieldset>
                    

                    <fieldset class=questions>
                        <input type="submit" value="Envoyer">
                    </fieldset>
                </form>
            </div>




            
        </div>
        <?php
        // Vérification si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Connexion à la base de données
                $bd = new PDO('mysql:host=10.187.22.3;dbname=bd3_2024_baillard', "bbaillard", "WYH8rFEHfoLjY0ez");
                $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $bd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Préparation de la requête SQL d'insertion
                $sql = "INSERT INTO users (surname, name, birth_date, adresse1, adresse2, adresse3, email, tel, sexe, gift, message)
                        VALUES (:surname, :name, :birth_date, :addresse1, :addresse2, :addresse3, :email, :tel, :sexe, :gift, :message)";

                // Préparation de la requête
                $stmt = $bd->prepare($sql);

                // Lier les valeurs des champs aux paramètres de la requête
                $stmt->bindParam(':surname', $_POST['qSurname']);
                $stmt->bindParam(':name', $_POST['qName']);
                $stmt->bindParam(':birth_date', $_POST['qnaissance']);
                $stmt->bindParam(':addresse1', $_POST['qhabitation1']);
                $stmt->bindParam(':addresse2', $_POST['qhabitation2']);
                $stmt->bindParam(':addresse3', $_POST['qhabitation3']);
                $stmt->bindParam(':email', $_POST['qemail']);
                $stmt->bindParam(':tel', $_POST['qtelephone']);
                $stmt->bindParam(':sexe', $_POST['qsex']);
                $stmt->bindParam(':gift', $_POST['qchoice']);
                $stmt->bindParam(':message', $_POST['qsage']);

                // Exécution de la requête
                $stmt->execute();

                // Enregistrer les données dans la session pour les utiliser après la redirection
                $_SESSION['surname'] = $_POST['qSurname'];
                $_SESSION['name'] = $_POST['qName'];
                
                // Redirection vers la page de félicitation
                header("Location: congrat_order.php");
                exit(); // Arrête l'exécution du script après la redirection

            } catch (PDOException $e) {
                // Gestion des erreurs
                echo "Erreur d'insertion : " . htmlspecialchars($e->getMessage());
            }
        }
        ?>


    </body>
</html>