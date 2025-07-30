<?php
require_once "./scriptes/functions.php"; // includes the functions file
?>

<html>
    <head>
        <link rel="stylesheet" href= "<?php echo "../style/PageStyle.css?ver=" . time(); ?>"> <!-- creates a "new" CSS so that the site does not read its cache and rereads the CSS, thus applying the changes written in it -->
        <!-- echo time() generates a random number to generate a different "unique" version -->
        <title>
            The Great Library
        </title>
    </head>
    <body>
        <div id=global>
            <?php require_once "./blueprints/header.php" ?>
            
                