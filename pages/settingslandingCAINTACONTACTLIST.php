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
include_once '../component/popupmsg.php';

// Include the fetch script for barangay contact data
$data = include '../server/fetch_brgconSectionData.php';

// Extract pagination and data details
$barangayContacts = $data['data'];
$total_entries = $data['total_entries'];
$current_page = $data['current_page'];
$entries_per_page = $data['entries_per_page'];

// Calculate the total number of pages
$total_pages = ceil($total_entries / $entries_per_page);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Settings</title>

  <link rel="stylesheet" href="../style/adminSettingsBrgCon_MODAL.css?v=<?php echo time(); ?>">

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
      margin-right: 20px;
    }

    .text-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-left: 15px;
      width: 100%;
      margin-bottom: 50px;
    }

    .text-content {
      flex-grow: 1;
    }

    .text-content h2 {
      font-size: 0.6em;
      font-weight: 600;
      color: rgba(31, 31, 41, 0.7);
      margin: 0;
      margin-top: 5px;
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
      gap: 5px;
      margin-left: auto;
      margin-top: 5px;
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
      content: url("../img/plus/plusH.png");
    }

    .table-container {
      margin-top: 20px;
      margin-left: 20px;
      width: 100%;
      height: auto;
      overflow: visible;
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
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    table td input[type="checkbox"], table th input[type="checkbox"] {
      margin: 0;
      padding: 0;
      display: block;
      width: 16px;
      height: 16px;
      margin: auto;
      background-color: #FCFAFB;
    }

    table th, table td {
      font-size: .6em;
      height: 40px;
      padding: 5px;
      text-align: left;
      border: 1px solid #ddd;
      vertical-align: middle;
      box-sizing: border-box;
      max-width: 87px;
    }

    table td:nth-child(11),
    table td:nth-child(12) {
      text-align: center;
      vertical-align: middle;
    }

    button.edit-btn, button.delete-btn {
      display: flex;
      justify-content: center;
      align-items: center;
      border: none;
      background: none;
      cursor: pointer;
      width: 15px;
      height: 10px;
    }

    button.edit-btn img, button.delete-btn img {
      width: 20px;
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
      margin-left: 20px;
    }

    .entries-dropdown select {
      width: 55px;
      padding: 4px 8px;
      border: 2px solid #1F1F29B3;
      border-radius: 4px;
      font-family: 'Poppins', sans-serif;
      font-size: 1em;
      font-weight: 600;
      background: white;
      background-image: url("../img/dropdownD.png");
      background-size: 13px 13px;
      background-repeat: no-repeat;
      background-position: right 5px center;
      appearance: none;
      cursor: pointer;
    }

    .entries-dropdown select:focus {
      outline: none;
      border-color: #1F1F29;
      background-image: url("../img/dropdownS.png");
      background-size: 13px 13px;
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
      margin-left: 20px;
      width: calc(100% - 0px);
    }

    .count p {
      font-size: 0.60em;
      font-weight: 600;
    }

    .count {
      flex: 1;
      text-align: left;
    }

    .pagination {
      margin: 20px 0;
      flex: 1;
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
      color: #aaa;
      cursor: not-allowed;
      border-color: #ddd;
      background-color: #f9f9f9;
    }

    .popup-overlay {
      z-index: 2000;
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
      '../img/iconpages/settings.png',
      'System Settings',
      'System settings allow users to customize preferences, manage permissions, and control system functions.',
      'Barangay Contact Settings',
      [
          ['label' => 'Pages', 'link' => '#'],
          ['label' => 'Settings', 'link' => '#'],
      ]
    );

    displayPopupMessage();
  ?>

  <div class="container">
    <div class="tabs">
      <a href="../pages/settingspage" class="tab" style="text-decoration: none;">Barangay Head Account</a>
      <div class="tab active">Edit Landing Page</div>
    </div>

    <div class="loob">
      <?php include "../component/sidebarloob.php"; ?>

      <div class="right">
        <div class="text-container">
          <div class="text-content">
            <h2>Barangay Contact</h2>
            <p>Edit the Contact Us Section in Landing Page</p>
          </div>
          <button class="add-account-btn">
            <img src="../img/plus/plusD.png" alt="Add" class="icon"> Add Barangay Contact
          </button>
        </div>

        <div class="entries-dropdown">
          <form method="GET">
            <select id="entries" name="entries" onchange="this.form.submit()">
              <option value="5" <?= $entries_per_page == 5 ? 'selected' : '' ?>>5</option>
              <option value="10" <?= $entries_per_page == 10 ? 'selected' : '' ?>>10</option>
              <option value="20" <?= $entries_per_page == 20 ? 'selected' : '' ?>>20</option>
              <option value="50" <?= $entries_per_page == 50 ? 'selected' : '' ?>>50</option>
            </select>
            <label for="entries">Entries per page</label>
          </form>
        </div>

        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>ID</th>
                <th>Brg Name</th>
                <th>Brg Head</th>
                <th>Contact No/s</th>
                <th>Email</th>
                <th>Address</th>
                <th>Logo</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($barangayContacts)): ?>
                <?php foreach ($barangayContacts as $row): ?>
                  <tr>
                    <td><input type="checkbox" class="row-checkbox"></td>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['barangay_name']) ?></td>
                    <td><?= htmlspecialchars($row['punong_barangay']) ?></td>
                    <td><?= htmlspecialchars($row['contact_number']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td>
                      <?php if (!empty($row['logo'])): ?>
                        <a href="data:image/jpeg;base64,<?= base64_encode($row['logo']) ?>" download="logo_<?= htmlspecialchars($row['id']) ?>.jpg">Download</a>
                      <?php else: ?>
                        <span>No Logo</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><?= htmlspecialchars($row['updated_at']) ?></td>
                    <td>
                      <button class="edit-btn">
                        <img src="../img/plus/penD.png" alt="Edit">
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="12">No records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="footer-container">
          <div class="count">
            <p>Showing <?= count($barangayContacts) ?> of <?= $total_entries ?> entries</p>
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
  </div>

  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close" onclick="document.getElementById('editModal').style.display='none'">
          <img src="../img/plus/closeD.png" alt="Close">
        </span>
        <h2 id="modalTitle">Edit Barangay Contact</h2>
      </div>

      <div class="modal-body">
        <div class="logo-upload-container">
          <div>
            <label for="current_logo">Current Logo</label>
            <img id="current_logo" src="" alt="Logo" style="display: none;">
            <span id="logoMessage" style="color: red; font-style: italic;"></span>
          </div>

          <div class="upload-button">
            <button type="button" id="browseButton">Browse</button>
          </div>
        </div>

        <form method="POST" action="../server/fetch_homepage_brgcontact_section.php" id="editForm" enctype="multipart/form-data">
          <label for="barangay_name">Barangay Name:</label>
          <input type="text" id="barangay_name" name="barangay_name" required placeholder="Enter barangay name">

          <label for="punong_barangay">Barangay Head Name:</label>
          <input type="text" id="punong_barangay" name="punong_barangay" required placeholder="Enter barangay head name">

          <label for="contact_number">Contact Number:</label>
          <input type="text" id="contact_number" name="contact_number" required placeholder="Enter contact number">

          <label for="email">Email:</label>
          <input type="email" id="email_modal" name="email" required placeholder="Enter email address">

          <label for="address">Address:</label>
          <input type="text" id="address" name="address" required placeholder="Enter address">

          <input type="file" id="logoBRGCON" name="logo" style="display: none;" accept="image/*" onchange="updateLogoPreview()">

          <input type="hidden" id="contact_id" name="id">
        </form>
      </div>

      <div class="modal-footer">
        <button type="submit" class="save-btn" form="editForm" id="submitBtn">Update</button>
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

  <!-- Notification container -->
  <div id="notification" class="popup-message" style="display: none;"></div>


  <?php displayPopupMessage(); ?>

  <script src="../js/admnsettingslandingBRGCON.js"></script>

</body>
</html>
