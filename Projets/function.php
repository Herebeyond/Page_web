<?php
    // Fonction pour vérifier si un nombre est premier.
    // int pour forcer un entier $x en entrée et : bool pour forcer la fonction à retourner un booléen.
    function estPremier(int $x) : bool {
        
        // Variable pour indiquer si $x est premier (initialisée à true).
        $testV = true;

        // Boucle pour tester les diviseurs possibles de $x.
        // On commence par 2 et on va jusqu'à la moitié de $x (arrondi).
        // Pas besoin d'aller au dela de la moitié de $x car aucun diviseur ne peut être plus grand que la moitié de son nombre
        for ($testing = 2; $testing < ($x+1)/2; $testing++) {

            // Test si $x est divisible par $testing (si oui alors $x n'est pas un nombre premier).
            if ($x % $testing == 0) {
                
                // Met la variable $testV à false et quitte la boucle.
                $testV = false;
                
                break;
            }
        }

        // Retourne le résultat : true si $x est premier, sinon false.
        return $testV;
    }
?>
