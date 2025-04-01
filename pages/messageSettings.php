<?php
include_once '../pages/auth_check.php';

// Allow only admin users
$allowedUserTypes = ['admin'];
check_auth($allowedUserTypes);

// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../server/connect.php';

// Include components and database connection
include '../component/adminsidebar.php';
include '../component/navbar.php';

// For mission and vision section
require_once '../server/fetch_mvSectionData.php'; // Include the function

// Check if data is successfully fetched
if (!isset($mvSectionData['error'])) {
    $missionTitle = $mvSectionData['mission_title'] ?? ''; // Matches the schema
    $missionDescription = $mvSectionData['mission_description'] ?? ''; // Matches the schema
    $visionTitle = $mvSectionData['vision_title'] ?? ''; // Matches the schema
    $visionDescription = $mvSectionData['vision_description'] ?? ''; // Matches the schema
} else {
    $missionTitle = '';
    $missionDescription = '';
    $visionTitle = '';
    $visionDescription = '';
    echo "<p class='error-message'>Error: " . htmlspecialchars($mvSectionData['error']) . "</p>";
}
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
      width: calc(100% - 300px);
      margin-left: 260px;
      margin-bottom: 20px;
      padding: 0px;
      border-radius: 10px;
      border: 2px solid rgba(31, 31, 41, 0.15);
      min-height: calc(100vh - 200px);
      height: auto;
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
      display: flex;
    }

    .tabheader {
      display: flex;
      align-items: flex-start;
      margin-bottom: 50px;
      gap: 10px;
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

    .header-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px;
      margin: 20px;
    }

    .right {
      flex: 3;
      display: flex;
      flex-direction: column;
    }

    .text-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-left: 15px;
      gap: 240px;
    }

    .text-content {
      flex-grow: 1;
    }

    .text-content h2 {
      font-size: 0.6em;
      font-weight: 600;
      color: rgba(31, 31, 41, 0.7);
      margin: 0;
    }

    .text-content p {
      font-size: 0.9em;
      margin: 0;
      font-weight: 600;
      color: #1F1F29;
      margin: 0;
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
      display: flex;
      align-items: center;
      gap: 1px;
      margin-top: 10px;
    }

    .add-account-btn img.icon {
      width: 20px;
      height: 20px;
      display: inline-block;
      vertical-align: middle;
    }

    .add-account-btn:hover {
      background-color: #2B3467;
      color: #fff;
    }

    .add-account-btn:hover .icon {
      content: url("../img/plus/penH2.png");
    }

    .form-container {
      margin-left: 0px;
      width: 100%;
      padding-top: 20px;
      padding-left: 20px;
      box-sizing: border-box;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-weight: 600;
      font-size: 0.80em;
      color: #1F1F29;
      margin-bottom: 5px;
    }

    input[type="text"], textarea {
      width: 100%;
      padding: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.75em;
      border: 2px solid rgba(31, 31, 41, 0.15);
      border-radius: 5px;
      outline: none;
      resize: vertical;
      box-sizing: border-box;
      background-color: #FCFAFB;
    }

    textarea {
      height: 80px;
    }

    input:disabled, textarea:disabled {
      background-color: #e9ecef;
      border: 2px solid rgba(31, 31, 41, 0.15);
      cursor: not-allowed;
      opacity: 0.7;
    }

    input:enabled:hover, textarea:enabled:hover {
      background-color: #e9ecef;
      font-weight: 500;
    }

    input:enabled:focus, textarea:enabled:focus {
      outline: none;
      border: 2px solid #2B3467;
      font-weight: 500;
      background-color: #f9f9f9;
    }

    small {
      display: block;
      font-size: 0.75em;
      color: #6c757d;
      margin-top: 5px;
      text-align: right;
    }

    small.warning {
      color: #dc3545;
      font-weight: bold;
    }

    .button-group {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }

    button {
      font-family: 'Poppins', sans-serif;
      padding: 10px 30px;
      font-size: 0.8em;
      font-weight: 600;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .cancel-btn {
      background-color: #FCFAFB;
      color: rgba(31, 31, 41, 0.7);
    }

    .cancel-btn:hover {
      background-color: #e9ecef;
      color: #2B3467;
    }

    .save-btn {
      background-color: #2B3467;
      color: white;
    }

    .save-btn:hover {
      background-color: #1F2947;
    }

    button:disabled {
      background-color: #e9ecef;
      color: #adb5bd;
      cursor: not-allowed;
      border: 1px solid #e9ecef;
      opacity: 0.7;
    }

    button:disabled:hover {
      background-color: #e9ecef;
      color: #adb5bd;
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .confirmation-dialog {
      background: #FCFAFB;
      padding: 20px 20px;
      width: 450px; /* Adjust the width */
      height: 170px; /* Adjust the height */
      border-radius: 10px;
      position: relative;
      text-align: right;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .confirmation-dialog h2 {
      font-size: 1.5em;
      font-weight: 600;
      color: #1F1F29;
      text-align: left;
      margin-bottom: 5px; /* Reduce space below h2 */
      margin-left: 15px;
    }

    .confirmation-dialog p {
      font-size: 0.75em;
      color: #333;
      text-align: left;
      margin-top: 0; /* Remove top margin */
      margin-left: 15px;
    }

    .dialog-actions {
      position: absolute; /* Make container absolutely positioned */
      bottom: 15px; /* Position from the bottom */
      right: 15px; /* Position from the right */
      display: flex;
      gap: 10px; /* Space between buttons */
    }

    .action-button {
      font-family: 'Poppins', sans-serif;
      font-size: 0.75em;
      font-weight: 600;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-bottom: 10px;
    }

    .confirm {
      background-color: #2B3467;
      color: white;
      margin-right: 20px;
    }

    .confirm:hover {
      background-color: #1F2947;
    }

    .cancel {
      background-color: #FCFAFB;
      color: rgba(31, 31, 41, 0.7);
    }

    .cancel:hover {
      background-color: #e9ecef;
      color: #2B3467;
    }

    /* Disable scrolling when overlay is active */
    body.no-scroll {
      overflow: hidden;
    }
  </style>
</head>
<body>
  <?php
    renderUnifiedComponent(
      '../img/iconpages/settings.png',
      'System Settings',
      'System settings allow users to customize preferences, manage permissions, and control system functions.',
      'Barangay Head Account',
      [
        ['label' => 'Pages', 'link' => '#'],
        ['label' => 'Settings', 'link' => '#'],
      ]
    );
  ?>

  <div class="container">
    <div class="tabs">
      <a href="../pages/settingspage.php" class="tab" style="text-decoration: none;">Barangay Head Account</a>
      <a href="../pages/landingeditorpage.php" class="tab" style="text-decoration: none;">Edit Landing Page</a>
      <div class="tab active">Device Settings</div>
    </div>

    <div class="loob">
      <?php include "../component/deviceSettings.php"; ?>

      <div class="right">
        <div class="text-container">
          <div class="text-content">
            <h2>About Cainta</h2>
            <p>Edit the About Cainta Section in Landing Page</p>
          </div>
          <button class="add-account-btn">
            <img src="../img/plus/penD.png" alt="Add" class="icon"> Edit Mission and Vision
          </button>
        </div>

        <div class="form-container">
          <form action="../server/fetch_mvSectionData.php" method="POST" id="mvSectionForm">
            <div class="form-group">
              <label for="missionTitle">Mission Title</label>
              <input
                type="text"
                id="missionTitle"
                name="missionTitle"
                placeholder="Mission Title here..."
                value="<?php echo htmlspecialchars($missionTitle); ?>"
                required>
              <small id="missionTitleCharCount">0/150 characters</small>
            </div>
            <div class="form-group">
              <label for="missionDescription">Mission Description</label>
              <textarea
                id="missionDescription"
                name="missionDescription"
                placeholder="Enter mission description here..."
                maxlength="800"
                required><?php echo htmlspecialchars($missionDescription); ?></textarea>
              <small id="missionDescriptionCharCount">0/800 characters</small>
            </div>
            <div class="form-group">
              <label for="visionTitle">Vision Title</label>
              <input
                type="text"
                id="visionTitle"
                name="visionTitle"
                placeholder="Vision Title here..."
                value="<?php echo htmlspecialchars($visionTitle); ?>"
                required>
              <small id="visionTitleCharCount">0/150 characters</small>
            </div>
            <div class="form-group">
              <label for="visionDescription">Vision Description</label>
              <textarea
                id="visionDescription"
                name="visionDescription"
                placeholder="Enter vision description here..."
                maxlength="800"
                required><?php echo htmlspecialchars($visionDescription); ?></textarea>
              <small id="visionDescriptionCharCount">0/800 characters</small>
            </div>
            <div class="button-group">
              <button type="button" class="cancel-btn">Cancel</button>
              <button type="submit" class="save-btn">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

    <div id="confirmationOverlay" class="overlay" style="display: none;">
        <div class="confirmation-dialog">
            <h2>Are you sure you want to save?</h2>
            <p>This action cannot be undone.</p>
            <div class="dialog-actions">
                <button id="cancelAction" class="action-button cancel">Cancel</button>
                <button id="confirmAction" class="action-button confirm">Confirm</button>
            </div>
        </div>
    </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const editButton = document.querySelector(".add-account-btn");
      const formInputs = document.querySelectorAll("#mvSectionForm input, #mvSectionForm textarea");
      const saveButton = document.querySelector(".save-btn");
      const cancelButton = document.querySelector(".cancel-btn");
      const confirmationOverlay = document.getElementById("confirmationOverlay");
      const confirmAction = document.getElementById("confirmAction");
      const cancelAction = document.getElementById("cancelAction");
      const confirmationDialog = document.querySelector(".confirmation-dialog");

      // Define max character limits
      const charLimits = {
        missionTitle: 50,
        visionTitle: 50,
        missionDescription: 500,
        visionDescription: 500,
      };

      // Disable inputs and buttons by default
      formInputs.forEach(input => input.disabled = true);
      saveButton.disabled = true;
      cancelButton.disabled = true;

      // Enable inputs and buttons when Edit button is clicked
      editButton.addEventListener("click", () => {
        formInputs.forEach(input => input.disabled = false);
        saveButton.disabled = false;
        cancelButton.disabled = false;
      });

      // Cancel button functionality
      cancelButton.addEventListener("click", () => {
        formInputs.forEach(input => input.disabled = true);
        saveButton.disabled = true;
        cancelButton.disabled = true;
        location.reload(); // Reload the page to reset the form
      });

      // Universal character counter function
      function updateCharCount(input, counterId, maxChars) {
        const counter = document.getElementById(counterId);
        const currentLength = input.value.length;

        // Display character count
        counter.textContent = `${currentLength}/${maxChars} characters`;

        // Apply warning class if close to the limit
        counter.classList.toggle("warning", currentLength >= maxChars - 5);

        // Prevent further input when limit is reached
        if (currentLength > maxChars) {
          input.value = input.value.substring(0, maxChars);
        }
      }

      // Add event listeners to inputs
      Object.keys(charLimits).forEach((fieldId) => {
        const input = document.getElementById(fieldId);
        const counterId = fieldId + "CharCount";

        if (input) {
          input.addEventListener("input", (event) => {
            const maxChars = charLimits[fieldId];
            if (event.target.value.length > maxChars) {
              // Prevent further input if the limit is reached
              event.target.value = event.target.value.substring(0, maxChars);
            }
            updateCharCount(input, counterId, maxChars);
          });

          // Initialize counters on page load
          updateCharCount(input, counterId, charLimits[fieldId]);
        } else {
          console.error(`Field not found: ${fieldId}`);
        }
      });

      // Function to check if all required fields are filled
      function checkRequiredFields() {
        let allFilled = true;
        formInputs.forEach(input => {
          if (input.required && input.value.trim() === '') {
            allFilled = false;
          }
        });
        saveButton.disabled = !allFilled;
      }

      // Add input event listeners to check required fields
      formInputs.forEach(input => {
        input.addEventListener("input", checkRequiredFields);
      });

      // Show confirmation dialog when save button is clicked
      saveButton.addEventListener("click", (e) => {
        e.preventDefault(); // Prevent form submission
        document.body.classList.add("no-scroll"); // Disable scrolling
        confirmationOverlay.style.display = "flex";
      });

      // Proceed with form submission
      confirmAction.addEventListener("click", () => {
        document.getElementById("mvSectionForm").submit();
        document.body.classList.remove("no-scroll"); // Enable scrolling
        confirmationOverlay.style.display = "none";
      });

      // Cancel and hide the confirmation dialog
      cancelAction.addEventListener("click", () => {
        document.body.classList.remove("no-scroll"); // Enable scrolling
        confirmationOverlay.style.display = "none";
      });

      // Prevent clicks inside the dialog from closing the overlay
      confirmationDialog.addEventListener("click", (e) => {
        e.stopPropagation();
      });
    });
  </script>
</body>
</html>
