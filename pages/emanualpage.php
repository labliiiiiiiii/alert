<?php
include_once '../pages/auth_check.php'; // Validate session
// Allow admin and brgyhead users
$allowedUserTypes = ['admin', 'brgyhead'];
check_auth($allowedUserTypes);

// Start session and include necessary files
include '../component/navbar.php';
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
    <link rel="stylesheet" href="../style/addCategory_MODAL.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../style/addNewManual_MODAL.css?v=<?php echo time(); ?>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
        .nav-title-wrapper {
            position: fixed;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-left: 240px;
            width: 100%;
            height: 100px; /* Adjust height as needed */
            background-color: #FCFAFB;
            z-index: 10;

        }
        .nav-title-wrapper .navpagesE {
            display: flex;
            align-items: center;
            font-size: 0.75em;
            padding: 20px;
            padding-top: 25px;
            padding-bottom: 0px;
        }
        .nav-title-wrapper .navpagesE a {
            text-decoration: none;
            color: #1F1F29;
            font-weight: 600;
            margin-bottom: 0px;
        }
        .nav-title-wrapper .navpagesE a:first-child {
            font-weight: 400;
        }
        .nav-title-wrapper .navpagesE a:last-child {
            font-weight: 600;
        }
        .nav-title-wrapper .titleE {
            font-size: 1.5em;
            margin: 0;
            font-weight: 600;
            color: #1F1F29;
            margin-top: 0;
            padding-left: 20px;
            padding-top: 0px;
        }
        .content-wrapper {
            display: flex;
            gap: 0px; /* Optional: Add space between the announcement section and sidebar */
        }
        .announcement-section {
            flex: 1; /* Allow the announcement section to take remaining space */
            padding: 10px; /* Add padding for spacing */
            box-sizing: border-box; /* Ensure padding/border doesn't affect width */
            margin-left: 0 !important; /* Ensure no left margin */

        }
        .sidebarE {
            position: fixed;
            width: 260px;
            background-color: #1F1F290D;
            padding: 20px;
            padding-left: 10px;
            padding-right: 10px;
            height: calc(100vh - 200px); /* Adjust height to fit within viewport */
            border-radius: 10px;
            box-sizing: border-box;
            margin-left: 260px;
            margin-top: 110px;
            overflow-y: auto; /* Ensure the sidebar itself can scroll if needed */
        }
        
        .sidebarE .category-list {
            display: flex;
            flex-direction: column;
           
            overflow-y: auto;
            padding-right: 10px; /* Add padding for scrollbar space */
            
        }
        .sidebarE .category-header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            margin-left: 10px;
        }
        .sidebarE .category-header {
            font-size: 1em;
            font-weight: 600;
            color: #1F1F29;
        }
        .sidebarE .add-new-btn {
            font-family: 'Poppins', sans-serif;
            padding: 10px;
            color: #1F1F29;
            font-size: 1em;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .sidebarE .add-new-btn:hover {
            background-color: #2B3467;
            color: white;
        }
        .sidebarE .category-list {
            display: flex;
            flex-direction: column; /* Ensure categories stack vertically */
        }
        .sidebarE .category-list .category-item {
            font-family: 'Poppins', sans-serif;
            flex: 1 1 auto; /* Allow buttons to grow and shrink */
            padding: 10px;
            font-size: 0.8em;
            color: #1F1F29B3;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: left;
            white-space: nowrap;
            overflow: hidden; /* Hide overflow text */
            text-overflow: ellipsis; /* Add ellipsis for overflow text */
            max-width: 100%; /* Ensure buttons don't exceed container width */
        }
        .sidebarE .category-list .category-item:hover {
            background-color: #1D223F;
            color: white;
        }
        /* Add the active class style */
        .sidebarE .category-list .category-item.active {
            background-color: #1D223F;
            color: white;
        }
        .custom-page .taas .content {
            position: fixed;
            margin-left: 530px;
            margin-right: 20px;
            background-color: #FCFAFB;
            margin-top: 0px;
            padding: 0px;
            margin-bottom: 20px;
            z-index: 5;
            width: 100%; /* Make the content responsive */
            max-width: calc(100% - 570px); /* Adjust width to match guidebook-container */
        }
        .custom-page .taas .text p {
            font-size: 0.8em;
            margin: 0;
            font-weight: 500;
            color: #1F1F29;
            margin-right: 50px;
        }
        .custom-page .taas .system-settings {
            display: flex;
            align-items: center;
            border: 2px solid rgba(31, 31, 41, 0.15);
            border-radius: 10px;
            background-color: #FCFAFB;
            margin-top: 100px;
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
            white-space: nowrap; /* Ensure the button text does not wrap */
        }
        .custom-page .system-settings .action-button:hover {
            background-color: #1D223F;
        }
        .guidebook-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            border-radius: 8px;
            border: 2px solid rgba(31, 31, 41, 0.15);
            padding: 10px 20px;
            margin-right: 20px;
            position: relative; /* Keeps the container position relative */
            transition: height 0.3s ease;
            overflow: hidden;
            height: auto; /* Allow the container to expand */
            
            margin-left: 530px;
            margin-bottom: 10px;
        }
        .guidebook-container:first-of-type {
            margin-top: 200px; /* Adjust the margin-top value as needed */
        }
        .file-preview {
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Align items to the left */
            margin-top: 20px;
            width: 100%; /* Make the file preview full width */
            position: relative; /* Necessary for absolute positioning of the button */

        }
        /* Ensure the embed element is also 100% width */
        #pdf-embed {
            width: 100%; /* Full width */
            height: 500px; /* Set a fixed height for the PDF preview */
        }
        .guidebook-container.open {
            height: auto; /* Adjust based on content */
        }
        .guidebook-container.open .file-preview {
            display: block; /* Show the file preview when the container is open */
        }
        .guidebook-container .guidebook-text {
            font-weight: 600;
            color: #1F1F29;
        }
        .guidebook-container .guidebook-text .guidebook-title {
            font-size: 1em;
        }
        .guidebook-container .guidebook-text .guidebook-subtitle {
            font-size: 0.7em;
            color: #1F1F29B3;
        }
        /* Make the dropdown icon fixed, staying at the top-right of the container */
        .guidebook-container .dropdown-icon {
            position: absolute; /* Fix the icon inside the container */
            top: 10px; /* Adjust top position as needed */
            right: 10px; /* Adjust right position as needed */
            cursor: pointer;
            width: 50px;
            height: auto;
            transition: transform 0.3s ease; /* Smooth transition for both adding and removing the rotation */
        }
        .guidebook-container .dropdown-icon.rotated {
            transform: rotate(180deg);
        }
        #open-pdf-button {
            position: absolute; /* Position the button absolutely within the container */
            bottom: 10px; /* Adjust the top position as needed */
            right: 10px; /* Align the button to the right */
            font-family: 'Poppins', sans-serif;
            font-size: 0.8em;
            padding: 10px;
            color: #1D223F;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }

        #open-pdf-button:hover {
            background-color: #1D223F;
            color: white;
        }


        .context-menu {
            display: none;
            position: absolute;
            background-color: white;
            border: 2px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .context-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .context-menu ul li {
            padding: 10px;
            cursor: pointer;
            font-size: 0.7em; /* Add this line to change the font size */
            color: #616168;
            font-weight: 600;
        }
        .context-menu ul li:hover {
            background-color: #1F1F290D;
        }
        /* Style for the editing input field */
        .sidebarE .category-item .editing-input {
            width: 100%;
            padding: 5px;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            box-sizing: border-box;
            background: #FCFAFB;
            color: black;
        }
        

    </style>
</head>
<body class="custompage">
    <div class="nav-title-wrapper">
        <nav class="navpagesE">
            <a href="#">Pages</a>&nbsp;&gt;&nbsp;
            <a href="#">Emergency Manual</a>
        </nav>
        <div class="titleE">All Manual</div>
    </div>
    <div class="custom-page">
        <div class="taas">
            <div class="content-wrapper">
                <div class="sidebarE">
                    <div class="category-header-container">
                        <div class="category-header">Category</div>
                        <!-- Button to add a new manual -->
                        <button class="add-new-btn">+&nbsp;&nbsp;Add New</button>
                    </div>
                    <!-- List of categories (as buttons) -->
                    <div class="category-list">
                        <!-- Categories will be injected here dynamically -->
                    </div>
                </div>

                <div class="announcement-section">
                    <?php
                    renderUnifiedComponent(
                        '../img/iconpages/eman.png', // Icon path
                        'Add Emergency Manual', // Section title
                        'To provide instructions, procedures, and guidelines for managing emergencies effectively.', // Section description
                        '', // Title (optional, empty string)
                        null // Breadcrumb set to null (optional)
                    );
                    ?>
                    <div class="announcementBook">
                        <div class="guidebook-container" id="guidebook-container">
                            <div class="guidebook-text">
                                <div class="guidebook-title">Disaster Preparedness Guidebook</div>
                                <div class="guidebook-subtitle">Materials from Office of Civil Defense</div>
                            </div>
                            <!-- Dropdown Icon -->
                            <img src="../img/drop.png" alt="Dropdown Icon" class="dropdown-icon" id="dropdown-icon">
                            <div id="file-preview" class="file-preview">
                                <embed src="" id="pdf-embed" type="application/pdf">
                                <button id="open-pdf-button">Open PDF in New Tab</button>
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div>
            <?php
            // Render the modal HTML
            renderArchiveConfirmationPopup();
            ?>
        </div>
        <!-- Modal Structure -->
        <div id="addCategoryModal" class="modal">
            <div class="modal-content">
                <!-- Modal Header with Close Button -->
                <div class="modalE-header">
                    <h2>Add New Category</h2>
                    <button id="closeModalButton" class="close-btnE">
                        <img src="../img/plus/closeD.png" alt="Close">
                    </button>
                </div>
                <!-- Modal Body with Form -->
                <form id="categoryForm">
                    <label for="categoryName">Category</label>
                    <input type="text" id="categoryName" name="categoryName" placeholder="Add Category Name here..." required>
                    <!-- Modal Footer with Save Button -->
                    <div class="modalE-footer">
                        <button type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
        


        <!-- Modal Structure -->
        <div id="addManualModal" class="modalNEW">
            <div class="modal-contentNEW">
                <div class="modal-headerNEW">
                    <h2>Add New Manual</h2>
                    <button id="closeModalBtnNEW" class="close-btnNEW">
                        <img src="../img/plus/closeD.png" alt="Close">
                    </button>
                </div>
                <form id="manualForm" class="manualForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="manualTitle">Title</label>
                        <input type="text" id="manualTitle" name="manualTitle" placeholder="Manual Title" required>
                    </div>
                    <div class="form-group">
                        <label for="manualCategory">Category</label>
                        <select id="manualCategory" name="manualCategory" required>
                            <option value="" disabled selected>Select Category...</option>
                            <!-- Categories will be injected dynamically here -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fileUpload">File Preview (PDF only)</label>
                        <input type="file" id="fileUpload" name="fileUpload" class="file-input" accept="application/pdf" required>
                        <div id="pdfPreview" style="margin-top: 20px;">
                            <p>No file selected</p>
                            <embed id="pdfEmbed" src="" type="application/pdf" width="100%" height="500px" style="display:none;">
                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button type="button" class="upload-btn" id="uploadManualBtn">Upload</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/eman.js"></script>
    <?php renderSaveConfirmationPopup(); ?>
    <?php displayPopupMessage(); ?>
</body>
</html>