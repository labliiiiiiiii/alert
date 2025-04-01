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

// Pagination settings
$entries_per_page = isset($_GET['entries']) ? intval($_GET['entries']) : 5; // Default 5 per page
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $entries_per_page;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchParam = "%$search%";

// Get barangay filter from URL
$barangayId = isset($_GET['barangay']) ? intval($_GET['barangay']) : null;

// Modify the SQL query to include barangay filter
$sql = "
SELECT
    r.*,
    s.BrgyName
FROM
    residentinfo r
LEFT JOIN
    barangaytb b ON r.barangay = b.brgyid
LEFT JOIN
    brgystaffinfotb s ON b.staff_userid = s.userid
WHERE
    r.fullname LIKE :search";

if ($barangayId !== null) {
    $sql .= " AND r.barangay = :barangayId";
}

$sql .= "
ORDER BY
    r.residentid DESC
LIMIT :limit OFFSET :offset
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
if ($barangayId !== null) {
    $stmt->bindParam(':barangayId', $barangayId, PDO::PARAM_INT);
}
$stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total entries for pagination
$total_sql = "SELECT COUNT(*) FROM residentinfo WHERE fullname LIKE :search";
if ($barangayId !== null) {
    $total_sql .= " AND barangay = :barangayId";
}
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
if ($barangayId !== null) {
    $total_stmt->bindParam(':barangayId', $barangayId, PDO::PARAM_INT);
}
$total_stmt->execute();
$total_entries = $total_stmt->fetchColumn();
$total_pages = ceil($total_entries / $entries_per_page);

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Output only the table body and pagination for AJAX requests
    ?>
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
    <div class="footer-container">
        <div class="count">
            <p>Showing <?= count($residents) ?> of <?= $total_entries ?> entries</p>
        </div>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?= $current_page - 1 ?>&entries=<?= $entries_per_page ?><?= $barangayId !== null ? "&barangay=$barangayId" : '' ?>">Previous</a>
            <?php else: ?>
                <a href="#" class="disabled">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&entries=<?= $entries_per_page ?><?= $barangayId !== null ? "&barangay=$barangayId" : '' ?>" class="<?= $current_page == $i ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?= $current_page + 1 ?>&entries=<?= $entries_per_page ?><?= $barangayId !== null ? "&barangay=$barangayId" : '' ?>">Next</a>
            <?php else: ?>
                <a href="#" class="disabled">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    exit;
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Settings</title>

  <link rel="stylesheet" href="../style/brgy.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="../style/saveConfirmation.css?v=<?php echo time(); ?>">
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
      '../img/iconpages/barangays.png',
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
    </div>

    <div class="loob">
      <?php include "../component/loobButton.php"; ?>

      <div class="right">
        <div class="text-container">
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
              <form method="GET" id="searchForm" class="search-container" onsubmit="handleSearchSubmit(event)">
                  <input type="text" name="search" id="searchInput" placeholder="Search Residents" value="<?= htmlspecialchars($search) ?>">
                  <img id="searchIcon" src="../img/plus/searchD.png" alt="Search Icon">
                  <button type="submit" id="searchButton">Search</button>
              </form>
          </div>

        </div>

        <div class="entries-dropdown">
          <form method="GET" id="entriesForm">
            <select id="entries" name="entries" onchange="handleEntriesChange()">
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
                  <a href="?page=<?= $current_page - 1 ?>&entries=<?= $entries_per_page ?><?= $barangayId !== null ? "&barangay=$barangayId" : '' ?>">Previous</a>
              <?php else: ?>
                  <a href="#" class="disabled">Previous</a>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                  <a href="?page=<?= $i ?>&entries=<?= $entries_per_page ?><?= $barangayId !== null ? "&barangay=$barangayId" : '' ?>" class="<?= $current_page == $i ? 'active' : '' ?>"><?= $i ?></a>
              <?php endfor; ?>

              <?php if ($current_page < $total_pages): ?>
                  <a href="?page=<?= $current_page + 1 ?>&entries=<?= $entries_per_page ?><?= $barangayId !== null ? "&barangay=$barangayId" : '' ?>">Next</a>
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

<script src="../js/brgypage.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const searchForm = document.getElementById("searchForm");

    // Clear search input and reset table when clicked, but only if it has a value
    searchInput.addEventListener("click", function () {
        if (this.value.trim() !== "") {
            this.value = "";
            resetTable();
        }
    });

    // Ensure form submits properly
    searchForm.addEventListener("submit", function (event) {
        if (searchInput.value.trim() === "") {
            event.preventDefault(); // Prevent submission if input is empty
            resetTable(); // Reset the table
        }
    });

    // Function to reset the table by removing the search query parameter and reloading the page
    function resetTable() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete('search');
        window.location.search = urlParams.toString();
    }
});

function handleSearchSubmit(event) {
    event.preventDefault();  // Prevent the default form submission

    const searchInput = document.getElementById('searchInput').value.trim();
    const urlParams = new URLSearchParams(window.location.search);
    
    // Add the search query to the URL
    if (searchInput) {
        urlParams.set('search', searchInput);
    } else {
        urlParams.delete('search');  // Remove search if input is empty
    }

    // Ensure other parameters (page, entries, barangay) are preserved
    const entries = document.getElementById('entries') ? document.getElementById('entries').value : null;
    const page = urlParams.get('page') || 1;
    const barangay = urlParams.get('barangay') || null;

    if (entries) urlParams.set('entries', entries);
    if (page) urlParams.set('page', page);
    if (barangay) urlParams.set('barangay', barangay);

    // Update the URL without reloading the page
    window.location.search = urlParams.toString();  // This reloads the page with updated search term and other parameters
}

</script>

</body>
</html>
