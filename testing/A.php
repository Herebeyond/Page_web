<?php
// Création d'un tableau avec des éléments à afficher dans la liste
$fruits = ["Pomme", "Banane", "Orange", "Fraise", "Mangue"];

// Début de la liste non ordonnée
echo "<ul>";

// Parcours du tableau et affichage des éléments dans la liste
foreach ($fruits as $fruit) {
    echo "<li>$fruit</li>";
}

// Fin de la liste
echo "</ul>";
?>