<html>
    <head>
    </head>

    <body>
        <style>
            
        </style>
    <?php

        $tailleCercle = 20;

        function esp() { 
            echo "<span>a</span>";}

        for ($i=1; $i<=$tailleCercle; $i++) { 

            $esp=$tailleCercle-$i;
            while ($esp>1) { 
                $esp-=1/log($esp);
                esp();
            }

            for ($y=0; $y<=$i+ceil(1/log($i+1)); $y++) { 
                if ($y==0 or $y==$i+ceil(1/log($i+1))) { 
                    echo "<span>o</span>";
                } else { 
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                }
            }
            echo "<br>"; 
        }

    ?>
</html>