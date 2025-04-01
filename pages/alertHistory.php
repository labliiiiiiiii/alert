<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include components and database connection
include '../component/adminsidebar.php';
include '../component/navbar.php';
include '../server/connect.php'; 

// Set default filters
$barangayFilter = isset($_GET['barangay']) ? $_GET['barangay'] : 'All Barangay';
$alertTypeFilter = isset($_GET['alertType']) ? $_GET['alertType'] : 'All';
$warningLevelFilter = isset($_GET['warningLevel']) ? $_GET['warningLevel'] : 'All Warning Level';
$monthFilter = isset($_GET['month']) ? $_GET['month'] : '0';
$yearFilter = isset($_GET['year']) ? $_GET['year'] : date('Y');
$entriesPerPage = isset($_GET['entries']) ? intval($_GET['entries']) : 5;

// Functions for water level status and color
function getWaterLevelStatus($waterLevel) {
    if ($waterLevel >= 30) return "RED WARNING (Force Evacuation)";
    if ($waterLevel >= 20) return "ORANGE WARNING (Evacuation)";
    if ($waterLevel >= 10) return "YELLOW WARNING (Ready)";
    return "BLUE (Normal)";
}

function getStatusColor($waterLevel) {
    if ($waterLevel >= 30) return "red-warning";
    if ($waterLevel >= 20) return "orange-warning";
    if ($waterLevel >= 10) return "yellow-warning";
    return "blue-warning";
}

// Prepare the query 
$query = "SELECT a.*, a.brgyName
          FROM alerttb a
          LEFT JOIN residentinfo r ON a.brgyName = r.barangay
          WHERE 1=1";

// Apply filters
if ($barangayFilter != 'All Barangay') {
    $query .= " AND a.brgyName = '$barangayFilter'";
}
if ($alertTypeFilter != 'All') {
    $query .= " AND 'Flood Alert' = '$alertTypeFilter'";
}
// Total records query
$countQuery = "SELECT COUNT(*) as total FROM alerttb a LEFT JOIN residentinfo r ON a.brgyName = r.barangay WHERE 1=1";

// Total records query
$countQuery = "SELECT COUNT(*) as total FROM alerttb a LEFT JOIN residentinfo r ON a.brgyName = r.barangay WHERE 1=1";

if ($barangayFilter != 'All Barangay') {
    $countQuery .= " AND a.brgyName = '$barangayFilter'";
}
if ($alertTypeFilter != 'All') {
    $countQuery .= " AND a.alertType = '$alertTypeFilter'";
}
if ($warningLevelFilter != 'All Warning Level') {
    switch ($warningLevelFilter) {
        case "RED WARNING (Force Evacuation)":
            $countQuery .= " AND a.waterLevel >= 30";
            break;
        case "ORANGE WARNING (Evacuation)":
            $countQuery .= " AND a.waterLevel >= 20 AND a.waterLevel < 30";
            break;
        case "YELLOW WARNING (Ready)":
            $countQuery .= " AND a.waterLevel >= 10 AND a.waterLevel < 20";
            break;
        case "BLUE (Normal)":
            $countQuery .= " AND a.waterLevel < 10";
            break;
    }
}
if ($monthFilter != '0') {
    $countQuery .= " AND MONTH(a.dateTime) = '$monthFilter'";
}
if ($yearFilter) {
    $countQuery .= " AND YEAR(a.dateTime) = '$yearFilter'";
}

// Apply warning level filter to main query
if ($warningLevelFilter != 'All Warning Level') {
  switch ($warningLevelFilter) {
      case "RED WARNING (Force Evacuation)":
          $query .= " AND a.waterLevel >= 30";
          break;
      case "ORANGE WARNING (Evacuation)":
          $query .= " AND a.waterLevel >= 20 AND a.waterLevel < 30";
          break;
      case "YELLOW WARNING (Ready)":
          $query .= " AND a.waterLevel >= 10 AND a.waterLevel < 20";
          break;
      case "BLUE (Normal)":
          $query .= " AND a.waterLevel < 10";
          break;
  }
}

if ($monthFilter != '0') {
  $query .= " AND MONTH(a.dateTime) = '$monthFilter'";
}
if ($yearFilter) {
  $query .= " AND YEAR(a.dateTime) = '$yearFilter'";
}

