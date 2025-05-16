<?php


// To determine if a user has access to a page and/or is displayed, we will check the following:
// 1. all : all users can access the page and it is displayed normally
// 2. admin : only admin users can access the page and it is displayed only for them
// 3. hidden : all users can access the page but it is not displayed

// So far, there is no need to hide a page for admin users
$authorisation = array(
    'Homepage' => 'all',
    'Map' => 'all',
    'Species' => 'all',
    'Specie_add' => 'admin',
    'Races' => 'all',
    'Race_add' => 'admin',
    'Beings_display' => 'hidden',
    'Dimensions' => 'all',
    'Dimension_affichage' => 'all',
    'Dimension_list' => 'all',
    'Character_display' => 'hidden',
    'Characters' => 'all',
    'Character_add' => 'admin',
    'Beings' => 'all',
    'Admin' => 'admin',
    'User_profil' => 'hidden',

);


// To determine if the page is displayed normally or as the branch of another one, we will check the following:
// 1. common : the page is displayed normally
// 2. X : the page is displayed as a branch of the X page

// Other uses can be added later
$type = array(
    'Homepage' => 'common',
    'Map' => 'common',
    'Species' => 'common',
    'Specie_add' => 'common',
    'Races' => 'common',
    'Race_add' => 'common',
    'Beings_display' => 'common',
    'Dimensions' => 'common',
    'Dimension_affichage' => 'Dimensions',
    'Dimension_list' => 'Dimensions',
    'Character_display' => 'common',
    'Characters' => 'common',
    'Character_add' => 'common',
    'Beings' => 'common',
    'Admin' => 'common',
    'User_profil' => 'common',

);

?>
