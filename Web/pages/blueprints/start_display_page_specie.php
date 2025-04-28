<?php
require_once "./blueprints/page_init.php";
require_once "./blueprints/gl_ap_start.php";

// Number of item per page
$perPage = 8;

// Calculate the total number of pages
$totalRacesQuery = $pdo->prepare("SELECT COUNT(*) FROM species");
$totalRacesQuery->execute();
$totalRaces = $totalRacesQuery->fetchColumn();
$totalPages = ceil($totalRaces / $perPage);

// Get the current page from the URL, default is page 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

// Calculate the offset for the SQL query
$offset = ($page - 1) * $perPage;
?>

<div id="mainText"> <!-- Right div -->
    <button id="Return" onclick="window.history.back()">Return</button><br>
    <div id="mainTextList" class="species-list"> <!-- Horizontal list for species -->
        