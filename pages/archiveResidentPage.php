<?php
include_once '../pages/auth_check.php';

// Allow only admin users
$allowedUserTypes = ['brgyhead'];
check_auth($allowedUserTypes);

include_once '../server/connect.php';

// Retrieve the logged-in user's userid
$userId = $_SESSION['userid'];  // Correctly accessing the session variable

// Include components and database connection
include '../component/brgysidebar.php'; // Barangay sidebar
include '../component/navbar.php';

// Fetch resident data
include '../server/fetch_residentinfo_archive.php';

// Include the fetch script for barangay contact data
$data = include '../server/fetch_brgconSectionData.php';

// Extract pagination and data details
$barangayContacts = $data['data'];
$total_entries = $data['total_entries'];
$current_page = $data['current_page'];
$entries_per_page = $data['entries_per_page'];

// Calculate the total number of pages
$total_pages = ceil($total_entries / $entries_per_page);

// Get the staff_userid from the session or query string
$staffUserId = isset($_SESSION['userid']) ? $_SESSION['userid'] : (isset($_GET['staffUserId']) ? $_GET['staffUserId'] : null);

// Check if staffUserId is available
if ($staffUserId) {
    // Query to fetch the barangay name based on staff_userid
    $query = "
        SELECT
            barangaytb.brgyid,
            brgystaffinfotb.BrgyName
        FROM
            barangaytb
        JOIN
            brgystaffinfotb
        ON
            barangaytb.staff_userid = brgystaffinfotb.userid
        WHERE
            barangaytb.staff_userid = :staffUserId
    ";

    // Prepare the SQL statement using PDO
    try {
        $stmt = $conn->prepare($query);  // Prepare the query with the PDO connection
        $stmt->bindParam(':staffUserId', $staffUserId, PDO::PARAM_INT);  // Bind the staff_userid to the query
        $stmt->execute();

        // Check if a result was returned
        if ($stmt->rowCount() > 0) {
            // Fetch the result
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $barangayName = $result['BrgyName'];  // Make sure you're accessing the correct column
            $barangayId = $result['brgyid'];  // Make sure you're accessing the correct column
        } else {
            $barangayName = null;
            $barangayId = null;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
    }
} else {
    $barangayName = null;
    $barangayId = null;
}

// Close the database connection (if needed)
$conn = null;
?>
<script>
    // Embed PHP variables in HTML
    window.barangayIdFromSession = <?php echo json_encode($barangayId); ?>;
    window.barangayNameFromSession = <?php echo json_encode($barangayName); ?>;
</script>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Settings</title>

  <link rel="stylesheet" href="../style/brgy.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/SaveConfirmationPopup.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/archiveConfirmationPopup.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/viewResident.css?v=<?php echo time(); ?>">


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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.16/jspdf.plugin.autotable.min.js"></script>




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

      
      <a href="../pages/brgys.php" class="tab" style="text-decoration: none;">List of Residents</a>
      <div class="tab active">Archived Residents</div>
    </div>

    <div class="loob">
      <div class="right">
        <div class="text-container">

          <!-- Print/Export Button with Dropdown -->
          <div class="print-account-btn" onclick="toggleDropdownPE()">
              <img src="../img/plus/printD.png" alt="Add" class="icon"> Print/Export
              <div class="PE-account-dropdown" id="peAccountDropdown">
                  <a href="#" class="print-btn" onclick="printTablePRINT(); return false;">
                      <img src="../img/plus/printD.png" alt="Print Single" class="icon"> Print
                  </a>
                  <a href="#" class="export-btn" onclick="exportToPDF()">
                      <img src="../img/plus/exportPDFD.png" alt="Print Multiple" class="icon"> Export
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
                <th><input type="checkbox" id="select-all" onclick="toggleSelectAll('select-all', '#mainTable')" style="display: none;"></th>
                <th>Full Name</th>
                <th>Sex</th>
                <th>Birthdate</th>
                <th>Age</th>
                <th>Contact Number</th>
                <th>Province</th>
                <th>Municipal</th>
                <th>Barangay</th>
                <th>Address</th>
                <th>Archived Date</th>
                <th> </th>
              </tr>
            </thead>
            <tbody>
                <?php if (!empty($residents)): ?>
                    <?php foreach ($residents as $row): ?>
                        <tr>
                            <td><input type='checkbox' name='resident_ids[]' value='<?= $row['residentid'] ?>' style='display: none;'></td>
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
                            <td><?= $row['archived_at'] ?></td>

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

          <label for="archivedat">Archived Date:</label>
          <input type="text" id="archivedat" name="archivedat" placeholder="Enter archived date" readonly>

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
          <th>Archived Date</th>
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
                      <td><?= $row['archived_at'] ?></td>
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

<script src="../js/archiveResidentPage.js"></script>


</body>
</html>
