<?php
// Start a PHP session
session_start();

// Destroy the session
session_destroy();

// Redirect to the index.php page after logout
header("Location: index.php");
exit();
?>
