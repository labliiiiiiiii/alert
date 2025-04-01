<?php
session_start(); // Start the session

// Destroy all session data
session_unset();

// Destroy the session itself
session_destroy();

// Redirect to the login page
header("Location: ../MAIN_LOGIN");
exit();
?>
