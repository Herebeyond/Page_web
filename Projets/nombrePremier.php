<html>
    <head>
        <?php
            // Inclut le fichier contenant la fonction estPremier().
            include 'function.php'; 
        ?>
    </head>

    <body>
        <?php
            
            // Initialise la variable $i avec la valeur 2 (le premier nombre entier positif qui peut être premier).
            $i = 2;

            // Boucle tant que $i est strictement inférieur à 100 car on cherche les nombres premiers inférieur à 100.
            while ($i < 100) {

                // Vérifie si le nombre $i est premier en utilisant la fonction estPremier().
                if (estPremier($i) == true) {
                    
                    // Affiche le nombre s'il est premier, suivi d'un saut de ligne HTML.
                    echo $i . "<br>";
                }

                // Incrémente $i de 1 pour passer au nombre suivant.
                $i++;
            }
        ?>
    </body>
</html>
