<?php

include_once '../pages/auth_check.php'; // Validate session

// Example: Restrict certain pages to admins only
if ($_SESSION['usertype'] !== 'admin') {
  header("Location: ../pages/loginpage.php");
  exit();
}

// Homepage.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include components and database connection
include '../component/adminsidebar.php';
include '../component/navbar.php';

// Fetch barangay staff data and pagination information
$data = include '../server/fetchbarangaystaffinfo.php';

// Validate $data structure
$barangayStaff = $data['data'] ?? [];
$total_entries = $data['total_entries'] ?? 0;
$current_page = $data['current_page'] ?? 1;
$entries_per_page = $data['entries_per_page'] ?? 5;

// Safeguard against division by zero
$total_pages = $entries_per_page > 0 ? ceil($total_entries / $entries_per_page) : 1;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>i-Alert: Flood Monitoring and Alert System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../style/settingspage_MODAL.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/saveConfirmation.css?v=<?php echo time(); ?>">
  <style>
    body {
        margin: 0;
        margin-bottom: 0px;
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
      padding-top: 20px;
      padding-left: 20px;
      padding-right: 20px;
      padding-bottom: 20px;
    }

    .tabheader {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 50px;
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

    .tabheader .text-container {
      display: flex;
      flex-direction: column;
    }

    .add-account-btn {
      font-family: 'Poppins', sans-serif;
      background-color: #FCFAFB;
      color: rgba(31, 31, 41, 0.7);
      border: none;
      padding: 10px 14px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.8em;
      font-weight: 600;
      display: flex; /* Ensures flex layout */
      align-items: center; /* Vertically aligns the icon and text */
      gap: 0px; /* Space between the icon and the text */
    }

    .add-account-btn img.icon {
      width: 26px; /* Adjust the size of the icon as needed */
      height: 26px; /* Ensure consistent height */
      display: inline-block;
      vertical-align: middle;
    }

    .add-account-btn:hover {
      background-color: #2B3467;
      color: #fff;
    }

    .add-account-btn:hover img.icon {
      content: url("../img/plus/plusH.png"); /* Specify the new icon for hover state */
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    table thead {
      background-color: #2B3467;
    }

    table th {
      font-weight: 600;
      color: #fff;
    }

    table td {
      font-weight: 400;
    }

    table td input[type="checkbox"], table th input[type="checkbox"] {
      margin: 0;
      padding: 0;
      display: block;
      width: 16px; /* Adjust checkbox size */
      height: 16px;
      margin: auto; /* Center checkbox in cell */
      background-color: #FCFAFB;
    }

    table th, table td {
      height: 40px;
      padding: 5px;
      text-align: left;
      border: 1px solid #ddd;
      font-size: 0.75em;
      vertical-align: middle;
      box-sizing: border-box;
    }

    table th:nth-child(7), table td:nth-child(7) {
      width: 108px;
    }

    button.edit-btn, button.delete-btn {
      display: flex; /* Use flex for centering */
      justify-content: center; /* Center horizontally */
      align-items: center; /* Center vertically */
      border: none; /* Remove any border */
      background: none; /* Remove background */
      padding-left: 15px; /* Remove padding */
      cursor: pointer; /* Show pointer on hover */
      width: 15px; /* Set a consistent width */
      height: 10px; /* Set a consistent height */
    }

    button.edit-btn img, button.delete-btn img {
      width: 20px; /* Set size of the icon */
      height: 20px;
    }

    .edit-btn:hover img {
      content: url("../img/plus/penH.png");
    }

    .delete-btn:hover img {
      content: url("../img/plus/trashS.png");
    }

    .entries-dropdown {
      display: flex;
      align-items: center;
      gap: 5px;
      font-family: 'Poppins', sans-serif;
      font-size: .6em;
      font-weight: 600;
      color: #1F1F29;
      margin-bottom: 8px;
    }

    .entries-dropdown select {
      width: 55px; /* Fixed width for dropdown */
      padding: 4px 8px;
      border: 2px solid #1F1F29B3;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-size: 1em;
      font-weight: 600;
      background: white;
      background-image: url("../img/dropdownD.png"); /* Arrow icon */
      background-size: 13px 13px; /* Set the width and height of the background image */
      background-repeat: no-repeat;
      background-position: right 5px center;
      appearance: none;
      cursor: pointer;
    }

    .entries-dropdown select:focus {
      outline: none;
      border-color: #1F1F29;
      background-image: url("../img/dropdownS.png"); /* Arrow icon */
      background-size: 13px 13px; /* Set the width and height of the background image */
      background-repeat: no-repeat;
      background-position: right 5px center;
      appearance: none;
    }

    .entries-dropdown label {
      margin: 0;
    }

    .footer-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 20px;
    }

    .count p {
      font-size: 0.60em;
      font-weight: 600;
    }

    .count {
      flex: 1; /* Adjusts the count width */
      text-align: left;
    }

    .pagination {
      margin: 20px 0;
      flex: 1; /* Adjusts the pagination width */
      text-align: right;
    }

    .pagination a, .pagination span {
      margin: 0 5px;
      padding: 8px 12px;
      font-size: 0.6em;
      text-decoration: none;
      color: #2B3467;
      font-weight: 600;
      border: 2px solid #ddd;
      border-radius: 4px;
      display: inline-block;
      text-align: center;
    }

    .pagination a:hover:not(.disabled) {
      background-color: #2B3467;
      color: white;
      border-color: #2B3467;
    }

    .pagination .active {
      background-color: #2B3467;
      color: white;
      border-color: #2B3467;
    }

    .pagination a.disabled {
      color: #aaa; /* Lighter color to indicate disabled state */
      cursor: not-allowed;
      border-color: #ddd;
      background-color: #f9f9f9;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 500px;
      border-radius: 10px;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header .close {
      cursor: pointer;
    }

    .modal-body {
      margin-top: 20px;
    }

    .modal-footer {
      text-align: right;
      margin-top: 20px;
    }

    .save-btn {
      background-color: #2B3467;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }

    .save-btn:hover {
      background-color: #1f2937;
    }


    .popup-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: rgba(0, 0, 0, 0.8);
    color: #fff;
    padding: 15px;
    border-radius: 5px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.9em;
    z-index: 10000;
    display: none; /* Hidden by default */
}

.popup-message.success {
    background-color: #4CAF50; /* Green for success */
}

.popup-message.error {
    background-color: #f44336; /* Red for error */
}

    

  </style>
</head>
<body>
<?php
renderUnifiedComponent(
    '../img/iconpages/settings.png', // $iconPath
    'System Settings', // $sectionTitle
    'System settings allow users to customize preferences, manage permissions, and control system functions.', // $sectionDescription
    'Barangay Head Account', // $title (optional)
    [
        ['label' => 'Pages', 'link' => '#'],
        ['label' => 'Settings', 'link' => '#'],
    ] // $breadcrumb (optional)
);
?>

<div class="container">
    <div class="tabs">
      <div class="tab active">Barangay Head Account</div>
      <a href="../pages/landingeditorpage" class="tab" style="text-decoration: none;">Edit Landing Page</a>
    </div>

    <div class="loob">
      <div class="tabheader">
        <div class="text-container">
          <h2>Pending Account</h2>
          <p>See Information about all Barangay Head</p>
        </div>
        <button class="add-account-btn" id="addAccountBtn">
          <img src="../img/plus/plusD.png" alt="Add" class="icon"> Add Barangay Account
        </button>
      </div>

      <div class="entries-dropdown">
        <form method="GET">
          <select id="entries" name="entries" onchange="this.form.submit()">
            <option value="5" <?= $entries_per_page == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $entries_per_page == 10 ? 'selected' : '' ?>>10</option>
            <option value="20" <?= $entries_per_page == 20 ? 'selected' : '' ?>>20</option>
            <option value="50" <?= $entries_per_page == 50 ? 'selected' : '' ?>>50</option>
            <option value="100" <?= $entries_per_page == 100 ? 'selected' : '' ?>>100</option>
          </select>
          <label for="entries">Entries per page</label>
        </form>
      </div>

      <table>
        <thead>
          <tr>
            <th><input type="checkbox"></th>
            <th>User ID</th>
            <th>Barangay</th>
            <th>Fullname</th>
            <th>Username</th>
            <th>Password</th>
            <th>Profile</th>
            <th>Status</th>

          </tr>
        </thead>
        <tbody>
          <?php if (!empty($barangayStaff)): ?>
            <?php foreach ($barangayStaff as $row): ?>
              <tr>
                <td><input type="checkbox"></td>
                <td><?= htmlspecialchars($row['BrgyId'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['BrgyName'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>********</td>
                <td>
                  <?php if (!empty($row['img'])): ?>
                    <a href="data:image/jpeg;base64,<?= base64_encode($row['img']) ?>" download="profile_picture_<?= htmlspecialchars($row['userid'] ?? '', ENT_QUOTES, 'UTF-8') ?>.jpg">Download</a>
                  <?php else: ?>
                    <span>No Image</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>

              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="12">No records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="footer-container">
        <div class="count">
          <p>Showing <?= count($barangayStaff) ?> of <?= $total_entries ?> entries</p>
        </div>

        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?= $current_page - 1 ?>&entries=<?= $entries_per_page ?>">Previous</a>
            <?php else: ?>
                <a href="#" class="disabled">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&entries=<?= $entries_per_page ?>" class="<?= $current_page == $i ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?= $current_page + 1 ?>&entries=<?= $entries_per_page ?>">Next</a>
            <?php else: ?>
                <a href="#" class="disabled">Next</a>
            <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div id="viewModal" class="resident-modal">
    <div class="resident-modal-content">

      <div class="resident-modal-header">
        <span class="resident-modal-close" onclick="document.getElementById('viewModal').style.display='none'">
          <img src="../img/plus/closeD.png" alt="Close">
        </span>
        <h2 id="modalTitle">Add Barangay Account</h2>
      </div>

      <div class="resident-modal-body">
        <form method="POST" action="../server/addBRGYaccount.php" id="editForm" enctype="multipart/form-data">
            <label for="barangay_name">Barangay Name:</label>
            <input type="text" id="barangay_name" name="barangay_name" required placeholder="Enter barangay name">

            <label for="firstname">First Name:</label>
            <input type="text" id="fistname" name="firstname" required placeholder="Enter your firstname">

            <label for="middlename">Middle Name:</label>
            <input type="text" id="middlenameADD" name="middlename" placeholder="Enter your middlename (optional)">

            <label for="surname">Surname:</label>
            <input type="text" id="surnameADD" name="surname" required placeholder="Enter your surname">

            <label for="email">Username:</label>
            <input type="username" id="usernameADD" name="username" required placeholder="Enter your username">

            <label for="password">Password:</label>
            <input type="text" id="password" name="password" required placeholder="Enter your password">
        </form>
      </div>

      <div class="resident-modal-footer">
          <button type="button" class="save-btn" id="saveButton">Submit</button>
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

  <!-- Notification container -->
  <div id="notification" class="popup-message" style="display: none;"></div>


  <?php displayPopupMessage(); ?>

  <script>
    document.getElementById('addAccountBtn').addEventListener('click', function() {
        document.getElementById('viewModal').style.display = 'block';
    });

    window.onclick = function(event) {
        var modal = document.getElementById('viewModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function displayNotification() {
        // Check if there are success or error messages stored in sessionStorage
        const successMessage = sessionStorage.getItem('success');
        const errorMessage = sessionStorage.getItem('error');

        // If success message exists, show the success notification
        if (successMessage) {
            showNotification('success', successMessage);
            sessionStorage.removeItem('success');  // Clear the success message after displaying

        // If error message exists, show the error notification
        } else if (errorMessage) {
            showNotification('error', errorMessage);
            sessionStorage.removeItem('error');  // Clear the error message after displaying
        }
    }

    function showNotification(type, message) {
        const notification = document.getElementById('notification');
        notification.textContent = message;
        
        // Set the notification's class based on the type (success or error)
        notification.className = 'popup-message ' + type;

        // Show the notification
        notification.style.display = 'block';

        // Hide the notification after 3 seconds
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }


    // Main event listener for the save button
    document.addEventListener('DOMContentLoaded', function() {
        const proceedBtn = document.getElementById("proceedBtnMAIN");
        const cancelPopupBtn = document.getElementById("cancelPopupBtnMAIN");
        const popupOverlay = document.getElementById("popupOverlayMAIN");
        const saveBtn = document.getElementById('saveButton'); // Select the save button in the modal

        if (proceedBtn && cancelPopupBtn && popupOverlay && saveBtn) {
            // Show the confirmation popup when the save button is clicked
            saveBtn.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent the form from submitting

                // Get all the input fields to check if they're filled
                const inputs = document.querySelectorAll("#viewModal input");
                let allFilled = true;

                // Loop through all the input fields to check if any is empty
                inputs.forEach(input => {
                    // Skip validation for the middlename input field
                    if (input.name !== "middlename" && input.value.trim() === "") {
                        allFilled = false;
                    }
                });

                if (allFilled) {
                    popupOverlay.style.display = "flex"; // Show the confirmation popup if all fields are filled
                } else {
                    showNotification('error', 'All fields must be filled before proceeding.');
                }
            });

            // Remove any existing event listeners before adding a new one
            proceedBtn.removeEventListener("click", handleProceedClick);
            proceedBtn.addEventListener("click", handleProceedClick);

            function handleProceedClick() {
                document.getElementById('editForm').submit(); // Submit the form
                popupOverlay.style.display = "none"; // Hide popup after saving
            }

            // Cancel the popup and prevent form submission
            cancelPopupBtn.addEventListener("click", () => {
                popupOverlay.style.display = "none"; // Hide the popup on cancel
            });

            // Optional: Function to close the overlay if clicked outside the popup
            popupOverlay.addEventListener("click", (e) => {
                if (e.target === popupOverlay) {
                    popupOverlay.style.display = "none";
                }
            });
        } else {
            console.error('One or more required elements are missing.');
        }
    });

</script>

</body>
</html>
