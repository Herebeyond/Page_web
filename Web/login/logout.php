<?php
session_start();
session_destroy(); // Delete all session data

// Redirect the user to the home page
header('Location: ../pages/Homepage.php');
exit;
?>