<html>
    <head>
        <link rel="stylesheet" href="./styleTableMultiColor.php">
    </head>

    <body>
        <?php
            $taille = 12;
            echo "<table>";
            for ($ligne=0; $ligne<=$taille; $ligne++){
                echo "<tr>";
                if ($ligne==0) {
                    echo "<td><img src='./croix.png'></img></td>";
                } else {
                    echo "<td class=entete" . $ligne . ">" . $ligne . "</td>"; // créé X entete différent avec un numéro dans leur nom en fonction de $taille
                }
                for ($colonne=1; $colonne<=$taille; $colonne++) {
                    if ($ligne==0) {
                        echo "<td class=entete" . $colonne . ">" . $colonne;
                    } elseif ($ligne==$colonne) {
                        echo "<td>" . $ligne*$colonne . "</td>";
                    } else {
                        echo "<td class=contenu style='background-color: hsl(";
                        if($colonne<$ligne) {
                            echo $ligne*50;
                        } else {
                            echo $colonne*50;
                        }
                        echo ", 100%, 70%)'>" . $colonne*$ligne;
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        ?>
    </body>
</html>