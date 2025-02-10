<html>
    <head>
        <link rel="stylesheet" href="../style/PageStyle.css?ver=<?php echo time(); ?>"> <!-- permet de créer un "nouveau" css pour que le site ne lise pas son cache et relise le css, ainsi applicant les changements écrit dedans -->
        <link rel="stylesheet" href="../style/styleScript.css?ver=<?php echo time(); ?>"> <!-- le echo time() permet de générer un nombre aléatoire pour générer une version différente "unique" -->
        <?php include "./scriptes/pages_races_generator.php" ?>
        <?php include "./scriptes/pages_factions_generator.php" ?>
        <title>
            Page d'Accueil
        </title>
    </head>

    
        
    <body>
        <div id=global>

            <div id=enTete>
                <img id=icon src='../images/custodes.jpg'>
                <div id=divTitre>
                    <a id=Titre> Warhammer 40k </a>
                </div>
                <div id=divacceuil>
                    <div id=acceuil onclick=window.location.href='./Accueil.php'>
                        <a> La Grande Librairie </a>
                    </div>
                </div>
            </div>
            
            <div id=englobe>
            
                <div class=texteGauche> <!-- Div de gauche -->
                    <img id=aquila src='../images/imperial_aquila.png'> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier 41st_millennium.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents("../texte/41st_millennium.txt"))) . '</span>';
                    ?>
                </div>

                <div class="textePrincipal"> <!-- Div de droite -->
                    <?php
                        try {
                            // Chemin vers votre fichier Access
                            $dbPath = "C:\\xampp\\htdocs\\test\\Site_internet\\BDD\\web_BDD.accdb";
                            $pdo = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbPath;");
                            
                            // Récupération des données du tableau Races
                            $query = $pdo->query("SELECT * FROM Races ORDER BY numero;");

                            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                $imgPath = htmlspecialchars($row["icon"], ENT_QUOTES, 'UTF-8');
                                if (htmlspecialchars($row["icon"], ENT_QUOTES, 'UTF-8') == Null or htmlspecialchars($row["icon"], ENT_QUOTES, 'UTF-8') == '') {
                                    $imgPath = '../images/default_icon.png';
                                }
                                // Création d'une div pour chaque faction
                                // htmlspecialchars(" ... ", ENT_QUOTES, 'UTF-8') permet d'échapper les caractères spéciaux dans la chaîne de caractères (tel que les ' et ")
                                // et ainsi les empêche de fermer des chaines de caractères

                                // ajouter un "alt" à l'image pour emplacer 

                                echo " 
                                    <div class='selection'>
                                        <div class='classImgSelection'>
                                            <img class='imgSelection' src='" . $imgPath . "'onclick='window.location.href=\"./Races/" . htmlspecialchars($row["nom_race"], ENT_QUOTES, 'UTF-8') . ".php\"'>
                                        </div>
                                        <div class='texteSelection' onclick='window.location.href=\"./Races/" . htmlspecialchars($row["nom_race"], ENT_QUOTES, 'UTF-8') . ".php\"'>
                                            " . htmlspecialchars($row["nom_race"], ENT_QUOTES, 'UTF-8') . "
                                        </div>
                                    </div>
                                ";
                            }
                        } catch (PDOException $e) {
                            echo "Erreur de connexion : " . $e->getMessage();
                        }
                        
                        
                    ?>
                </div>

            </div>

        </div>




        <script> // compte le nombre de div "selection" pour ajuster la taille de "textePincipal" car le "flex-wrap: wrap;" dedans peu espacer un peu trop verticalement les divs
            // compte le nombre d'éléments avec une classe spécifique
            const count = document.querySelectorAll('.selection').length;

            // Utilisez fetch pour envoyer le résultat à un fichier PHP pour traitement
            fetch('../style/styleControl.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'count=' + count
            })
            .then(response => response.text())
        </script>

        <script> // recharge la page après un cours délai pour bien intégrer la taille des divs
            document.addEventListener("DOMContentLoaded", function () {
            // Vérifie si un identifiant unique est déjà enregistré
            if (!sessionStorage.getItem("pageReloaded")) {
                sessionStorage.setItem("pageReloaded", "true"); // Marque comme rechargée
                setTimeout(function() {
                        location.reload(); // recharge
                }, 50); // le délai est de 0.05 secondes
            } else 
                sessionStorage.removeItem("pageReloaded"); // Réinitialise pour les prochaines visites
            });
        </script>

    </body>
</html>