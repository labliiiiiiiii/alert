<?php
include_once '../pages/auth_check.php'; // Validate session
// Allow admin and brgyhead users
$allowedUserTypes = ['admin', 'brgyhead'];
check_auth($allowedUserTypes);

include_once '../component/popupmsg.php';

// Start the session at the beginning of the page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Now you can access $_SESSION variables
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDRRMO Announcement</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../style/saveConfirmation.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../style/archiveConfirmationPopup.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../style/addAnnouncement_MODAL.css?v=<?php echo time(); ?>">

    <!-- Add this to the <head> section -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Add this before the closing </body> tag -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<style>
    /* General Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    /* Body */
    body {
        background-color: #FCFAFB;
        font-family: 'Poppins', sans-serif;
    }
    
    .return-home {
        position: absolute; /* Position the container absolutely */
        top: 20px; /* Add some spacing from the top */
        left: 20px; /* Add some spacing from the left */
        z-index: 10; /* Ensure it appears above other elements */
        font-family: 'Poppins', sans-serif;
    }

    .back-button {
        position: relative;
        font-size: 1em;
        font-weight: 600;
        text-decoration: none;
        color: #2B3467;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .back-button:hover {
        color: #1F1F29;
    }

    .back-button img {
        width: 20px;
        height: auto;
    }

    /* Header Section */
    .headerProfile {
        margin-top: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        position: relative;
    }

    /* Logo Section */
    .header-logo {
        position: absolute;
        top: 40%;
        left: 10%;
        transform: translate(-50%, -50%);
        z-index: 10;
    }

    .header-logo img {
        width: 130px;
        height: 130px;
    }

    /* Profile Info Section */
    .profile-info {
        display: flex;
        flex-direction: column;
        margin-left: 200px;
    }

    .profile-info h1 {
        font-size: 24px;
        font-weight: bold;
    }

    .profile-info p {
        font-size: 14px;
        color: #777;
    }

    /* Actions Section */
    .actions button {
        padding: 13px 20px;
        background-color: #1F1F290D;
        color: #1F1F29B3;
        border: none;
        cursor: pointer;
        font-size: 0.8em;
        font-weight: 600;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .actions button:hover {
        background-color: #1F1F2926;
        
    }

    .iconProfile {
        width: 16px; /* Adjust the size as needed */
        height: 16px; /* Adjust the size as needed */
        margin-right: 5px; /* Add spacing between the icon and text */
        vertical-align: middle; /* Align the icon with the text */
    }

    #date-range-input {
        padding: 12px 20px;
        margin-right: 10px;
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
        max-width: 200px;
        
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
        border: 2px solid #2B3467;
        
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
        margin-right: 50px;
        
    }

    #reset-btn:hover {
        background-color: #1D223F;
        color: white;
    }

    /* Banner Image Section */
    .banner-image {
        width: 100%;
        height: 200px;
        display: flex;
        justify-content: center;
        align-items: center; 
        position: relative;
    }

    .banner-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Announcement Section */
    .announcement-section {
        background-color: #FCFAFB;
        padding: 10px;
        margin-top: 50px;
        margin-left: 70px;
        margin-right: 70px;
        border-radius: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 2px solid rgba(31, 31, 41, 0.15);
        border-radius: 10px;
    }

    .announcement-section .announcement-text {
        display: flex;
        align-items: center;
    }

    .announcement-section .announcement-text img {
        width: 30px;
        height: 30px;
        margin-right: 10px;
    }

    .announcement-section .announcement-text h2 {
        font-size: 0.6em;
        margin: 0;
        font-weight: 500;
        color: rgba(31, 31, 41, 0.7);
    }

    .announcement-section .announcement-text p {
        font-size: 0.8em;
        margin: 0;
        font-weight: 500;
        color: #1F1F29;
    }

    .announcement-section .add-announcement-btn {
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

    .announcement-section .add-announcement-btn:hover {
        background-color: #1D223F;
    }




    .post-section {
        width: auto;
        margin-left: 70px;
        margin-right: 70px;
        margin-top: 30px;
        padding: 0px;
        margin-bottom: 0;
        border-radius: 10px;
        background-color: #FCFAFB;
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

    .announcement .headerPost {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        position: relative;
        margin: 10px;
    }

    .announcement .headerPost img {
        width: 60px;
        height: 60px;
        margin-right: 0px;
    }

    .announcement-header-info {
        display: flex;
        flex-direction: column;
        margin-left: 10px;
    }

    .creator {
        font-size: 1em;
        font-weight: 600;
        color: #1F1F29;
        margin: 0;
    }

    .announcement-date {
        font-size: 0.6em;
        color: rgba(31, 31, 41, 0.7);
        margin: 2px 0 0;
    }

    .caption {
        clear: both;
        margin: 10px 0; /* Adjust the vertical spacing as needed */
        margin-top: 25px;
        margin-bottom: 20px;
        margin-left: 10px;
        font-size: 0.8em;
        font-weight: 400;
        color: #1F1F29;
        line-height: 1.5; /* Proper line spacing */
        text-align: left; /* Aligns text */
        word-wrap: break-word; /* Prevent text overflow */
    }

    .announcement-media-container {
        display: grid;
        gap: 10px;
        justify-content: center;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); /* Dynamically adjust columns */
    }

    .announcement-media-container img,
    .announcement-media-container video {
        width: auto; /* Adjust width as needed */
        height: 80%; /* Maintain aspect ratio */
        border-radius: 8px;
        object-fit: cover;
        margin-top: 10px;
        display: block; /* Ensures element behaves like a block element */
        margin-left: auto; /* Centers content */
        margin-right: auto; /* Centers content */
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

</style>
<body>
    <?php
        // Call the popup function
        displayPopupMessage();
    ?>

    <!-- Banner Image Section -->
    <div class="banner-image">
        <img src="../img/Stay informed, stay prepared.png" alt="Banner Image"> <!-- Replace with actual image -->
    </div>

    <!-- back button to -->
    <div class="return-home">
        <a href="/pages/announcementpage" class="back-button">
            <img src="../img/arrowback.png" alt="Return to Homepage" />
            Return to Announcement Page
        </a>

    </div>

    <!-- Header Section -->
    <div class="headerProfile">
        <div class="header-logo">
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
            <h1><?php echo $_SESSION['fullName']; ?></h1>
            <p>
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
            </p>
        </div>
        <div class="actions">
            <input type="text" id="date-range-input" placeholder="Date Range" readonly>
            <button id="reset-btn">Reset</button>
        </div>
    </div>


    <!-- Announcement Section -->
    <div class="announcement-section">
        <div class="announcement-text">
            <img src="../img/iconpages/announcement.png" alt="Announcement Icon"> <!-- Replace with actual icon -->
            <div>
                <h2>Create Announcement</h2>
                <p>To inform, raise awareness, and guide action.</p>
            </div>
        </div>
        <button class="add-announcement-btn">Add Announcement</button>
    </div>

    <div id="loader" class="loader hidden"></div>

    <!-- Dynamic Announcement Section -->
    <div id="post-section" class="post-section">
        <div id="media-container"></div>
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
            <div class="headerProfile-container">
                
                
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
                        <input type="file" id="imageInput" accept="image/*" multiple style="display: none;">
                        
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

    <script src="../js/announcementProfile.js"></script>

</body>
</html>
