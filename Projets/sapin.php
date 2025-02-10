<html>
    <head>
    </head>

    <body>
        <style>
            html {
                font-size: 40px; /* change la taille des symboles du sapin */
            }
            .bouleMil {
                color: red;
            }
            .bouleCote {
                color: yellow;
            }
            .tronc {
                color: brown;
                font-weight: bold;
            }
            .feuilles {
                color: green;
            }
            #chap {
                color: blue;
                font-weight: bold;
            }
        </style>


    <?php

        $nbLignes = 9; // définit le nombre de lignes que formeront les feuilles du sapin
        $tailleTronc = 2; // défini le nombre de lignes que fera le tronc du sapin

        function esp() { // sert à gagner de la place d'écriture, permet de générer les espaces pour décaler les caractères. 
            echo "<span>&nbsp;&nbsp;&nbsp;</span>"; // comme &nbsp; seul génère de petits espaces, il est plus simple d'en mettre plusieurs
        }
        function gdesp(int $x) { // on y insert un nombre $x et la fonction va se répéter autant de fois pour générer autant d'espaces qu'inscrit
            for ($esp=$x; $esp>=0; $esp--) {
                esp();
            }
        }

        for ($i=0; $i<=$nbLignes; $i++) { // la boucle se répète un nombre de fois égal à $nbLignes

            esp(); // décale tout le feuillage 1 fois pour qu'il s'aligne avec le tronc

            gdesp($nbLignes-$i); // génère un espace plus ou moins de fois en fonction de la ligne sur laquelle on se trouve pour décaler le feuillage et l'aligner avec le reste 

            if($i==0) { // pour la première ligne affiche un ^ au lieu d'une *
                echo "<span id=chap>^</span>";
            } else { // pour les autres lignes que la 1ère :
                for ($y=0; $y<=$i*2; $y++) { // comme $y commence à 0 et $i commence à 1 (car $i==0 à déjà été utilisé au dessus) alors y va se répéter $i*2+1(3) fois (0 => 1 => 2)
                    // $i*2 car $y commence à 0 donc le for se lance 1 fois puis 3 puis 5 puis 7 car $i augmente de 1 à chaque boucle et il est mutiplié par 2
                    if ($y==0 or $y==$i*2) { // vérifie si on se trouve au début ou à la fin de la ligne pour mettre o au lieu de *
                        echo "<span class=bouleCote>o&nbsp;</span>";
                    } elseif ($y==$i & $y>1) { // vérifie si on se trouve au milieu de la ligne pour mettre o au lieu de *
                        echo "<span class=bouleMil>o&nbsp;</span>";
                    } else { // sinon pour le reste afficher un *
                        echo "<span class=feuilles>*&nbsp;</span>";
                    }
                }
            }

            echo "<br>"; // à la fin de chaque ligne sauter une ligne
        }

        for($t=1; $t<=$tailleTronc; $t++) { // en fonction de $tailleTronc indiqué, répète x fois 2 chose :
            gdesp($i); // une fonction pour générer des espaces pour placer le tronc au milieu du feuillage
            echo "<span class=tronc>#</span><br>"; // et un <span> et un <br> pour placer le symbole du tronc et sauter une ligne
        }
    ?>
</html>