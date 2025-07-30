<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>
<div id=englobe>
<div class=leftText> <!-- Left div -->
    <div id=leftHeaderText>
        <?php
            for($i=0; $i<4; $i++) {
                echo "<div><img src=../images/Icon.png></div>";
            }?> <!-- creates 4 identical images as decoration for the left text -->
    </div>
    <?php // creates a span and writes the content of the forgotten_worlds.txt file inside
        echo '<span>' . nl2br(sanitize_output(file_get_contents("../texte/forgotten_worlds.txt"))) . '</span>';
    ?>
</div>
<div id="mainText"> <!-- Right div -->

    <button id="Return" onclick="window.history.back()">Return</button><br>
    <?php
    // Read file names in the pages directory
    $pages = [];
    $dir = "../pages";
    if (is_dir($dir)) { // if the directory exists
        if ($dh = opendir($dir)) { // open the directory for reading
            while (($file = readdir($dh)) !== false) { // read files in the directory
                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php' && $file != "Homepage.php") { // if the file is not a directory and has a .php extension and isn't Homepage.php, add it to the $pages array
                    $pages[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
            closedir($dh); // close the directory
        }
    }

    /// ADMIN VERIFICATION
    // $user and $user_roles are already set in page_init.php
    // No need to fetch $user again

    sort($pages); // sort the array alphabetically
    $frstLetter = ""; // initialize the $frstLetter variable

    // in case the first page is for a page that the user shouldn't have access to like hidden or admin pages
    if ((isset($_SESSION['user']) && ($authorisation[$pages[0]] == 'admin' && in_array('admin', $user_roles))) || ($authorisation[$pages[0]] == 'all' && $type[$pages[0]] == 'common')) { // if the user is logged in and is admin or if the first page is public and of common type
        $frstLetter = mb_substr($pages[0], 0, 1); // get the first letter of the first element in the $pages array
        echo "<span class='firstLetter'>" . $frstLetter . "</span>"; // display the first letter
        // Start of the unordered list here otherwise it creates an unsightly space
        echo "<ul class='homepageLetterGroup'>"; 
    } 

    // Loop through the array and display the elements in the list
    foreach ($pages as $page) { // for each element in the $pages array, display a link to the corresponding page
        
        if ((isset($_SESSION['user']) && ($authorisation[$page] == 'admin' && in_array('admin', $user_roles))) || ($authorisation[$page] == 'all' && $type[$page] != 'admin')) { // if the user is logged in and is admin or if the page is public and of common type
            // if the first letter of the element is different from the first letter of the first element in the $pages array, close the list and open a new one
            // this allows grouping elements by first letter
            if (mb_substr($page, 0, 1) != $frstLetter) { 
                echo "</ul>";
                $frstLetter = mb_substr($page, 0, 1);
                echo "<span class='firstLetter'>" . sanitize_output($frstLetter) . "</span>";
                echo "<ul class='homepageLetterGroup'>";
            }
            echo "<li class='homepageList'><a href='./" . sanitize_output($page) . ".php'>" . sanitize_output($page) . "</a></li>"; // link to the corresponding page in the $pages array
        }
    }

    // End of the list
    echo "</ul>";
    ?>
</div>

<?php 
require_once "./blueprints/gl_ap_end.php"; 
?>
