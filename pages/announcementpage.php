<?php

include_once '../pages/auth_check.php'; // Validate session
// Allow admin and brgyhead users
$allowedUserTypes = ['admin', 'brgyhead'];
check_auth($allowedUserTypes);

// Start session and include necessary files

include_once '../component/navbar.php';
include_once '../component/popupmsg.php';
include_once '../component/confirmmsgsave.php';
include_once '../component/confirmmsgarchive.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../server/connect.php';

if (!isset($_SESSION['position'])) {
    echo json_encode([
        'error' => 'Unauthorized access!',
        'session' => $_SESSION
    ]);
    exit();
}

// Include the appropriate sidebar based on userType
if ($_SESSION['position'] === 'MDRRMO Cainta') {
    include '../component/adminsidebar.php'; // Admin sidebar
} elseif ($_SESSION['position'] === 'BRGY Staff') {
    include '../component/brgysidebar.php'; // Barangay sidebar
} else {
    // Handle unknown user types
    echo "Invalid user type!";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcement Page</title>

  <link rel="stylesheet" href="../style/saveConfirmation.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/archiveConfirmationPopup.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/addAnnouncement_MODAL.css?v=<?php echo time(); ?>">

  <!-- Add this to the <head> section -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  
  <!-- Add this before the closing </body> tag -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  
  <style>
    body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
        background-color: #FCFAFB;
    }
    .content-wrapper {
        display: flex;
        gap: 0px; /* Optional: Add space between the announcement section and sidebar */
        
        background-color: #FCFAFB;
    }
    .announcement-section {
        flex: 1; /* Allow the announcement section to take remaining space */
        padding: 10px; /* Add padding for spacing */
        box-sizing: border-box; /* Ensure padding/border doesn't affect width */
        margin-right: 0 !important; /* Ensure no left margin */
        
        background-color: #FCFAFB;
    }
    .custom-page .taas .content {
        position: fixed;
        background-color: #FCFAFB;
        margin-left: 250px;
        padding: 0px;
        margin-top: -10px !important;
        padding-top: 25px;
        margin-bottom: 20px;
        z-index: 10;
        width: 100%; /* Make the content responsive */
        max-width: calc(100% - 570px); /* Adjust width to match guidebook-container */
        
    }
    .custom-page .taas .system-settings {
        display: flex;
        align-items: center;
        margin-top: 32px;
        border: 2px solid rgba(31, 31, 41, 0.15);
        border-radius: 10px;
        background-color: #FCFAFB;
        width: auto; /* Inherit width naturally */
        box-sizing: border-box; /* Ensure padding/border doesn't affect width */
        padding: 10px; /* Add padding for spacing */
        margin-right: 0 !important;
    }
    .custom-page .system-settings .action-button {
        padding: 10px 30px;
        background-color: #2B3467;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        font-size: 0.8em;
        font-weight: 600;
        transition: background-color 0.3s ease;
        margin-left: auto;
    }
    .custom-page .system-settings .action-button:hover {
        background-color: #1D223F;
    }

     /* Profile Card Section */
    .profile-card-section {
        position: fixed; /* Fixes the position of the profile card */
        top: 0px;
        right: 0px !important;
        border-radius: 10px;
        z-index: 100;
    }
    /* Profile Card Style */
    .profile-card {
        margin-top: 110px;
        margin-right: 40px;
        border-radius: 10px;
        padding: 20px;
        border: 2px solid rgba(31, 31, 41, 0.15);
        border-radius: 10px;
        background-color: #FCFAFB;
        text-align: center;
    }
    /* Profile Header */
    .profile-header {
        display: flex;
        justify-content: center;
    }
    .profile-logo {
        margin-top: 15px;
        width: 100px;
        height: 100px;
    }
    /* Profile Info */
    .profile-info {
        margin-top: 100px;
    }
    .profile-name {
        font-weight: 600;
        font-size: 1.2em;
        color: #1F1F29;
        margin: 0;
    }
    .profile-subname {
        font-weight: 500;
        font-size: 0.80em;
        color: rgba(31, 31, 41, 0.7);
        margin: 0;
    }
    /* Button Style */
    .view-profile-btn {
        background-color: #2B3467;
        color: white;
        padding: 10px 70px;
        border: none;
        border-radius: 5px;
        margin-top: 50px;
        margin-bottom: 15px;
        cursor: pointer;
        font-size: 0.8em;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;

    }
    .view-profile-btn:hover {
        background-color: #1D223F;
    }


    .post-section {
        max-width: calc(100% - 595px); /* Adjust width to match guidebook-container */
        width: auto;
        
        margin-left: 250px; /* Space on the left */
        
        padding: 0px;
        margin-bottom: 0;
        border-radius: 10px;
        background-color: #FCFAFB;
        margin-top: 70px;
    }
    .announcement {
        display: block;
        overflow: hidden;
        border: 2px solid rgba(31, 31, 41, 0.15);
        border-radius: 10px;
        background-color: #FCFAFB;
        margin-bottom: 20px;
        padding: 20px;
        width: 100%;
    }
    .announcement .header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        
    }
    .announcement .header img {
        width: 60px;
        height: 60px;
        margin-right: 10px;
        margin-top: 0px;
    }
    .creator {
        margin: 0;
        font-size: 1em;
        font-weight: 600;
        color: #1F1F29;
    }
    .announcement-date {
        margin: 2px 0 0;
        font-size: 0.6em;
        color: rgba(31, 31, 41, 0.7);
    }
    .caption {
        clear: both;
        margin: 10px 0; /* Adjust the vertical spacing as needed */
        margin-top: 25px;
        margin-bottom: 20px;
        font-size: 0.8em;
        font-weight: 400;
        color: #1F1F29;
        line-height: 1.5; /* Proper line spacing */
        text-align: left; /* Aligns text */
        word-wrap: break-word; /* Prevent text overflow */
    }
    .announcement img {
        width: 100%;
        border-radius: 8px;
        margin-top: 10px;
    }
    .announcement-media-container {
        display: grid;
        gap: 10px;
        justify-content: center;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); /* Dynamically adjust columns */
    }
    .announcement img{
        width: 100%;
        height: auto; /* Maintain aspect ratio */
        border-radius: 8px;
        object-fit: cover;
    }
    /* Style for the grid when only one media item exists */
    .announcement-media-container.single-item {
        grid-template-columns: 1fr; /* Single column for a single item */
    }
    /* Responsive Design */
    @media (max-width: 768px) {
        .announcement-media-container {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Adjust for smaller screens */
        }
    }
    .no-media {
        text-align: center;
        font-size: 0.6em;
        font-weight: 600;
        color: #555; /* Neutral text color */
        background-color: #f9f9f9; /* Light background */
        padding: 10px;
        margin-top: 10px;
        border: 2px dashed #ccc; /* Dashed border for emphasis */
        border-radius: 8px;
    }
    .menu-container {
        position: absolute;
        top: 0;
        right: 0; /* Align to the top-right corner */
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-right: 10px;
    }
    .menu-container .menu-dots {
        font-size: 1.3em;
        font-weight: bold;
        color: #555;
        user-select: none;
    }
    .menu-container .dropdown-menu {
        position: absolute;
        top: 40px; /* Position the dropdown below the "..." */
        right: 0;
        background-color: #fff;
        border: 2px solid rgba(31, 31, 41, 0.15);
        border-radius: 5px;
        display: none;
        z-index: 10;
        min-width: 110px;
    }
    .menu-container .dropdown-menu.active {
        display: block;
    }
    .menu-container .dropdown-menu a {
        display: block;
        padding: 10px 15px;
        text-decoration: none;
        color: #555;
        font-size: 0.7em;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    .menu-container .dropdown-menu a:hover {
        background-color: #f4f4f4;
        color: #333;
    }

    .sort-by-section {
        margin-left: 260px; /* Align with the announcement section */
        margin-top: 195px;
        margin-bottom: 20px; /* Space between sort-by and announcements */
        display: flex;
        align-items: center;
        gap: 10px; /* Space between label and dropdown */
    }

    #date-range-input {
        font-family: 'Poppins', sans-serif;
        padding: 12px 20px;
        border: 2px solid rgba(31, 31, 41, 0.15);
        border-radius: 5px;
        color: #1F1F29B3;
        background-color: #FCFAFB;
        cursor: pointer;
        font-size: 0.8em;
        font-weight: 600;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        width: 100%;
        max-width: 250px;
        
        resize: none;
        box-sizing: border-box;

        /* Center the placeholder text */
        text-align: center;

        /* Combine the left and right icons into one background-image */
        background-image: url("../img/icons/calendar-icon.png"), url("../img/icons/dropdownD.png");
        background-repeat: no-repeat;
        background-position: 10px center, right 10px center; /* Position the left icon and right icon */
        background-size: 20px, 27px; /* First value (left icon), second value (right icon) */
    }

    #date-range-input:hover {
        background-color: #1F1F290D;
        border-color: #2B3467;
        /* Combine the left and right icons into one background-image */
        background-image: url("../img/icons/calendar-iconS.png"), url("../img/icons/dropdownS.png");
        background-repeat: no-repeat;
        background-position: 10px center, right 10px center; /* Position the left icon and right icon */
        background-size: 20px, 27px; /* First value (left icon), second value (right icon) */
    }

    #date-range-input::placeholder {
        transition: background-color 0.3s ease;
        margin-right: 7px;
    }

    #date-range-input:hover::placeholder {
        color: #1F1F29 !important;
        
    }

    #date-range-input:focus {
        background-color: #FCFAFB; /* Set to your preferred color */
        border: 2px solid #2B3467 !important;
        color: #1F1F29 !important;
        outline: none;
    }

    /* Change background color of the calendar container */
    .flatpickr-calendar {
        font-family: 'Poppins', sans-serif;

        background-color: #FCFAFB; /* Set to your preferred color */
        border-radius: 10px; /* Optional: to round the corners */
        border: 2px solid #1F1F29;
        
    }

    /* Change the text color of the days */
    .flatpickr-day {
        color: #1F1F29; /* Change this to your preferred text color */
        font-size: .80em; /* Adjust the font size as needed */
    }

    /* Highlight selected date */
    .flatpickr-day.selected {
        background-color: #2B3467 !important; /* Your preferred background color */
        color: #FFFFFF !important; /* Your preferred text color */
        border: none;
        font-size: 1em; /* Adjust the font size as needed */
        font-weight: 600;
    }

    /* Change color for the hovered date */
    .flatpickr-day:hover {
        background-color: #1F1F2926; /* Change to your preferred hover color */
        font-size: 1em; /* Adjust the font size as needed */
        font-weight: 600;
    }

    .flatpickr-day.selected:hover {
        background-color: #1D223F !important; /* Darker shade for hover effect */
    }

    /* Remove blue background from range selection */
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #2c3354 !important; /* Change to your desired color */
        color: white !important;
        border: none !important; /* Remove the border */
        
    }

    

    /* Change the color of the current date */
    .flatpickr-day.today {
        color: #1F1F29; /* Change to your preferred text color */
        border: 2px solid #1F1F29;
    }

    /* Change the color of the month dropdown items */
    .flatpickr-monthDropdown-months {
        background-color: #FCFAFB; /* Set to your preferred color */
        color: #1F1F29; /* Set to your preferred text color */
    }

    /* Change the background color of the dropdown when hovering */
    .flatpickr-monthDropdown-months:hover {
        background-color: #FCFAFB; /* Adjust to your hover color */
        color: #1F1F29; /* Change text color when hovering */
        font-weight: 600;
    }


    /* Change the color of the calendar navigation year dropdown */
    .flatpickr-yearDropdown {
        background-color: #FCFAFB; /* Set to your preferred background */
        color: #1F1F29; /* Set to your preferred text color */
    }

    /* Change the color of the month and year text */
    .flatpickr-monthDropdown-months, .flatpickr-yearDropdown {
        color: #1F1F29;    
    }

    /* Change the color of the calendar navigation arrows */
    .flatpickr-prev-month, .flatpickr-next-month {
        color: #1F1F29; /* Change to your preferred color */
    }

    /* Change the font size of the weekdays */
    .flatpickr-weekdays {
        font-family: 'Poppins', sans-serif;
        font-size: .8em; /* Adjust the font size as needed */
    }

    .flatpickr-current-month {
        font-family: 'Poppins', sans-serif;
        font-size: 1.1em; /* Adjust the font size as needed */
    }

    #reset-btn {
        font-family: 'Poppins', sans-serif;
        padding: 13px 20px;
        background-color: #2B3467;
        color: white;
        border: 2px solid  #2B3467;
        border: none;
        cursor: pointer;
        font-size: 0.8em;
        font-weight: 600;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        margin-right: 0px;
        cursor: pointer;
    }

    #reset-btn:hover {
        background-color: #1D223F;
        color: white;
    }

    #archived-btn {
        font-family: 'Poppins', sans-serif;
        background-color: #FCFAFB;
        padding: 0;
        margin: 0;
        margin-left: -10px !important;
        color: #2B3467;
        border: none;
        font-size: 0.8em;
        font-weight: 600;
        display: flex; /* Ensures flex layout */
        align-items: center; /* Vertically aligns the icon and text */
        gap: 0px; /* Space between the icon and the text */
        margin-right: calc(100% - 740px) !important; 
        cursor: pointer;
    }

    #archived-btn:hover {
        color: #1F1F29;
    }



  </style>
