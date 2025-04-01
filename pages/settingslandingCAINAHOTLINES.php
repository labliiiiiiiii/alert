<?php

// Start session and include necessary files

// Include components and database connection
include '../component/adminsidebar.php';
include '../component/navbar.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../server/connect.php';



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Settings</title>
  
  <style>
    body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
        background-color: #FCFAFB;
    }

    .container {
        width: calc(100% - 300px); /* Adjusts width */
        margin-left: 260px; /* Space on the left */
        margin-bottom: 20px; /* Leaves 10px margin at the bottom */
        padding: 0px; /* No internal padding */
        border-radius: 10px; /* Rounded corners */
        border: 2px solid rgba(31, 31, 41, 0.15); /* Border */
        min-height: calc(100vh - 200px); /* Minimum height to ensure it doesn't shrink */
        height: auto; /* Automatically adjusts height based on content */
    }

    .tabs {
      display: flex;
      border-bottom: 2px solid rgba(31, 31, 41, 0.15);
      width: 100%;
      margin: 0;
      padding: 0;
    }

    .tab {
      padding-top: 13px;
      padding-bottom: 3px;
      cursor: pointer;
      text-align: center;
      color: #1F1F29B3;
    }

    .tab.active {
      padding-top: 13px;
      padding-bottom: 3px;
      color: #2B3467;
      border-bottom: 3px solid #2B3467;
      box-sizing: border-box;
    }

    .tab, .tab.active {
      font-size: 0.90em;
      font-weight: 600;
      width: 220px;
    }

    .loob {
      padding: 20px;
    }

    .tabheader {
      display: flex;
      align-items: flex-start;
      margin-bottom: 50px;
      gap: 10px; /* Space between the sidebar and text container */
    }

    .tabheader h2, .tabheader p {
      margin: 0;
      font-weight: 600;
    }

    .tabheader h2 {
      font-size: .60em;
      color: rgba(31, 31, 41, 0.7);
    }

    .tabheader p {
      font-size: 0.90em;
      color: #1F1F29;
      margin-top: 0px;
    }

  </style>

</head>
<body>
    <?php
    renderUnifiedComponent(
        'Edit Landing Page ',
        [
            ['label' => 'Pages', 'link' => '#'],
            ['label' => 'Settings', 'link' => '#'],
        ],
        '../img/iconpages/settings.png',
        'System Settings',
        'System settings allow users to customize preferences, manage permissions, and control system functions.'
    );
    ?>

    <div class="container">
      <div class="tabs">
        <a href="../pages/settingspage.php" class="tab" style="text-decoration: none;">Barangay Head Account</a>
        <div class="tab active">Edit Landing Page</div>
      </div>

      <div class="loob">
      <?php
        include "../component/sidebarloob.php";
      ?>

      </div>
    </div>
</body>
</html>
