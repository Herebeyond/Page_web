<?php
require_once "./scriptes/functions.php"; // includes the functions file
?>

<html>
    <head>
        <link rel="stylesheet" href= "<?php echo "../style/PageStyle.css?ver=" . time(); ?>"> <!-- creates a "new" CSS so that the site does not read its cache and rereads the CSS, thus applying the changes written in it -->
        <!-- echo time() generates a random number to generate a different "unique" version -->
        <title>
            Home Page
        </title>
    </head>
    <body>
        <div id=global>
            <?php require_once "./blueprints/header.php" ?>
            <div id=englobe>
                <div class=leftText> <!-- Left div -->
                    <div id=leftHeaderText>
                        <?php
                            for($i=0; $i<4; $i++) {
                                echo "<div><img src=../images/Icon.png></div>";
                            }?> <!-- creates 4 identical images as decoration for the left text -->
                    </div> <br>
                    <?php // creates a span and writes the content of the forgotten_worlds.txt file inside
                        echo '<span>' . nl2br(sanitize_output(file_get_contents("../texte/forgotten_worlds.txt"))) . '</span>';
                    ?>
                </div>
                