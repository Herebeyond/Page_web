<html>
    <head>
        <link rel="stylesheet" href="../style/PageStyle.css?ver=<?php echo time(); ?>">
        <link rel="stylesheet" href="../style/styleScript.css?ver=<?php echo time(); ?>">
        <title>
            Titre
        </title>
    </head>
        

    
    <body>
        <div id=global>

            <div id=enTete>
                <img id=icon src='../images/custodes.jpg'>
                <div id=divTitre>
                    <a id=Titre> Titre </a>
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
                    <span>"It is the 41st Millennium. For more than a hundred centuries the Emperor of Mankind has sat immobile on the Golden Throne of Earth. He is the master of mankind by the will of the gods and master of a million worlds by the might of His inexhaustible armies. He is a rotting carcass writhing invisibly with power from the Dark Age of Technology. He is the Carrion Lord of the vast Imperium of Man for whom a thousand souls are sacrificed every day so that He may never truly die. <br><br>

                    Yet even in His deathless state, the Emperor continues His eternal vigilance. Mighty battlefleets cross the daemon-infested miasma of the Warp, the only route between distant stars, their way lit by the Astronomican, the psychic manifestation of the Emperor's will. Vast armies give battle in His name on uncounted worlds. Greatest amongst His soldiers are the Adeptus Astartes, the Space Marines, bio-engineered super-warriors. Their comrades in arms are legion: the Imperial Guard and countless planetary defence forces, the ever-vigilant Inquisition and the Tech-priests of the Adeptus Mechanicus to name only a few. But for all their multitudes, they are barely enough to hold off the ever-present threat to humanity from aliens, heretics, mutants -- and far, far worse. <br><br>

                    To be a man in such times is to be one amongst untold billions. It is to live in the cruelest and most bloody regime imaginable. These are the tales of those times. Forget the power of technology and science, for so much has been forgotten, never to be relearned. Forget the promise of progress and understanding, for in the grim dark future there is only war. There is no peace amongst the stars, only an eternity of carnage and slaughter, and the laughter of thirsting gods." <br><br>
                    </span>
                </div>

                <div class=textePrincipal> <!-- Div de droite -->
                    
                </div>

            </div>

        </div>

        


        <script> // compte le nombre de div "selection" pour ajuster la taille de "textePincipal" car le "flex-wrap: wrap;" dedans peu espacer un peu trop verticalement les divs
            // JavaScript pour compter le nombre d'éléments avec une classe spécifique
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
                        location.reload();
                }, 5); // le délai est de 0.005 secondes
            } else 
                sessionStorage.removeItem("pageReloaded"); // Réinitialise pour les prochaines visites
            });
        </script>





    </body>
</html>