<?php

// To determine if a user has access to a page and/or is displayed, we will check the following:
// 1. all : all users can access the page and it is displayed normally
// 2. admin : only admin users can access the page and it is displayed only for them
// 3. user : only authenticated users can access the page and it is displayed for them
// 4. hidden : all users can access the page but it is not displayed, often because it needs an id in the url to display a specific resource and as such is only accessible by links

// So far, there is no need to hide a page for admin users
$authorisation = array(
    'Homepage' => 'all',
    'Map_view' => 'all',
    'Beings' => 'all',
    'Beings_display' => 'hidden',
    'Dimensions' => 'all',
    'Dimension_affichage' => 'all',
    'Dimension_list' => 'all',
    'Character_display' => 'hidden',
    'Characters' => 'all',
    'Character_add' => 'admin',
    'Admin' => 'admin',
    'User_profil' => 'user',
    'Map_modif' => 'admin',
    'place_detail' => 'hidden',
    'places_manager' => 'admin',
    'Ideas' => 'admin'
);


// To determine if the page is displayed normally or as the branch of another one, we will check the following:
// 1. common : the page is displayed normally
// 2. X : the page is displayed as a branch of the X page

// Other uses can be added later
$type = array(
    'Homepage' => 'common',
    'Map_view' => 'common',
    'Beings' => 'common',
    'Beings_display' => 'common',
    'Dimensions' => 'common',
    'Dimension_affichage' => 'Dimensions',
    'Dimension_list' => 'Dimensions',
    'Character_display' => 'common',
    'Characters' => 'common',
    'Character_add' => 'common',
    'Admin' => 'common',
    'User_profil' => 'common',
    'Map_modif' => 'common',
    'place_detail' => 'common',
    'places_manager' => 'common',
    'Ideas' => 'common'
);

?>
