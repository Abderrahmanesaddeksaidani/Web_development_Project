<?php
session_start(); // Start the session to find it
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session itself

// Redirect back to the login page
header("Location: login.php");
exit;
?>