<?php
require "./blueprints/page_init.php"; // includes the page initialization file
include "./blueprints/gl_ap_start.php";
?>

<div id="mainText" style="opacity: 100%;"> <!-- Right div -->
    <div>
        <a id=Return onclick='window.history.back()'> Return </a><br>
        <img src="../images/map/dimensions.png" alt="Dimensions of the universe" style="width: 100%; height: auto;">
    </div>
</div>

<?php
include "./blueprints/gl_ap_end.php";
?>