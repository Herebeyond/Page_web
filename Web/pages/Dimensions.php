<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php";
?>
                
<div id="mainText"> <!-- Right div -->

    <button id="Return" onclick="window.history.back()">Return</button><br>
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

    // Helper function to check if user has access to a page
    function hasPageAccess($page, $authorisation, $user_roles) {
        if (!isset($authorisation[$page])) {
            return false;
        }
        
        $auth_level = $authorisation[$page];
        
        if ($auth_level === 'all') {
            return true;
        } elseif ($auth_level === 'admin') {
            return isset($_SESSION['user']) && in_array('admin', $user_roles);
        } elseif ($auth_level === 'hidden') {
            return true; // Hidden pages are accessible but not displayed in lists
        }
        
        return false;
    }

    // Filter pages that are Dimensions type and user has access to
    $dimension_pages = [];
    foreach ($pages as $page) {
        if (isset($type[$page]) && $type[$page] === 'Dimensions' && hasPageAccess($page, $authorisation, $user_roles)) {
            $dimension_pages[] = $page;
        }
    }

    sort($dimension_pages); // sort the array alphabetically

    if (!empty($dimension_pages)) {
        $current_letter = "";
        $list_open = false;

        // Loop through the filtered pages and display them grouped by first letter
        foreach ($dimension_pages as $page) {
            $first_letter = mb_strtoupper(mb_substr($page, 0, 1));
            
            // If new letter group, close previous and start new one
            if ($first_letter !== $current_letter) {
                if ($list_open) {
                    echo "</ul>";
                }
                echo "<span>" . htmlspecialchars($first_letter) . "</span>";
                echo "<ul>";
                $current_letter = $first_letter;
                $list_open = true;
            }
            
            echo "<li><a href='./" . sanitize_output($page) . ".php'>" . htmlspecialchars($page) . "</a></li>";
        }
        
        // Close the final list if one was opened
        if ($list_open) {
            echo "</ul>";
        }
    } else {
        echo "<p>No dimension pages available.</p>";
    }
    ?>

</div>
                

<?php
require_once "./blueprints/gl_ap_end.php";
?>
