

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

// Fetch resident data
include '../server/fetch_residentinfo.php';

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

  <link rel="stylesheet" href="../style/brgy.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/saveConfirmationPopup.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/archiveConfirmationPopup.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/viewResident.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/addSingleResidents.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/addMultiplesResident.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/addCSVResident.css?v=<?php echo time(); ?>">


  
  <!-- CSS to Hide Header and Table on Main Page, Show on Print -->
  <style>
    /* Hide content by default */
    .residentPRINT-header, .tablePRINT-container {
      display: none;
    }

    /* Show content only when printing */
    @media print {
      .residentPRINT-header, .tablePRINT-container {
        display: block;
      }
    }
    
</style>

</head>
<body>
  <?php
    renderUnifiedComponent(
      '../img/iconpages/settings.png',
      'List of Residents',
      'A list showing the names, locations, and essential details of each resident.',
      'Resident Information',
      [
          ['label' => 'Pages', 'link' => '#'],
          ['label' => 'Residents', 'link' => '#'],
      ]
    );

  ?>

  <div class="container">
    <div class="tabs">
      <div class="tab active">List of Residents</div>
      <a href="../pages/settingspage.php" class="tab" style="text-decoration: none;">Archived Residents</a>
    </div>

    <div class="loob">
      <?php include "../component/loobButton.php"; ?>

      <div class="right">
        <div class="text-container">

          <div class="add-account-btn" onclick="toggleDropdown()">
            <img src="../img/plus/plusD.png" alt="Add" class="icon"> Add Residents
            <div class="add-account-dropdown" id="addAccountDropdown">
              <a href="#" class="add-single-btn">
                <img src="../img/plus/sD.png" alt="Add Single" class="icon"> Add Single
              </a>
              <a href="#" class="add-multiple-btn">
                <img src="../img/plus/mD.png" alt="Add Multiple" class="icon"> Add Multiple
              </a>
            </div>
          </div>

          <button class="archive-account-btn" id="archiveBtn">
            <img src="../img/plus/arcD.png" alt="Add" class="icon"> Archived Resident
          </button>


          <!-- Print/Export Button with Dropdown -->
          <div class="print-account-btn" onclick="toggleDropdownPE()">
              <img src="../img/plus/printD.png" alt="Add" class="icon"> Print/Export
              <div class="PE-account-dropdown" id="peAccountDropdown">
                  <a href="#" class="print-btn" onclick="printTablePRINT(); return false;">
                      <img src="../img/plus/printD.png" alt="Print Single" class="icon"> Print 
                  </a>
                  <a href="#" class="export-btn" onclick="exportToCSV()">
                      <img src="../img/plus/exportD.png" alt="Print Multiple" class="icon"> Export
                  </a>
              </div>
          </div>

          <div class="search-container">
              <input type="text" id="searchInput" placeholder="Search Residents">
              <img id="searchIcon" src="../img/plus/searchD.png" alt="Search Icon">
          </div>

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
          <table id="mainTable">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-all" onclick="toggleSelectAll('select-all', '#mainTable')"></th>
                <th>Full Name</th>
                <th>Sex</th>
                <th>Birthdate</th>
                <th>Age</th>
                <th>Contact Number</th>
                <th>Province</th>
                <th>Municipal</th>
                <th>Barangay</th>
                <th>Address</th>
                <th> </th>
              </tr>
            </thead>
            <tbody>
                <?php if (!empty($residents)): ?>
                    <?php foreach ($residents as $row): ?>
                        <tr>
                            <td><input type='checkbox' name='resident_ids[]' value='<?= $row['residentid'] ?>'></td>
                            <td><?= $row['fullname'] ?></td>
                            <td><?= $row['sex'] ?></td>
                            <td><?= $row['birthdate'] ?></td>
                            <td><?= $row['age'] ?></td>
                            <td><?= $row['contact'] ?></td>
                            <td><?= $row['province'] ?></td>
                            <td><?= $row['municipal'] ?></td>
                            <td data-barangay-id="<?= $row['barangay'] ?>">
                                <?= isset($row['BrgyName']) ? $row['BrgyName'] : 'No Barangay Found' ?>
                            </td>
                            <td><?= $row['address'] ?></td>
                            <td>
                                <button class="view-btn">
                                    <img src="../img/eyeD.png" alt="View">
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr id="no-records-row" style="display: none;">
                    <td colspan="15">No records found.</td>
                </tr>
            </tbody>
          </table>
        </div>

        <div class="footer-container">
          <div class="count">
            <p>Showing <?= count($residents) ?> of <?= $total_entries ?> entries</p>
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

  <div id="viewModal" class="resident-modal">
    <div class="resident-modal-content">
      <div class="resident-modal-header">
        <span class="resident-modal-close" onclick="document.getElementById('viewModal').style.display='none'">
          <img src="../img/plus/closeD.png" alt="Close">
        </span>
        <h2 id="modalTitle">View Resident Information</h2>
      </div>

      <div class="resident-modal-body">
        <form id="viewForm" enctype="multipart/form-data">
          <label for="fullname">Full Name:</label>
          <input type="text" id="fullname" name="fullname" required placeholder="Enter full name" readonly>

          <label for="sex">Sex:</label>
          <input type="text" id="sex" name="sex" required readonly>

          <label for="birthdate">Birthdate:</label>
          <input type="date" id="birthdate" name="birthdate" required readonly>

          <label for="age">Age:</label>
          <input type="number" id="age" name="age" required placeholder="Enter age" readonly>

          <label for="contact">Contact Number:</label>
          <input type="text" id="contact" name="contact" placeholder="Enter contact number" readonly>

          <label for="province">Province:</label>
          <input type="text" id="provinceeme" name="province" placeholder="Enter province" readonly>

          <label for="municipal">Municipal:</label>
          <input type="text" id="municipal" name="municipal" placeholder="Enter municipal" readonly>

          <label for="barangay">Barangay:</label>
          <input type="text" id="barangayeme" name="barangay" placeholder="Enter barangay" readonly>

          <label for="address">Address:</label>
          <textarea id="address" name="address" placeholder="Enter address" readonly></textarea>

          <input type="hidden" id="resident_id" name="id">
        </form>
      </div>

      <div class="resident-modal-footer">
        <div class="left-aligned">
          <span class="no-style" style="display: none;">or</span>
          <a href="javascript:void(0);" class="add-multiple-text" onclick="addResidentsAtOnce()" style="display: none;">Add Multiple Residents at once</a>
        </div>
        <div class="right-aligned">
          <button type="button" class="save-btn" style="display: none;">Submit</button>
        </div>
      </div>

    </div>
  </div>

      <!-- Add Single Modal -->
      <div id="addSingleModal" class="ADDresident-modal">
        <div class="ADDresident-modal-content">
          <div class="ADDresident-modal-header">
            <span class="ADDresident-modal-close" onclick="document.getElementById('addSingleModal').style.display='none'">
              <img src="../img/plus/closeD.png" alt="Close">
            </span>
            <h2 id="addModalTitle">Add Resident Information</h2>
          </div>
          <div class="ADDresident-modal-body">
            

              <label for="addFullname">Full Name:</label>
              <input type="text" id="addFullname" name="fullname" required placeholder="Enter full name">

              <label for="addSex">Sex:</label>
              <select id="addSex" name="sex" required>
                <option value="" disabled selected>Select Sex</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>

              <label for="addBirthdate">Birthdate:</label>
              <input type="date" id="addBirthdate" name="birthdate" required>

              <label for="addAge">Age:</label>
              <input type="number" id="addAge" name="age" required placeholder="Enter age">

              <label for="addContact">Contact Number:</label>
              <input type="text" id="addContact" name="contact" placeholder="Enter contact number" oninput="this.value = this.value.replace(/[^0-9]/g, '')">

              
              <label for="addProvince">Province:</label>
              <input type="text" id="addProvince" name="province" placeholder="Enter province">

              <label for="addMunicipal">Municipal:</label>
              <input type="text" id="addMunicipal" name="municipal" placeholder="Enter municipal">
              
              <label for="addBarangay">Barangay:</label>
              <input type="text" id="addBarangay" name="barangay" placeholder="Enter barangay" readonly>
              <input type="hidden" name="barangay_id" value=""> <!-- Hidden input for barangay ID -->
              
              <label for="addAddress">Address:</label>
              <textarea id="addAddress" name="address" placeholder="Enter address"></textarea>
          </div>
          
          <div class="ADDresident-modal-footer">
            <div class="left-aligned">
              <span class="no-style">or</span>
              <a href="javascript:void(0);" class="add-multiple-text" onclick="openAddMultipleModal()">Add Multiple Residents at once</a>
            </div>
            <div class="right-aligned">
              <button type="button" class="SINGsave-btn" onclick="showSaveConfirmation('single')">Submit</button>
            </div>
          </div>
        </div>
      </div>

  <div id="addMultipleModal" class="multiple-modal">
    <div class="modal-content-multiple">

      <div class="addMultipleModal-header">
        <span class="addMultipleModal-close" onclick="document.getElementById('addMultipleModal').style.display='none'">

          <img src="../img/plus/closeD.png" alt="Close">
        </span>
        <h2 id="modalMULTitle">Add Multiple Residents</h2>
      </div>

      <div class="addMultipleModal-body">
        <div class="resident-form-MUL">
          <div class="MULform-buttons">
            <button type="button" class="add-row-btn">+ Add Row</button>
            <button type="button" class="remove-row-btn">- Remove Row</button>
            <!-- <button type="submit" class="submit-btn">Submit</button> -->
          </div>

          <table id="dynamicTable">
            <thead>
              <tr>
                <th><input type="checkbox" id="select-allMUL" onclick="toggleSelectAll('select-allMUL', '#dynamicTable')"></th>
                <th>Fullname</th>
                <th>Sex</th>
                <th>Birthday</th>
                <th>Age</th>
                <th>Contact No</th>
                <th>Province</th>
                <th>Municipal</th>
                <th>Barangay</th>
                <th>Address</th>
                

              </tr>
            </thead>
            <tbody>
              
            </tbody>
    
          </table>
        </div>
      </div>
      
      <div class="addMultipleModal-footer">
        <div class="MULleft-aligned">
          <span class="MULno-style">or</span>
          <a href="#" class="add-csv-text" onclick="openCSVModal()">Add Multiple Residents by uploading CSV file</a>

        </div>
        <div class="MULright-aligned">
          <button type="button" class="MULsave-btn" onclick="showSaveConfirmation('multiple')">Submit</button>
        </div>
      </div>
      
    </div>
  </div>

  <!-- Modal background (semi-transparent dark overlay) -->
  <div id="csvModal">
    <div class="CSVmodal-container">
        <div class="CSVmodal-header">
            <span class="csv-modal-close" onclick="document.getElementById('csvModal').style.display='none'">
                <img src="../img/plus/closeD.png" alt="Close">
            </span>
            <h2 id="csvTitle">Add Multiple Residents</h2>
        </div>
        <div class="CSVmodal-body">
            <p class="p-description">Create and upload the file.</p>
            <p class="p-details">In this step, please download one of the CSV files provided below, save the file, and use Excel or another application to input the resident's information. Once completed, return here to upload the file.</p>

            <div class="CSVmodal-fileDL">
                <!-- Link for downloading the first CSV template -->
                <a href="../files/template.zip" class="CSVmodal-btnDL" download>Download the CSV Template</a>
                
                <!-- Link for downloading the second CSV template with sample information -->
                <a href="../files/template_with_sample.zip" class="CSVmodal-btnDL" download>Download the CSV Template with sample information</a>
            </div>

            <div class="CSVmodal-file-input">
                <!-- This label is used to trigger the file input when clicked -->
                <label for="fileUpload" class="CSVmodal-btn CSVmodal-btnBROWSE" id="fileLabel">Browse to upload</label>
                <input type="file" id="fileUpload" accept=".csv" style="display: none;" onchange="updateFileName()">
                <label for="fileUpload" class="CSVmodal-btnFILE">Browse</label>
            </div>
        </div>
        <div class="CSVmodal-footer">
            <button class="CSVmodal-btnFOOTER CSVmodal-btn-secondary" id="backButton">Back</button>
            <button type="button" class="CSVmodal-btnFOOTER CSVmodal-btn-primary" onclick="showSaveConfirmation('csv')">Submit</button>
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

