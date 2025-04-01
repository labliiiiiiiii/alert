<?php
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['userid'])) {
    header("Location: ../MAIN_LOGIN.php");
    exit();
}

// Redirect to the appropriate dashboard for logged-in users
if ($_SESSION['usertype'] === 'admin') {
    header("Location: caintamappage.php"); // Redirect admins
    exit();
} elseif ($_SESSION['usertype'] === 'brgyhead') {
    header("Location: BRGYcaintamappage.php"); // Redirect barangay staff
    exit();
} else {
    header("Location: MAIN_LOGIN.php"); // Fallback for unknown user types
    exit();
}
?>
