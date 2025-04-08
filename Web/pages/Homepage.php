<?php
require "./blueprints/page_init.php"; // includes the page initialization file
require "./blueprints/gl_ap_start.php"; // includes the start of the general page file
?>

<div id="mainText"> <!-- Right div -->

    <a id=Return onclick='window.history.back()'> Return </a><br>
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
    if (isset($_SESSION['user'])) {
        // Retrieve the username from the database
        $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user']]);
        $user = $stmt->fetch();
        
        // check if the user is admin or not
        if ($user['admin'] == 1) {
            // User is admin
        } elseif ($user["admin"] == null || $user["admin"] == '') {
            // User is not admin
        } else {
            echo "Error in the admin column<br>";
        }
    }

    sort($pages); // sort the array alphabetically
    $frstLetter = ""; // initialize the $frstLetter variable

    // in case the first page is for a page that the user shouldn't have access to like hidden or admin pages
    if ((isset($_SESSION['user']) && ($authorisation[$pages[0]] == 'admin' && ($user['admin']) == 1)) || ($authorisation[$pages[0]] == 'all' && $type[$pages[0]] == 'common')) { // if the user is logged in and is admin or if the first page is public and of common type
        $frstLetter = mb_substr($pages[0], 0, 1); // get the first letter of the first element in the $pages array
        echo "<span>" . $frstLetter . "</span>"; // display the first letter
        // Start of the unordered list here otherwise it creates an unsightly space
        echo "<ul>"; 
    } 

    // Loop through the array and display the elements in the list
    foreach ($pages as $page) { // for each element in the $pages array, display a link to the corresponding page
        
        if ((isset($_SESSION['user']) && ($authorisation[$page] == 'admin' && $user['admin'] == 1)) || ($authorisation[$page] == 'all' && $type[$page] == 'common')) { // if the user is logged in and is admin or if the page is public and of common type
            // if the first letter of the element is different from the first letter of the first element in the $pages array, close the list and open a new one
            // this allows grouping elements by first letter
            if (mb_substr($page, 0, 1) != $frstLetter) { 
                echo "</ul>";
                $frstLetter = mb_substr($page, 0, 1);
                echo "<span>" . sanitize_output($frstLetter) . "</span>";
                echo "<ul>";
            }
            echo "<li><a href='./" . sanitize_output($page) . ".php'>" . sanitize_output($page) . "</a></li>"; // link to the corresponding page in the $pages array
        }
    }

    // End of the list
    echo "</ul>";
    ?>
</div>

<?php 
require "./blueprints/gl_ap_end.php"; 
?>