<!-- Save Confirmation Popup -->
<div id="popupOverlayMAIN_ARCHIVE" class="archive-overlay" style="display: none;">
  <div class="archive-popup">
    <h2>Are you sure you want to save?</h2>
    <p>This action cannot be undone.</p>
    <div class="action-buttons">
      <button id="cancelPopupBtnMAIN_ARCHIVE" class="cancel-button">Cancel</button>
      <button id="proceedBtnMAIN_ARCHIVE" class="save-button">Proceed</button>
    </div>
  </div>
</div>


<!-- Notification container -->
<div id="notification" class="notification" style="display: none;"></div>



<?php displayPopupMessage(); ?>

<div id="printRES">
  <!-- Header for the Printout -->
  <div class="residentPRINT-header">
      <p>Republic of the Philippines</p>
      <h2>MUNICIPAL OF CAINTA</h2>
      <p>Province of Rizal</p>
      <h3>i-Alert: Flood Monitoring and Alert System - <?= isset($row['BrgyName']) ? $row['BrgyName'] : 'Barangay Not Found' ?></h3>
  </div>

  <!-- Table to Print -->
  <div class="tablePRINT-container">
    <table id="mainTablePRINT">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Sex</th>
          <th>Birthdate</th>
          <th>Age</th>
          <th>Contact No</th>
          <th>Province</th>
          <th>Municipal</th>
          <th>Barangay</th>
          <th>Address</th>
        </tr>
      </thead>
      <tbody>
          <?php if (!empty($residents)): ?>
              <?php foreach ($residents as $row): ?>
                  <tr>
                      <td><?= $row['fullname'] ?></td>
                      <td><?= $row['sex'] ?></td>
                      <td><?= $row['birthdate'] ?></td>
                      <td><?= $row['age'] ?></td>
                      <td><?= $row['contact'] ?></td>
                      <td><?= $row['province'] ?></td>
                      <td><?= $row['municipal'] ?></td>
                      <td data-barangay-id="<?= $row['barangay'] ?>">
                          <?= isset($row['BrgyName']) ? $row['BrgyName'] : 'No Barangay Found' ?>
                      </td>
                      <td><?= $row['address'] ?></td>
                  </tr>
              <?php endforeach; ?>
          <?php endif; ?>
          <tr id="no-records-row" style="display: none;">
              <td colspan="9">No records found.</td>
          </tr>
      </tbody>
    </table>
  </div>
</div>


<script src="../js/brgy.js"></script>

</body>
</html>
