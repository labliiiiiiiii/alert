<?php
// Start session and include necessary files
include '../component/brgysidebar.php';
include '../component/navbar.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../server/connect.php';
?>