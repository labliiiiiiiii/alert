<?php
session_start();

// Prevent cached pages from being accessed after logout
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

/**
 * Check user authentication and role.
 *
 * @param array $allowedUserTypes List of user types allowed to access the page.
 */
function check_auth($allowedUserTypes) {
    // Check if the user is logged in
    if (!isset($_SESSION['userid'])) {
        header("Location: ../MAIN_LOGIN"); // Redirect to login if not authenticated
        exit();
    }

    // Check if the user's role is in the allowed list
    if (!in_array($_SESSION['usertype'], $allowedUserTypes)) {
        header("Location: ../MAIN_LOGIN");
        exit();
    }
}
?>
