<?php
include '../server/connect.php';
include_once '../pages/auth_check.php'; // Validate session

// Homepage.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include '../component/navbar.php';

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

// Fetch barangay staff data and pagination information

$data = include '../server/fetchbarangaystaffinfo.php';

$userid = $_SESSION['userid'];
$usertype = $_SESSION['usertype']; // "admin" or "brgyhead"
$brgyid = $_SESSION['BrgyId']; // Only exists for barangay staff
$brgyName = $_SESSION['BrgyName']; // Store Barangay Name

$startDateFilter = $_GET['start_date'] ?? '';
$endDateFilter = $_GET['end_date'] ?? '';
$selectedBrgy = isset($_GET['brgyName']) ? $_GET['brgyName'] : $brgyName; // Default to user's barangay if barangay staff

$conditions = [];
$params = [];

if (!empty($startDateFilter)) {
  $conditions[] = "DATE(dateTime) >= :startDateFilter";
  $params[':startDateFilter'] = $startDateFilter;
}

if (!empty($endDateFilter)) {
  $conditions[] = "DATE(dateTime) <= :endDateFilter";
  $params[':endDateFilter'] = $endDateFilter;
}
// Query to fetch alerts based on user role
// Admin: Show all barangays by default, allow filtering
if ($usertype == "admin") {
  if (!empty($selectedBrgy)) {
      $conditions[] = "brgyName = :brgyName";
      $params[':brgyName'] = $selectedBrgy;
  }
  $sql = "SELECT * FROM alerttb";
} 
// Barangay staff: Only see their assigned barangay
else {
  $conditions[] = "brgyName = :brgyName";
  $params[':brgyName'] = $brgyName;
  $sql = "SELECT * FROM alerttb";
}

