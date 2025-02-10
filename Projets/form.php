<html>
    <head>
        <link rel="stylesheet" href="form.css" />
    </head>
    <body>
        <form method="Post">
            <div class='log'>
                <div>
                    <label for="name"> name </label> <br>
                    <label for="login"> mots de passe </label>
                </div>
                <div>
                    <?php
                        echo '<input type="text" name="name" value="';
                        if(!empty($_POST["name"])) {
                            echo $_POST["name"]; 
                        }
                        echo  '"/> <br>';
                        
                        echo '<input type="text" name="login" value="';
                        if(!empty($_POST["login"])) {
                            echo $_POST["login"];
                        }
                        echo '"/> <br>';
                    ?>
                    <br>
                </div>
            </div>
            <input type="submit" value="se connecter"/>
        </form>
        <div>
            <?php
                if(!empty($_POST["name"])) {
                    echo $_POST["name"];
                    echo '<br>';
                }
                if(!empty($_POST["login"])) {
                    echo $_POST["login"];
                }
            ?>
        </div>
    </body>
</html>

