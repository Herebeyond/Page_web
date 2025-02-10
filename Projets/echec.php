<html>
    <head>
        <link rel="stylesheet" href="./style.css">
        <title>
            Echec
        </title>
    </head>
    <body>
        <table>
        <?php
            for($hauteur=1;$hauteur<=10;$hauteur++) {
                echo "<tr>";
                for($largeur=1;$largeur<=10;$largeur++) {
                    if(($hauteur+$largeur)%2==0) {
                        echo "<td class=noir></td>";
                    } else {
                        echo "<td class=blanc></td>";
                    }  
                }
                echo "</tr>";
            }
        ?>
        </table>
    </body>
</html>