// Append conditions if any
if (!empty($conditions)) {
  $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Order results
$sql .= " ORDER BY dateTime DESC";

// Execute query
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch barangays for filter dropdown (Admin only)
if ($usertype == "admin") {
  $brgyStmt = $conn->prepare("SELECT DISTINCT brgyName FROM alerttb ORDER BY brgyName ASC");
  $brgyStmt->execute();
  $barangays = $brgyStmt->fetchAll(PDO::FETCH_COLUMN);
}

// Export CSV functionality
if (isset($_GET['export'])) {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="Alert_Reports.csv"');
  $output = fopen("php://output", "w");
  fputcsv($output, ['Alert ID', 'Water Level', 'Humidity', 'Temperature', 'Brgy Name', 'Date & Time']);

  foreach ($alerts as $alert) {
      fputcsv($output, [$alert['alertid'], $alert['waterLevel'], $alert['humidity'], $alert['temperature'], $alert['brgyName'], $alert['dateTime']]);
  }
  fclose($output);
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>i-Alert: Flood Monitoring and Alert System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
      gap: 5px; /* Space between the icon and the text */
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
      content: url("../img/plus/printH.png"); /* Specify the new icon for hover state */
    }

    .filter-container {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .filter-container label {
        font-family: 'Poppins', sans-serif; /* Set font family */
        font-size: 0.75em;
    }

    .filter-container select, .filter-container input {
      padding: 8px;
      border: 2px solid rgba(31, 31, 41, 0.15);
      background-color: #FCFAFB;
      border-radius: 5px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.75em;
      font-weight: 400;
      width: 200px;
      outline: none;
    }

    .filter-container select:hover, .filter-container input:hover {
      background-color: #e9ecef !important;
      font-weight: 500 !important;
    }

    .filter-container select:focus, .filter-container input:focus {
      background-color: white !important;
      outline: none; /* Remove outline */
      border: 2px solid #2B3467 !important; /* Active border color on focus */
      font-weight: 500; /* Optional: Maintain font weight on focus */
    }

    .filter-container button {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      background-color: #2B3467;
      color: white;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 0.75em;
      cursor: pointer;
      width: 120px;
    }

    .filter-container button:hover {
      background-color: #1f264e;
    }

    
        /* Styling for the Reset button */
    .filter-container .reset-btn {
        border: none;
        border-radius: 4px;
        background-color: #FCFAFB;
        color: #2B3467;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 0.75em;
        cursor: pointer;
        width: 80px;
    }

    .filter-container .reset-btn:hover {
        color: #1F1F29;
        background-color: #FCFAFB;
    }

    .table-container {
      border: 1px solid #ccc;
      overflow: hidden;
      margin-top: 30px;
      padding: 20px;
    }

    .report-summary {
        padding: 20px;
        border-radius: 8px;
        
        margin-top: 20px;
        text-align: center;
    }


    .report-header p {
        font-size: 14px;
        color: #666666;
        margin: 5px 0;
    }


    .report-header h2 {
        font-size: 18px;
        font-weight: 700;
        color: #1F1F29;
        margin: 5px 0;
    }


    .report-header h3 {
        font-size: 14px;
        font-weight: 600;
        color: #1F1F29;
        margin-top: 10px;
    }

    .activity-log-table {
      width: 100%;
      border-collapse: collapse;
    }

    .activity-log-table th, .activity-log-table td {
      border: 1px solid #ccc;
      padding: 12px;
      text-align: left;
      font-family: 'Poppins', sans-serif;
      font-size: .7em;
    }


    .activity-log-table th {
      background-color: #2B3467;
      color: white;
      font-weight: bold;
    }

    /* Hide content by default */
    .AlertLog-header, .tablePRINT-container {
      display: none;
    }

    /* Show content only when printing */
    @media print {
      .AlertLog-header, .tablePRINT-container {
        display: block;
      }
    }

    .PE-account-dropdown {
    display: none;
    position: absolute;
    top: 100%; /* Ensures it appears directly below the button */
    left: 0;
    background-color: #fff;
    border: 2px solid #ddd;
    border-radius: 5px;
    z-index: 1000;
    width: 132px; /* Set width for proper alignment */
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }

    .PE-account-dropdown a {
        display: flex;
        align-items: center;
        padding: 10px;
        color: #1F1F29B3;
        text-decoration: none;
        font-weight: 600;
    }

    .PE-account-dropdown a:hover {
        background-color: #ddd;
        color: #1F1F29;
    }

    .PE-account-dropdown a img.icon {
        width: 20px;
        height: 20px;
        margin-right: 10px;
    }

    /* Ensure button and dropdown are positioned correctly */
    .print-account-container {
        position: relative;
        display: inline-block;
    }

    .print-account-btn {
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
        position: relative;
    }

    .print-account-btn:hover {
        background-color: #2B3467;
        color: #fff;
    }

    /* Default state for the print and export icons */
    .PE-account-dropdown a.print-btn img.icon {
      content: url("../img/plus/printD.png"); /* Default Print icon */
    }

    .PE-account-dropdown a.export-btn img.icon {
      content: url("../img/plus/exportPDFD.png"); /* Default Export icon */
    }

    /* Hover effect for the icons inside the dropdown links */
    .PE-account-dropdown a.print-btn:hover img.icon {
      content: url("../img/plus/printBH.png"); /* Hovered Print icon */
    }

    .PE-account-dropdown a.export-btn:hover img.icon {
      content: url("../img/plus/exportPDFS.png"); /* Hovered Export icon */
    }

    .print-account-btn:hover img.icon {
      content: url("../img/plus/printH.png"); /* Change the icon here */
    }

  </style>
</head>
<body>


<?php
renderUnifiedComponent(
    '../img/iconpages/report.png', // $iconPath
    'Reports', // $sectionTitle
    'Provide insights, summaries, or analyses of system performance, operations, or activities', // $sectionDescription
    'Alert Log', // $title (optional)
    [
        ['label' => 'Pages', 'link' => '#'],
        ['label' => 'Reports', 'link' => '#'],
    ] // $breadcrumb (optional)
);
?>

<div class="container">
    <div class="tabs">
      <a href="../pages/reportpage.php" class="tab" style="text-decoration: none;">Activity Log</a>
      <div class="tab active">Alert Log</div>
    </div>

    <div class="loob">
      <div class="tabheader">
        <div class="text-container">
          <h2>Alert Log</h2>
          <p>Records user actions and system events, providing a detailed history for tracking and accountability.</p>
        </div>
        
        <!-- Print/Export Button with Dropdown -->
        <div class="print-account-btn" onclick="toggleDropdownPE()">
              <img src="../img/plus/printD.png" alt="Add" class="icon"> Print/Export
              <div class="PE-account-dropdown" id="peAccountDropdown">
                  <a href="#" class="print-btn" onclick="printTablePRINT(); return false;">
                      <img src="../img/plus/printD.png" alt="Print Single" class="icon"> Print 
                  </a>
                  <a href="#" class="export-btn" onclick="exportToPDF(); return false;">
                      <img src="../img/plus/exportD.png" alt="Export PDF" class="icon"> Export
                  </a>
              </div>
        </div>
      </div>

      <!-- Filter UI -->
      <div class="filter-container">
        <form method="GET" action="">
          <div class="filter-container">
              <?php if ($usertype == "admin"): ?>
                <select name="brgyName" class="form-select">
                        <option value="">All Barangays</option>
                        <?php foreach ($barangays as $barangay): ?>
                            <option value="<?= htmlspecialchars($barangay) ?>" <?= ($selectedBrgy == $barangay) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($barangay) ?>
                            </option>
                        <?php endforeach; ?>
                </select>
              <?php else: ?>
                <div class="Barangay" style="margin-top: -5px; font-size: .7em;">
                  <p><b>Barangay:</b> <?= htmlspecialchars($brgyName) ?></p>
                  <input type="hidden" name="brgyName" value="<?= htmlspecialchars($brgyName) ?>">
                </div>
              <?php endif; ?>

              <div class="date-filter">
                  <label>Start Date:</label>
                  <input type="date" name="start_date" value="<?= htmlspecialchars($startDateFilter) ?>">

                  <label>End Date:</label>
                  <input type="date" name="end_date" value="<?= htmlspecialchars($endDateFilter) ?>">
              </div>

              <button type="submit">Apply</button>
              <button type="button" class="reset-btn" onclick="resetFilters()">Reset</button>
          </div>
        </form>
      </div>

      <!-- Table Container -->
      <div class="table-container">
        <div class="report-summary">
          <div class="report-header">
              <p>Republic of the Philippines</p>
              <h2>MUNICIPAL OF CAINTA</h2>
              <p>Province of Rizal</p>
              <h3>i-Alert: Flood Monitoring and Alert System - Alert LOG</h3>
          </div>
        </div>

        <table class="activity-log-table">
          <thead>
            <tr>
                <th>Alert ID</th>
                <th>Water Level</th>
                <th>Humidity</th>
                <th>Temperature</th>
                <th>Barangay Name</th>
                <th>Date & Time</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($alerts)): ?>
                <?php foreach ($alerts as $alert): ?>
                      <tr>
                          <td><?= $alert['alertid'] ?></td>
                          <td><?= $alert['waterLevel'] ?></td>
                          <td><?= $alert['humidity'] ?></td>
                          <td><?= $alert['temperature'] ?></td>
                          <td><?= $alert['brgyName'] ?></td>
                          <td><?= $alert['dateTime'] ?></td>
                      </tr>
                  <?php endforeach; ?>
            <!-- Add more rows as needed -->
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No records found.</td>
                </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Table to Print -->
  <?php displayPopupMessage(); ?>
  <div id="printRES">
  <!-- Header for the Printout -->
        <div class="AlertLog-header">
            <p>Republic of the Philippines</p>
            <h2>MUNICIPAL OF CAINTA</h2>
            <p>Province of Rizal</p>
            <h3>i-Alert: Flood Monitoring and Alert System - ALERT LOG</h3>
        </div>

        <div class="tablePRINT-container">
          <table id="mainTablePRINT">
            <thead>
              <tr>
                <th>Alert ID</th>
                <th>Water Level</th>
                <th>Humidity</th>
                <th>Temperature</th>
                <th>Barangay Name</th>
                <th>Date & Time</th>
              </tr>
            </thead>
            <tbody>
            <?php if (!empty($alerts)): ?>
                <?php foreach ($alerts as $alert): ?>
                      <tr>
                          <td><?= $alert['alertid'] ?></td>
                          <td><?= $alert['waterLevel'] ?></td>
                          <td><?= $alert['humidity'] ?></td>
                          <td><?= $alert['temperature'] ?></td>
                          <td><?= $alert['brgyName'] ?></td>
                          <td><?= $alert['dateTime'] ?></td>
                      </tr>
                  <?php endforeach; ?>
            <?php endif; ?>
            <tr id="no-records-row" style="display: none;">
                    <td colspan="5">No records found.</td>
                </tr>
            </tbody>
          </table>
        </div>
      </div>
  </div>

  <script src="../js/reportAlert.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

  <script>
    function resetFilters() {
        // Redirect to 'reportpage.php' inside the 'pages' folder without query parameters
        window.location.href = "../pages/reportpageALERT";
    }
  </script>

</body>
</html>