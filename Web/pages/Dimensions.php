<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php";
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
                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php') { // if the file is not a directory and has a .php extension, add it to the $pages array
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
        if (($user['admin']) == 1 ) { 
            // User is admin
        } elseif (($user["admin"]) == null || ($user["admin"]) == '') {
            // User is not admin
        } else {
            echo "Error in the admin column<br>";
        }
    }

    sort($pages); // sort the array alphabetically
    $frstLetter = ""; // initialize the $frstLetter variable

    /// in case the first page is for a page that the user shouldn't have access to like hidden or admin pages
    // if the user is (logged in and is admin or the page is public) and the type of the page is Dimensions
    if (((isset($_SESSION['user']) && ($authorisation[$pages[0]] == 'admin' && ($user['admin']) == 1)) || $authorisation[$pages[0]] == 'all') && $type[$pages[0]] == 'Dimensions') {
        $frstLetter = mb_substr($pages[0], 0, 1); // get the first letter of the first element in the $pages array
        echo "<span>$frstLetter</span>"; // display the first letter
        // Start of the unordered list here otherwise it creates an unsightly space
        echo "<ul>"; 
    } 

    // Loop through the array and display the elements in the list
    foreach ($pages as $page) { // for each element in the $pages array, display a link to the corresponding page
        
        // if the user is (logged in and is admin or the page is public) and the type of the page is Dimensions
        if (((isset($_SESSION['user']) && ($authorisation[$page] == 'admin' && ($user['admin']) == 1)) || $authorisation[$page] == 'all') && $type[$page] == 'Dimensions') {
            // if the first letter of the element is different from the first letter of the first element in the $pages array, close the list and open a new one
            // this allows grouping elements by first letter
            if(mb_substr($page, 0, 1) != $frstLetter) { 
                echo "</ul>"; // even if the list isn't open, the closing tag won't cause any problem
                $frstLetter = mb_substr($page, 0, 1);
                echo "<span>$frstLetter</span>";
                echo "<ul>";
            }
            echo "<li><a href='./" . sanitize_output($page) . ".php'>$page</a></li>"; // link to the corresponding page in the $pages array
        
        }
        
    }
    

    // End of the list
    echo "</ul>";
    ?>

</div>
                

<?php
require_once "./blueprints/gl_ap_end.php";
?>