</head>
<body class="custompage">
    <div class="custom-page">
        <div class="taas">
            <div class="content-wrapper">
                <!-- Unified Component (Create Announcement Section) -->
                <div class="announcement-section">
                    <?php
                    renderUnifiedComponent(
                        '../img/iconpages/announcement.png', // Icon path
                        'Create Announcement', // Section title
                        'To inform, raise awareness, and guide action.', // Section description
                        'Announcement', // Title (optional)
                        [
                            ['label' => 'Pages', 'link' => '#'],
                            ['label' => 'Announcement', 'link' => '#'],
                        ] // Breadcrumb (optional)
                    );
                    ?>

                    <!-- Sort By Section -->
                    <div class="sort-by-section">
                        <!-- <select id="sortBy" class="sort-by-dropdown">
                            <option value="" disabled selected>Select Creator Type</option>
                            <option value="admin">Admin</option>
                            <option value="brgyhead">Per Barangay (Brgy Head)</option>
                        </select> -->

                        <!-- Archived Button -->
                        <button id="archived-btn" onclick="window.location.href='../pages/archivepage';">Show Archived</button>

                        <input type="text" id="date-range-input" placeholder="Date Range" readonly>
                        <button id="reset-btn">Reset</button>

                    </div>
                    
                    <!-- Dynamic Announcement Section -->
                    <div id="post-section" class="post-section">
                        <div id="media-container"></div>
                    </div>
                    <p>Loading announcements...</p>
                    
                </div>

                <!-- Profile Card Section -->
                <div class="profile-card-section">
                    <div class="profile-card">
                        <div class="profile-header">
                            <?php 
                            // Fetch position and image based on the session data
                            if ($_SESSION['position'] === 'MDRRMO Cainta') {
                                // Static image for admin
                                echo '<img src="../img/LOGO/MDRRMO1.png" alt="Admin Logo" class="profile-logo">';
                            } elseif ($_SESSION['position'] !== 'MDRRMO Cainta') {
                                // Dynamic image for BRGY staff
                                if ($_SESSION['img'] !== 'No Logo Available') {
                                    // Display the profile image if available
                                    echo '<img src="data:image/png;base64,' . $_SESSION['img'] . '" alt="Profile Image" class="profile-logo">';
                                } else {
                                    // Fallback if no image is available
                                    echo '<img src="default-image.png" alt="Profile Image" class="profile-logo">';
                                }
                            }
                            ?>
                        </div>
                        <div class="profile-info">
                            <h3 class="profile-name">
                            <?php
                            // Check if the user is BRGY staff and adjust the position display
                            if ($_SESSION['position'] !== 'MDRRMO Cainta') {
                                // If the user is BRGY staff, display as "Brgy [BrgyName]"
                                echo "BRGY " . $_SESSION['BrgyName'];
                            } else {
                                // If the user is an admin, display "MDRRMO Cainta"
                                echo $_SESSION['position']; // Display "MDRRMO Cainta" for admin
                            }
                            ?>
                            </h3>
                            <p class="profile-subname">
                                <?php
                                echo $_SESSION['fullName']; // Display the full name stored in the session
                                ?>
                            </p>
                        </div>
                        
                        <!-- Button for Redirecting to Profile Page -->
                        <button class="view-profile-btn" onclick="window.location.href='../pages/announcementProfile';">See Profile</button> <!-- Replace with your actual profile page URL -->
                    </div>
                </div>
            </div>



        </div>


    
    </div>




    <!-- Modal for Creating Announcement -->
    <div id="createAnnouncementModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create Announcement</h2>
                <button id="closeModalButton" class="close-button">
                    <img src="../img/plus/closeD.png" alt="Close">
                </button>
            </div>
            <div class="header-container">
                
                
            </div>
            <div class="modal-body">
                <div class="content-container">
                    <textarea id="announcementText" class="announcement-input" placeholder="What's on your mind?"></textarea>
                    <!-- Media Preview Section -->
                    <div id="mediaPreview" class="media-preview"></div>
                </div>
            </div>
            <!-- Sticky Footer -->
            <div class="modal-footer">
                <div class="media-upload">
                    <!-- Image Upload Icon -->
                    <label for="imageInput" class="upload-icon" data-tooltip="Upload Image">
                        <img src="../img/plus/imgD.png" data-hover="../img/plus/imgH.png" alt="Upload Image" class="icon">
                    </label>

                    <!-- File Inputs -->
                    <div id="mediaInputContainer">
                        <input type="file" id="imageInput" accept=".jpg,.jpeg,.png" multiple style="display: none;">
                    </div>


                </div>
                
                <button id="postAnnouncementButton" class="post-button">Post</button>
                
            </div>
        </div>
    </div>

    <!-- Save Confirmation Popup -->
    <div id="popupOverlayMAIN" class="confirmation-overlay" style="display: none;">
        <div class="confirmation-popup">
        <h2>Are you sure you want to save?</h2>
        <p>This action cannot be undone.</p>
        <div class="action-buttons">
            <button id="cancelPopupBtnMAIN" class="cancel-button">Cancel</button>
            <button id="proceedBtnMAIN" class="save-button">Proceed</button>
        </div>
        </div>
    </div>

    <!-- Archive Confirmation Popup -->
    <div id="popupOverlayMAIN_ARCHIVE" class="archive-overlay" style="display: none;">
        <div class="archive-popup">
            <h2>Are you sure you want to archive?</h2>
            <p>This action cannot be undone.</p>
            <div class="action-buttons">
            <button id="cancelPopupBtnMAIN_ARCHIVE" class="cancel-button">Cancel</button>
            <button id="proceedBtnMAIN_ARCHIVE" class="save-button">Proceed</button>
            </div>
        </div>
    </div>

    <script src="../js/announcementpage1.js"></script>
</body>
</html>
