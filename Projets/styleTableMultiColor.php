


<?php
include 'tableMultiColor.php';
header("Content-type: text/css");
?>


table{
    border-spacing: 0px;
    padding: 0px;
}

td {
    padding: 0px;
    height: 30px;
    width: 30px;
    border: 1px solid;
    border-color: white;
    text-align: center;
}

<?php for ($i=0; $i<=$taille; $i++){ // récupère les différents enteteX pour leur donenr chacun une couleur?> 
.entete<?php echo $i; ?> {
    border-radius: 50%;
    background-color: hsl(<?php echo $i*50; ?> , 100%, 70%);
    
}<?php } ?>

img {
    height: 30px;
    width: auto;
}