$query .= " ORDER BY a.dateTime DESC LIMIT $entriesPerPage";

$result = $conn->query($query);

// Get all barangays for the filter dropdown
$barangaysQuery = "SELECT brgyid FROM barangaytb ORDER BY brgyid";
$barangaysResult = $conn->query($barangaysQuery);

// Warning levels
$warningLevels = [
    "RED WARNING (Force Evacuation)",
    "ORANGE WARNING (Evacuation)",
    "YELLOW WARNING (Ready)",
    "BLUE (Normal)"
];

// Get alert types for the filter dropdown
$alertTypes = [
    "Flood Alert"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>i-Alert: Flood Monitoring and Alert System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  
  <link rel="stylesheet" href="../style/alertHistory.css?v=<?php echo time(); ?>">
</head>

<body>
<?php
renderUnifiedComponent(
    '../img/iconpages/alerts.png', // $iconPath
    'Alert', // $sectionTitle
    'Provide insights, summaries, or analyses of system performance, operations, or activities.', // $sectionDescription
    'View All Alert History', // $title (optional)
    [
        ['label' => 'Pages', 'link' => '#'],
        ['label' => 'Reports', 'link' => '#'],
    ] // $breadcrumb (optional)
);
?>

<div class="container">
    <div class="tabs">
      <a href="../pages/alertpage" class="tab" style="text-decoration: none;">View All Active Alerts</a>
      <div class="tab active">View All Alert History</div>
    </div>
    

    <div class="loob">
      <div class="tabheader">
        <div class="text-container">
          <h2>Activity Log</h2>
          <p>Records user actions and system events, providing a detailed history for tracking and accountability.</p>
        </div>      

      </div>

      <form action="alertHistory.php" method="GET">
          <div class="filter-row">
          <div class="filter-group">
            <label for="barangay">Barangay</label>
            <select name="barangay" id="barangay">
              <option value="All Barangay">All Barangay</option>
              <?php
              $barangayNames = [
                '1' => 'San Juan',
                '2' => 'San Andres',
                '3' => 'San Isidro',
                '4' => 'San Roque',
                '5' => 'Santo Domingo',
                '6' => 'Santo Niño'
              ];

              if ($barangaysResult && $barangaysResult->rowCount() > 0) {
                  while($row = $barangaysResult->fetch(PDO::FETCH_ASSOC)) {
                      $barangayName = isset($barangayNames[$row['brgyid']]) ? $barangayNames[$row['brgyid']] : $row['brgyid'];
                      $selected = ($barangayFilter == $barangayName) ? 'selected' : '';
                      echo "<option value='".$barangayName."' $selected>".$barangayName."</option>";
                  }
              }
              ?>
            </select>
          </div>
            
            <div class="filter-group">
            <label for="alertType">Alert Type</label>
            <select name="alertType" id="alertType">
                <option value="All">All</option>
                <?php
                foreach ($alertTypes as $type) {
                    $selected = ($alertTypeFilter == $type) ? 'selected' : '';
                    echo "<option value='$type' $selected>$type</option>";
                 }
                 ?>
            </select>
            </div>
            
            <div class="filter-group">
                <label for="warningLevel">Warning Level</label>
                <select name="warningLevel" id="warningLevel">
                    <option value="All Warning Level">All Warning Level</option>
                    <?php
                    foreach ($warningLevels as $level) {
                        $selected = ($warningLevelFilter == $level) ? 'selected' : '';
                        echo "<option value='$level' $selected>$level</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
              <label for="month">Month:</label>
              <select name="month" id="month">
                <option value="0" <?php echo ($monthFilter == '0') ? 'selected' : ''; ?>>All</option>
                <option value="01" <?php echo ($monthFilter == '01') ? 'selected' : ''; ?>>January</option>
                <option value="02" <?php echo ($monthFilter == '02') ? 'selected' : ''; ?>>February</option>
                <option value="03" <?php echo ($monthFilter == '03') ? 'selected' : ''; ?>>March</option>
                <option value="04" <?php echo ($monthFilter == '04') ? 'selected' : ''; ?>>April</option>
                <option value="05" <?php echo ($monthFilter == '05') ? 'selected' : ''; ?>>May</option>
                <option value="06" <?php echo ($monthFilter == '06') ? 'selected' : ''; ?>>June</option>
                <option value="07" <?php echo ($monthFilter == '07') ? 'selected' : ''; ?>>July</option>
                <option value="08" <?php echo ($monthFilter == '08') ? 'selected' : ''; ?>>August</option>
                <option value="09" <?php echo ($monthFilter == '09') ? 'selected' : ''; ?>>September</option>
                <option value="10" <?php echo ($monthFilter == '10') ? 'selected' : ''; ?>>October</option>
                <option value="11" <?php echo ($monthFilter == '11') ? 'selected' : ''; ?>>November</option>
                <option value="12" <?php echo ($monthFilter == '12') ? 'selected' : ''; ?>>December</option>
              </select>
            </div>

            <div class="filter-group">
              <?php
              // Query to get distinct years from the database
              $query = "SELECT DISTINCT YEAR(dateTime) AS year FROM alerttb ORDER BY year DESC";
              $yearsResult = $conn->query($query);
              ?>

              <label for="year">Year:</label>
              <select name="year" id="year">
                  <?php
                  // If there are results, loop through and create an option for each year
                  if ($yearsResult && $yearsResult->rowCount() > 0) {
                      while ($row = $yearsResult->fetch(PDO::FETCH_ASSOC)) {
                          $year = $row['year'];
                          // Check if the current year is selected from GET
                          $selected = ($year == (isset($_GET['year']) ? $_GET['year'] : date('Y'))) ? 'selected' : '';
                          echo "<option value='$year' $selected>$year</option>";
                      }
                  } else {
                      echo "<option value=''>No available years</option>";
                  }
                  ?>
              </select>
            </div>
            
            <div class="filter-group" style="display: flex; align-items: flex-end;">
              <button type="submit" class="apply-btn">Apply</button>
              <button type="button" class="reset-btn" onclick="resetFilters()">Reset</button>
            </div>
          </div>
      </form>

      <div class="entries-row">
        <div class="entries-select">
          <form id="entriesForm" method="GET" style="display:inline;">
            <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
            <input type="hidden" name="barangay" value="<?php echo htmlspecialchars($barangayFilter); ?>">
            <input type="hidden" name="alertType" value="<?php echo htmlspecialchars($alertTypeFilter); ?>">
            <input type="hidden" name="warningLevel" value="<?php echo htmlspecialchars($warningLevelFilter); ?>">
            <input type="hidden" name="month" value="<?php echo htmlspecialchars($monthFilter); ?>">
            <input type="hidden" name="year" value="<?php echo htmlspecialchars($yearFilter); ?>">
              <select name="entries" onchange="document.getElementById('entriesForm').submit()">
                <option value="5" <?php echo ($entriesPerPage == 5) ? 'selected' : ''; ?>>5</option>
                <option value="10" <?php echo ($entriesPerPage == 10) ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo ($entriesPerPage == 25) ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo ($entriesPerPage == 50) ? 'selected' : ''; ?>>50</option>
              </select>
              <span>Entries per page</span>
          </form>
        </div>

        <div class="print-account-btn" onclick="toggleDropdownPE()">
            <img src="../img/plus/printD.png" alt="Add" class="icon"> Print/Export
            <div class="PE-account-dropdown" id="peAccountDropdown">
              <a href="#" class="print-btn" onclick="printTablePRINT(); return false;">
                <img src="../img/plus/printD.png" alt="Print Single" class="icon"> Print
              </a>
              <a href="#" class="export-btn" onclick="exportToPDF()">
                <img src="../img/plus/exportD.png" alt="Print Multiple" class="icon"> Export
              </a>
            </div>
          </div>
      </div>
        

      <table class="alert-table">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAll"></th>
              <th>Barangay</th>
              <th>Alert Type</th>
              <th>Warning Level</th>
              <th>Date</th>
              <th>Timestamp</th>
              <th>Details</th>
              <th>Resident Alert Log</th>
            </tr>
          </thead>
          <tbody>
            <?php
           if ($result && $result->rowCount() > 0) {
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                // Get water level status based on function
                $waterLevel = $row['waterLevel'] ?? 0;
                $warningStatus = getWaterLevelStatus($waterLevel);
                $statusColor = getStatusColor($waterLevel);
                
                // Format date and time
                $dateTime = new DateTime($row['dateTime']);
                $date = $dateTime->format('Y-m-d');
                $time = $dateTime->format('H:i A');
                
                // Set default alert type 
                $alertType = "Flood Alert";
                
                echo "<tr>";
                echo "<td><input type='checkbox' class='rowCheckbox'></td>";
                echo "<td>".$row['brgyName']."</td>";
                echo "<td>".$alertType."</td>";
                echo "<td><span class='warning-badge ".$statusColor."'>".$warningStatus."</span></td>";
                echo "<td>".$date."</td>";
                echo "<td>".$time."</td>";
                echo "<td><img src='../img/eyeH.png' alt='Eye Icon' class='eye-icon' onclick='showDetails(".$row['alertid'].", \"".
                    $row['brgyName']."\", \"".
                    $alertType."\", \"".
                    $warningStatus."\", \"".
                    $date."\", \"".
                    $time."\")'></td>";
                echo "<td><button class='view-btn' onclick='showResidentLog(".$row['alertid'].")'>View Resident Log</button></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8' style='text-align: center;'>No alerts found</td></tr>";
        }
            ?>
          </tbody>
      </table>

        <?php
            // Modify the pagination calculation
            $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $entriesPerPage = isset($_GET['entries']) ? intval($_GET['entries']) : 5;

            // Total records query
            $countQuery = "SELECT COUNT(*) as total FROM alerttb a WHERE 1=1";
            if ($barangayFilter != 'All Barangay') {
                $countQuery .= " AND a.brgyName = '$barangayFilter'";
            }
            if ($alertTypeFilter != 'All') {
                $countQuery .= " AND 'Flood Alert' = '$alertTypeFilter'";
            }
            if ($warningLevelFilter != 'All Warning Level') {
                switch ($warningLevelFilter) {
                    case "RED WARNING (Force Evacuation)":
                        $countQuery .= " AND a.waterLevel >= 30";
                        break;
                    case "ORANGE WARNING (Evacuation)":
                        $countQuery .= " AND a.waterLevel >= 20 AND a.waterLevel < 30";
                        break;
                    case "YELLOW WARNING (Ready)":
                        $countQuery .= " AND a.waterLevel >= 10 AND a.waterLevel < 20";
                        break;
                    case "BLUE (Normal)":
                        $countQuery .= " AND a.waterLevel < 10";
                        break;
                }
            }
            if (!empty($dateFilter)) {
                $countQuery .= " AND DATE(a.dateTime) = '$dateFilter'";
            }

            $countResult = $conn->query($countQuery);
            $row = $countResult->fetch(PDO::FETCH_ASSOC);
            $totalRecords = $row['total'];
            $totalPages = ceil($totalRecords / $entriesPerPage);

            // Modify the query to include pagination
            $offset = ($currentPage - 1) * $entriesPerPage;
            $query .= " LIMIT $entriesPerPage OFFSET $offset";
        ?>

        <!-- Pagination Section -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination-wrapper">
            <div class="pagination-container">
              <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <a href="?page=<?php echo $i; ?>&entries=<?php echo $entriesPerPage; ?>&barangay=<?php echo urlencode($barangayFilter); ?>&alertType=<?php echo urlencode($alertTypeFilter); ?>&warningLevel=<?php echo urlencode($warningLevelFilter); ?>&month=<?php echo urlencode($monthFilter); ?>&year=<?php echo urlencode($yearFilter); ?>" <?php if ($i == $currentPage) echo 'class="active"'; ?>><?php echo $i; ?></a>
                <?php endfor; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
                
    
    </div>

    <!-- Alert Details Modal -->
    <div id="detailsModal" class="modal">
      <div class="modal-content">

        <div class="modal-header">
          <h3 id="modalTitle">Flood Alert</h3>
          <p id="modalLocation">Location: <strong><span id="detailBarangay"></span></strong></p>
          <p id="modalDate">Issued on: <strong><span id="modalDateTime"></span></strong></p>
          

          <button id="closeModalButton" class="close-button" onclick="closeModal('detailsModal')">
            <img src="../img/plus/closeD.png" alt="Close">
          </button>
        </div>

        <div class="modal-body">
          <div id="alertDetails" class="alert-details">
            <h4 id="detailWarningLevel"></h4>
            <p id="detailMessage"></p>
          </div>
          <div>
            <p><strong>Water Level:</strong> <span id="detailWaterLevel"></span> cm</p>
            <p><strong>Humidity:</strong> <span id="detailHumidity"></span>%</p>
            <p><strong>Temperature:</strong> <span id="detailTemperature"></span>°C</p>
          </div>
        </div>
      </div>
    </div>


    <!-- Resident Alert Log Modal -->
    <div id="residentLogModal" class="modal">
      <div class="modal-content-alertlog">
        <div class="modal-header">
          <h2>Resident Alert Log</h2>
          

          <button id="closeModalButton" class="close-button" onclick="closeModal('residentLogModal')">
            <img src="../img/plus/closeD.png" alt="Close">
          </button>
        </div>
        <div class="filter-row">
          <div class="filter-group">
            <label for="tagFilter">Tags</label>
            <select id="tagFilter">
              <option value="All Tags">All Tags</option>
              <option value="Along River">Along River</option>
              <option value="Low-lying Area">Low-lying Area</option>
              <option value="Emergency Route">Emergency Route</option>
              <option value="Near Coastal Area">Near Coastal Area</option>
              <option value="Wetland Area">Wetland Area</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="deliveryFilter">Delivery Status</label>
            <select id="deliveryFilter">
              <option value="All">All</option>
              <option value="Delivered">Delivered</option>
              <option value="Not Sent">Not Sent</option>
            </select>
          </div>
          <div class="filter-group" style="display: flex; align-items: flex-end;">
            <button class="apply-btn">Apply</button>
          </div>
        </div>
        
        <div class="entries-row">
          <div class="entries-select">
           
          <select name="entries" onchange="document.getElementById('entriesForm').submit()">
                <option value="5" <?php echo ($entriesPerPage == 5) ? 'selected' : ''; ?>>5</option>
                <option value="10" <?php echo ($entriesPerPage == 10) ? 'selected' : ''; ?>>10</option>
                <option value="25" <?php echo ($entriesPerPage == 25) ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo ($entriesPerPage == 50) ? 'selected' : ''; ?>>50</option>
              </select>
              <span>entries per page</span>
          </div>
          
          <div class="print-account-btn" onclick="toggleDropdownPE()">
            <img src="../img/plus/printD.png" alt="Add" class="icon"> Print/Export
            <div class="PE-account-dropdown" id="peAccountDropdown">
              <a href="#" class="print-btn" onclick="printRESIDENTLOG(); return false;">
                <img src="../img/plus/printD.png" alt="Print Single" class="icon"> Print
              </a>
              <a href="#" class="export-btn" onclick="exportToPDF_RESIDENTLOG()">
                <img src="../img/plus/exportD.png" alt="Print Multiple" class="icon"> Export
              </a>
            </div>
          </div>
        </div>
        
        <table class="modal-table">
          <thead>
            <tr>
              <th>Resident Name</th>
              <th>Address</th>
              <th>Contact Number</th>
              <th>Alert Type</th>
              <th>Warning Level</th>
              <th>Date</th>
              <th>Delivery Status</th>
            </tr>
          </thead>
          <tbody id="residentLogBody">
            <!-- Resident log data will be loaded here dynamically -->
          </tbody>
        </table>
        
        <div class="pagination" id="residentLogPagination">
          <!-- Pagination info will be loaded here -->
        </div>
      </div>
    </div>


















  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>

  <script>
      // Select all checkboxes
      document.getElementById('selectAll').addEventListener('change', function() {
        var checkboxes = document.getElementsByClassName('rowCheckbox');
        for (var i = 0; i < checkboxes.length; i++) {
          checkboxes[i].checked = this.checked;
        }
      });
    
    function showDetails(alertid, barangay, alertType, warningLevel, date, time) {
        var modal = document.getElementById('detailsModal');
        var modalBarangay = document.getElementById('detailBarangay');
        var detailWarningLevel = document.getElementById('detailWarningLevel');
        var detailMessage = document.getElementById('detailMessage');
        var alertDetails = document.getElementById('alertDetails');

        // Set default values for missing data
        var waterLevel = 0;
        var humidity = 0;
        var temperature = 0;

        // Extract water level from warning level
        if (warningLevel.includes('RED')) {
            waterLevel = 30;
        } else if (warningLevel.includes('ORANGE')) {
            waterLevel = 20;
        } else if (warningLevel.includes('YELLOW')) {
            waterLevel = 10;
        } else {
            waterLevel = 5;
        }

        // Set modal content
        modalBarangay.textContent = barangay;
        document.getElementById('modalDateTime').textContent = date + ' ' + time;
        detailWarningLevel.textContent = warningLevel;
        document.getElementById('detailWaterLevel').textContent = waterLevel;
        document.getElementById('detailHumidity').textContent = humidity;
        document.getElementById('detailTemperature').textContent = temperature;

        // Determine message based on warning level
        var message = '';
        var statusColor = '';

        if (warningLevel.includes('RED')) {
            message = 'Water levels have rapidly risen to dangerous levels, threatening to flood critical areas. Immediate evacuation is necessary to ensure safety. Stay updated and follow local emergency guidelines.';
            statusColor = 'red';
        } else if (warningLevel.includes('ORANGE')) {
            message = 'Water levels are rising significantly. Evacuation may be necessary in low-lying areas. Be prepared to relocate if instructed by local authorities.';
            statusColor = 'orange';
        } else if (warningLevel.includes('YELLOW')) {
            message = 'Water levels are elevated. Stay vigilant and prepare for possible evacuation if levels continue to rise. Monitor official announcements closely.';
            statusColor = 'yellow';
        } else {
            message = 'Water levels are within normal range. No immediate flood risk, but continue to monitor updates during rainy season.';
            statusColor = 'blue';
        }

        detailMessage.textContent = message;

        // Set appropriate styling for alert box
        alertDetails.className = "alert-details " + statusColor + "-alert";

        // Show the modal
        modal.style.display = "block";

        // Prevent background scrolling
        document.body.classList.add('modal-open');
    }

    // Close modal and allow scrolling again
    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        modal.style.display = 'none';
        
        // Allow background scrolling again
        document.body.classList.remove('modal-open');
    }

    // Show resident log modal
    function showResidentLog(alertid) {
        // Show loading indicator
        document.getElementById('residentLogBody').innerHTML = '<tr><td colspan="7" class="text-center">Loading...</td></tr>';
        document.getElementById('residentLogModal').style.display = 'block';

        // Prevent background scrolling
        document.body.classList.add('modal-open'); // This prevents scrolling on body
        
        // Fetch data from server using AJAX
        fetch('../server/get_resident_log.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                alertid: alertid
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('residentLogBody').innerHTML = '<tr><td colspan="7" class="text-center">Error: ' + data.error + '</td></tr>';
                return;
            }

            if (data.residents.length === 0) {
                document.getElementById('residentLogBody').innerHTML = '<tr><td colspan="7" class="text-center">No residents found for this alert</td></tr>';
                return;
            }

            var html = '';
            for (var i = 0; i < data.residents.length; i++) {
                var resident = data.residents[i];
                html += '<tr>';
                html += '<td>' + resident.name + '</td>';
                html += '<td>' + resident.address + '</td>';
                html += '<td>' + resident.contact + '</td>';
                html += '<td>Flood Alert</td>'; // Fixed alert type
                html += '<td>' + resident.warningLevel + '</td>';
                html += '<td>' + resident.date + '</td>';
                html += '<td>' + resident.status + '</td>';
                html += '</tr>';
            }

            document.getElementById('residentLogBody').innerHTML = html;
            document.getElementById('residentLogPagination').innerHTML = 'Showing 1 to ' + data.residents.length + ' of ' + data.total + ' entries';
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('residentLogBody').innerHTML = '<tr><td colspan="7" class="text-center">Error fetching data</td></tr>';
        });
    }


    function toggleDropdownPE() {
        var dropdown = document.getElementById("peAccountDropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", function(event) {
        var dropdown = document.getElementById("peAccountDropdown");
        var button = document.querySelector(".print-account-btn");

        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });

    function printTablePRINT() {
    // Create a copy of the table to modify before printing
    var table = document.querySelector(".alert-table").cloneNode(true);
    
    // Check if any rows are selected
    var selectedRows = document.querySelectorAll('.rowCheckbox:checked');
    var hasSelectedRows = selectedRows.length > 0;
    
    // Remove checkboxes and action columns (first column and last two columns)
    var headerRow = table.querySelector("thead tr");
    var rows = table.querySelectorAll("tbody tr");
    
    // Remove the checkbox column and action columns from header
    if (headerRow) {
        headerRow.removeChild(headerRow.cells[0]); // Remove checkbox column
        headerRow.removeChild(headerRow.cells[headerRow.cells.length - 1]); // Remove Resident Alert Log column
        headerRow.removeChild(headerRow.cells[headerRow.cells.length - 1]); // Remove Details column
    }
    
    // Process rows - either keep only selected ones or all of them
    var rowsToKeep = [];
    rows.forEach(function(row, index) {
        // If we have selected rows, only keep those
        var shouldKeepRow = !hasSelectedRows || selectedRows[index]?.checked;
        
        if (shouldKeepRow) {
            // Clone the row before modifying it
            var clonedRow = row.cloneNode(true);
            
            // Remove action columns from this row
            clonedRow.removeChild(clonedRow.cells[0]); // Remove checkbox column
            clonedRow.removeChild(clonedRow.cells[clonedRow.cells.length - 1]); // Remove Resident Alert Log button
            clonedRow.removeChild(clonedRow.cells[clonedRow.cells.length - 1]); // Remove Details icon
            
            rowsToKeep.push(clonedRow);
        }
    });
    
    // Clear the table body
    var tbody = table.querySelector('tbody');
    tbody.innerHTML = '';
    
    // Add only the rows we want to keep
    rowsToKeep.forEach(function(row) {
        tbody.appendChild(row);
    });
    
    // Create a new window for printing
    var printWindow = window.open('', '_blank');
    
    // Add content to the print window
    printWindow.document.write(`
        <html>
        <head>
            <title>Alert History</title>
            <style>
                body { font-family: 'Poppins', sans-serif; padding: 20px; }
                h2 { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
                th { background-color: #f2f2f2; }
                .warning-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
                .red-warning { background-color: #ffcccc; color: #cc0000; }
                .orange-warning { background-color: #ffe0cc; color: #cc6600; }
                .yellow-warning { background-color: #ffffcc; color: #999900; }
                .blue-warning { background-color: #ccf2ff; color: #0077b3; }
            </style>
        </head>
        <body>
            <h2>Alert History Report</h2>
            ${table.outerHTML}
        </body>
        </html>
    `);
    
    // Focus and print
    printWindow.document.close();
    printWindow.focus();
    
    // Add a slight delay to ensure content is loaded before printing
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 500);
}


