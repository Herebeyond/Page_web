<html>
    <head>
        <style>
            #ele {
                border: 3px solid black;
                border-collapse: collapse;
                font-size: 20;
            }
            #trf {
                border: 2px solid black;
            }
            td {
                border: 1px solid black;
                padding-left: 8px;
                padding-right: 8px;
            }
        </style>
    </head>
    <body>

        <?php
            echo "Bonjour!<br>";
            $students = [
                'std1' => 17,
                'std2' => 15,
                'std3' => 8,
                'std4' => 18,
                'std5' => 12,
            ];
            $somme=0;
            for($i=1;$i<10;$i++) {
                $somme+=$i;
            }
            echo "Le résultat de la somme est de : " .$somme ."<br>";
            $somme=0;?>
            <br><br>
            <!-- table des élèves avec les notes -->
            <table id=ele>
                <tr id=trf>
                    <td>élève</td>
                    <td>note</td>
                </tr>
                <?php
                foreach($students as $name => $note) {
                    $somme+=$note;?>
                    <tr>
                        <td><?php echo $name ?></td>
                        <td><?php echo $note ?></td>
                    </tr>            
                <?php
            };
            ?>
                <tr id=trf>
                    <td>moyenne</td>
                    <td><?php echo $somme/count($students) ?></td>
                </tr>
            </table>
        <br><br>
    </body>
</html>

