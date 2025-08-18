<?php
require_once "./blueprints/page_init.php"; // includes the page initialization file
require_once "./blueprints/gl_ap_start.php";
?>
                
<div id="mainText"> <!-- Right div -->

    <button id="Return" onclick="window.history.back()">Return</button><br>
    <div class="page-intro">
        <h2>Beings & Creatures</h2>
        <p>Explore the diverse species and races that inhabit the Forgotten Worlds. From ancient beings to newly discovered creatures, each has their own unique characteristics and history.</p>
    </div>
    
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

    // Filter pages that are Beings type and user has access to
    $beings_pages = [];
    foreach ($pages as $page) {
        if (isset($type[$page]) && $type[$page] === 'Beings' && hasPageAccess($page, $authorisation, $user_roles)) {
            $beings_pages[] = $page;
        }
    }

    sort($beings_pages); // sort the array alphabetically

    if (!empty($beings_pages)) {
        echo "<div class='subpages-container'>";
        
        // Create a more organized display for beings-related pages
        $page_descriptions = [
            'Species' => 'Browse all species in the Forgotten Worlds',
            'Races' => 'Explore the different races within each species'
        ];
        
        foreach ($beings_pages as $page) {
            echo "<div class='subpage-card'>";
            echo "<h3><a href='./" . sanitize_output($page) . ".php'>" . htmlspecialchars($page) . "</a></h3>";
            
            if (isset($page_descriptions[$page])) {
                echo "<p>" . htmlspecialchars($page_descriptions[$page]) . "</p>";
            }
            
            echo "</div>";
        }
        
        echo "</div>";
    } else {
        echo "<p>No beings pages available.</p>";
    }
    ?>

</div>

<style>
    .page-intro {
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 8px;
        border-left: 4px solid #222088;
    }
    
    .subpages-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .subpage-card {
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .subpage-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .subpage-card h3 {
        margin-top: 0;
        color: #222088;
    }
    
    .subpage-card h3 a {
        text-decoration: none;
        color: inherit;
    }
    
    .subpage-card h3 a:hover {
        color: #a1abff;
    }
    
    .subpage-card p {
        margin-bottom: 0;
        color: #666;
        line-height: 1.5;
    }
</style>
                

<?php
require_once "./blueprints/gl_ap_end.php";
?>