function exportToPDF() {
    // Initialize jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Extract data from table, excluding checkboxes and action columns
    const headers = [];
    const data = [];
    
    // Get header cells (exclude first and last two columns)
    const headerCells = document.querySelectorAll('.alert-table thead th');
    for (let i = 1; i < headerCells.length - 2; i++) {
        headers.push(headerCells[i].textContent.trim());
    }
    
    // Check if any rows are selected
    const selectedCheckboxes = document.querySelectorAll('.rowCheckbox:checked');
    const hasSelectedRows = selectedCheckboxes.length > 0;
    
    // Get all rows and their checkboxes
    const rows = document.querySelectorAll('.alert-table tbody tr');
    const checkboxes = document.querySelectorAll('.rowCheckbox');
    
    // Process only selected rows or all rows if none are selected
    rows.forEach((row, index) => {
        // Only process this row if either:
        // 1. No rows are selected (export all)
        // 2. This specific row is selected
        if (!hasSelectedRows || checkboxes[index].checked) {
            const rowData = [];
            const cells = row.querySelectorAll('td');
            
            for (let i = 1; i < cells.length - 2; i++) {
                // Add cell content
                rowData.push(cells[i].textContent.trim());
            }
            
            data.push(rowData);
        }
    });
    
    // Add title to PDF
    doc.text('Alert History Report', 14, 15);
    
    // Add table to PDF
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 20,
        theme: 'grid',
        styles: {
            fontSize: 10,
            cellPadding: 3
        },
        headStyles: {
            fillColor: [43, 52, 103],
            textColor: 255
        }
    });
    
    // Save the PDF
    doc.save('alert_history.pdf');
}
    

    function resetFilters() {
        window.location.href = 'alertHistory.php';
    }

  </script>
</body>
</html>
