
<html>
    <head>
        <link rel='stylesheet' href='../../style/PageStyle.css?ver=<?php echo time(); ?>'>
        <link rel='stylesheet' href='../../style/styleScript.css?ver=<?php echo time(); ?>'>
        <title>
            Custodes
        </title>
    </head>
    
    <body>
        <div id='global'>
            <div id='enTete'>
                <img id='icon' src='../../images/custodes.jpg'>
                <div id='divTitre'>
                    <a id='Titre'> Custodes </a>
                </div>
                <div id='divacceuil'>
                    <div id='acceuil' onclick='window.location.href="../Accueil.php"'>
                        <a> La Grande Librairie </a>
                    </div>
                </div>
            </div>

            <div id='englobe'>
                <div class='texteGauche'>
                    <img id='aquila' src='../../images/imperial_aquila.png'> <br>
                    <?php // créé un span et écrit dedans le contenu du fichier 41st_millennium.txt
                        echo '<span>' . nl2br(htmlspecialchars(file_get_contents('../../texte/41st_millennium.txt'))) . '</span>';
                    ?>
                </div>

                <div class='textePrincipal'>
                    <!-- Contenu spécifique à la race -->
                    <!-- Vous pouvez ajouter ici des informations supplémentaires sur chaque race -->
                    
                </div>
            </div>
        </div>

        <script> // compte le nombre de div 'selection' pour ajuster la taille de 'textePincipal' car le 'flex-wrap: wrap;' dedans peu espacer un peu trop verticalement les divs
            // JavaScript pour compter le nombre d'éléments avec une classe spécifique
            const count = document.querySelectorAll('.selection').length;

            // Utilisez fetch pour envoyer le résultat à un fichier PHP pour traitement
            fetch('../../style/styleControl.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'count=' + count
            })
            .then(response => response.text())
        </script>

        <script> // recharge la page après un cours délai pour bien intégrer la taille des divs
            document.addEventListener('DOMContentLoaded', function () {
            // Vérifie si un identifiant unique est déjà enregistré
            if (!sessionStorage.getItem('pageReloaded')) {
                sessionStorage.setItem('pageReloaded', 'true'); // Marque comme rechargée
                setTimeout(function() {
                        location.reload();
                }, 50); // le délai est de 0.05 secondes
            } else 
                sessionStorage.removeItem('pageReloaded'); // Réinitialise pour les prochaines visites
            });
        </script>

    </body>
</html>
            