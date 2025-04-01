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
include '../server/connect.php'; 

// Fetch only the past week's alerts data from the database
$past_week_date = date('Y-m-d H:i:s', strtotime('-1 week'));
$query = "SELECT a.alertid, a.waterLevel, a.humidity, a.temperature, a.brgyName, a.dateTime 
          FROM alerttb a 
          WHERE a.dateTime >= :past_week_date
          ORDER BY a.dateTime DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':past_week_date', $past_week_date, PDO::PARAM_STR);
    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}


// Add date range variables for display
$week_start = date('M d, Y', strtotime('-1 week'));
$week_end = date('M d, Y');
// Pagination settings
$total_entries = count($alerts);
$entries_per_page = isset($_GET['entries']) ? intval($_GET['entries']) : 5;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$total_pages = ceil($total_entries / $entries_per_page);

// Get subset of alerts for current page
$start_index = ($current_page - 1) * $entries_per_page;
$alerts_page = array_slice($alerts, $start_index, $entries_per_page);

// Function to determine warning level based on water level
function getWaterLevelStatus($waterLevel) {
    if ($waterLevel >= 30) return "RED WARNING (Force Evacuation)";
    if ($waterLevel >= 20) return "ORANGE WARNING (Evacuation)";
    if ($waterLevel >= 10) return "YELLOW WARNING (Ready)";
    return "BLUE (Normal)";
}

// Function to get status color for CSS classes
function getStatusColor($waterLevel) {
    if ($waterLevel >= 30) return "red";
    if ($waterLevel >= 20) return "orange";
    if ($waterLevel >= 10) return "yellow";
    return "blue";
}

// Function to get water level description
function getWaterLevelDescription($waterLevel) {
    if ($waterLevel >= 30) return "Critical Level - Evacuation May Be Needed";
    if ($waterLevel >= 20) return "Rising Level - Prepare for Possible Evacuation";
    if ($waterLevel >= 10) return "Elevated Level - Monitor Closely";
    return "Normal Level";
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

    .entries-selector {
      display: flex;
      align-items: center;
      gap: 5px;
      font-family: 'Poppins', sans-serif;
      font-size: .6em;
      font-weight: 600;
      color: #1F1F29;
      margin-bottom: 8px;
    }
    
    .entries-selector select {
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

    .entries-selector select:focus {
      outline: none;
      border-color: #1F1F29;
      background-image: url("../img/dropdownS.png");
      background-size: 13px 13px;
      background-repeat: no-repeat;
      background-position: right 5px center;
      appearance: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      table-layout: auto; /* Changed from fixed to auto for better responsiveness */
    }

    table th {
      background-color: #2B3467;
      font-weight: 600;
      color: #fff;
      padding: 10px;
    }
    
    table td {
      padding: 10px;
      font-weight: 400;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    table td input[type="checkbox"], table th input[type="checkbox"] {
      margin: 0;
      padding: 0;
      display: block;
      width: 15px;
      height: 15px;
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
    }

    table td:nth-child(1), table th:nth-child(1) {
      width: 30px;
      text-align: center;
      vertical-align: middle;
    }

    table td:nth-child(4), table td:nth-child(7)  {
      width: 50px;
      text-align: center;
      vertical-align: middle;
    }

    .warning-badge {
      padding: 6px 10px;
      border-radius: 4px;
      font-size: 0.8em;
      font-weight: 600;
      white-space: nowrap;
    }

    .red-warning {
      background-color: #ffebee;
      color: #c62828;
      border: 1px solid #ef9a9a;
    }

    .orange-warning {
      background-color: #fff3e0;
      color: #e65100;
      border: 1px solid #ffcc80;
    }

    .yellow-warning {
      background-color: #fffde7;
      color: #f9a825;
      border: 1px solid #fff59d;
    }

    .blue-warning {
      background-color: #e3f2fd;
      color: #1565c0;
      border: 1px solid #90caf9;
    }

    button.details-btn {
      display: flex;
      justify-content: center;
      align-items: center;
      border: none;
      background: none;
      cursor: pointer;
      width: 20px !important;
      height: 20px !important;
      margin: 0 auto;
      padding: 0;
    }

    button.details-btn img {
      width: 20px;
      height: 20px;
    }

    .details-btn:hover img {
      content: url("../img/eyeH.png");
    }

    .pagination-info {
      margin-top: 20px;
      font-size: 0.6em;
      font-weight: 600;
      color: black;
    }

    .pagination {
      margin-top: 20px;
      display: flex;
      justify-content: center;
    }

    .pagination {
      margin-top: 20px;
      display: flex;
      justify-content: center;
    }

    .pagination a {
      padding: 8px 12px;
      margin: 0 4px;
      border-radius: 4px;
      border: 1px solid #ddd;
      color: #2B3467;
      text-decoration: none;
    }

    .pagination a.active {
      background-color: #2B3467;
      color: white;
      border-color: #2B3467;
    }

    .pagination-wrapper {
      display: flex;
      justify-content: flex-end;
      margin-top: 10px;
    }

    .pagination-container {
      max-width: 250px; /* You can adjust */
      overflow-x: auto;
      white-space: nowrap;
      padding: 5px 0;
    }

    .pagination {
      display: inline-flex;
      gap: 4px;
    }

    .pagination a {
      display: inline-block;
      padding: 4px 8px;
      font-size: 12px;
      background-color: #f0f0f0;
      color: #333;
      text-decoration: none;
      border-radius: 4px;
      transition: background-color 0.3s ease;
    }

    .pagination a.active {
      background-color: #2B3467;
      color: #fff;
    }

    .pagination a:hover {
      background-color: #ddd;
    }

    
    body.modal-open {
        overflow: hidden;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
    }

    #alertModal .modal-content {
        background-color: #FCFAFB;
        padding: 20px;
        border-radius: 8px;
        width: 500px;
        position: fixed;  /* Use fixed positioning to make it centered relative to the viewport */
        top: 50%;  /* Position it vertically in the center */
        left: 50%;  /* Position it horizontally in the center */
        transform: translate(-50%, -50%);  /* Offset the element by 50% of its own width and height */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }


    #alertModal .close-button {
        position: absolute;
        top: 5px;
        right: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        padding: 20px;
        border: none;
        border-radius: 5px;
        margin: 15px 0px 0 0;
        background-color: #FCFAFB;
        transition: background-color 0.3s ease;
    }

    #alertModal .close-button:hover {
      background-color: rgba(31, 31, 41, 0.15);
    }

    #alertModal .close-buttom img {
      width: 24px;
      height: 24px;
    }

    #alertModal .modal-header h3 {
      font-size: 1.2em;
      margin-bottom: -5px;
    }

    #alertModal .modal-header p {
      font-size: .8em;
      margin-bottom: -5px;
    }

    #alertModal .modal-body {
      margin-top: 20px;
      margin-bottom: 20px;
      font-size: .7em;
    }

    #alertModal .modal-footer {
      text-align: right;
    }

    #alertModal .modal-footer button {
        font-family: 'Poppins', sans-serif;
        font-size: 0.75em;
        font-weight: 600;
        padding: 10px 40px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        background-color: #2B3467;
        color: white;
        margin-right: 0px;
    }

    #alertModal .modal-footer button:hover {
      background-color: #1F2947 !important;
    }

    #alertModal .alert-details {
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 15px;
    }

    #alertModal .red-alert {
      background-color: #ffebee;
      border-left: 4px solid #c62828;
    }

    #alertModal .orange-alert {
      background-color: #fff3e0;
      border-left: 4px solid #e65100;
    }

    #alertModal .yellow-alert {
      background-color: #fffde7;
      border-left: 4px solid #f9a825;
    }

    #alertModal .blue-alert {
      background-color: #e3f2fd;
      border-left: 4px solid #1565c0;
    }

    




  </style>
</head>
<body>
<?php
renderUnifiedComponent(
    '../img/iconpages/alerts.png', // $iconPath
    'Active Alerts', // $sectionTitle
    'Display a list of currently ongoing alerts for monitoring.', // $sectionDescription
    'View All Alert History', // $title (optional)
    [
        ['label' => 'Pages', 'link' => '#'],
        ['label' => 'Reports', 'link' => '#'],
    ] // $breadcrumb (optional)
);
?>

<div class="container">
    <div class="tabs">
      <div class="tab active">View All Active Alerts</div>
      <a href="../pages/alertHistory" class="tab" style="text-decoration: none;">View All Alert History</a>
    </div>
    

    <div class="loob">
      <div class="tabheader">
        <div class="text-container">
          <h2>Activity Log</h2>
          <p>Records user actions and system events, providing a detailed history for tracking and accountability.</p>
        </div>      

      </div>

      <div class="entries-selector">
        <form id="entriesForm" method="GET" action="">
          <select name="entries" onchange="this.form.submit()">
            <option value="5" <?php echo $entries_per_page == 5 ? 'selected' : ''; ?>>5</option>
            <option value="10" <?php echo $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
            <option value="25" <?php echo $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
            <option value="50" <?php echo $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
          </select> Entries per page
        </form>
      </div>

      <table>
        <thead>
          <tr>
            <th style="width: 30px;"><input type="checkbox"></th>
            <th>Barangay</th>
            <th>Alert Type</th>
            <th>Warning Level</th>
            <th>Date</th>
            <th>Timestamp</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alerts_page as $alert): 
            $statusColor = getStatusColor($alert['waterLevel']);
            $warningLevel = getWaterLevelStatus($alert['waterLevel']);
            $dateTime = new DateTime($alert['dateTime']);
            $date = $dateTime->format('Y-m-d');
            $time = $dateTime->format('h:i A');
          ?>
            <tr>
              <td><input type="checkbox"></td>
              <td><?php echo $alert['brgyName']; ?></td>
              <td>Flood Alert</td>
              <td>
                <span class="warning-badge <?php echo $statusColor; ?>-warning"><?php echo $warningLevel; ?></span>
              </td>
              <td><?php echo $date; ?></td>
              <td><?php echo $time; ?></td>
              <td>
                <button class="details-btn" onclick="showAlertDetails(<?php echo $alert['alertid']; ?>, '<?php echo $alert['brgyName']; ?>', <?php echo $alert['waterLevel']; ?>, <?php echo $alert['humidity']; ?>, <?php echo $alert['temperature']; ?>, '<?php echo $alert['dateTime']; ?>')">
                  <img src="../img/eyeH.png" alt="View" width="20">
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="pagination-info">
        Showing <?php echo $start_index + 1; ?> to <?php echo min($start_index + $entries_per_page, $total_entries); ?> of <?php echo $total_entries; ?> weekly entries (<?php echo $week_start; ?> - <?php echo $week_end; ?>)
      </div>

      <!-- Scrollable Pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="pagination-wrapper">
          <div class="pagination-container">
            <div class="pagination">
              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&entries=<?php echo $entries_per_page; ?>" <?php if ($i == $current_page) echo 'class="active"'; ?>><?php echo $i; ?></a>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>



      
    
    </div>

    <!-- Alert Details Modal -->
    <div id="alertModal" class="modal">
      <div class="modal-content">


        <div class="modal-header">
          <h3 id="modalTitle">Flood Alert</h3>
          <p id="modalLocation">Location: <strong><span id="modalBarangay"></span></strong></p>
          <p id="modalDate">Issued on: <strong><span id="modalDateTime"></span></strong></p>

          <button id="closeModalButton" class="close-button" onclick="closeModal()">
            <img src="../img/plus/closeD.png" alt="Close">
          </button>
        </div>
        <div class="modal-body">
          <div id="alertDetails" class="alert-details">
            <h4 id="modalWarningLevel"></h4>
            <p id="modalDescription"></p>
          </div>
          <div>
            <p><strong>Water Level:</strong> <span id="modalWaterLevel"></span> cm</p>
            <p><strong>Humidity:</strong> <span id="modalHumidity"></span>%</p>
            <p><strong>Temperature:</strong> <span id="modalTemperature"></span>°C</p>
          </div>
        </div>
        <div class="modal-footer">
          <button onclick="closeModal()" style="padding: 8px 16px; background-color: #2B3467; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
        </div>
      </div>
    </div>

    <script>

        function showAlertDetails(id, barangay, waterLevel, humidity, temperature, dateTime) {
            var modal = document.getElementById('alertModal');
            var modalBarangay = document.getElementById('modalBarangay');
            var modalDateTime = document.getElementById('modalDateTime');
            var modalWarningLevel = document.getElementById('modalWarningLevel');
            var modalDescription = document.getElementById('modalDescription');
            var modalWaterLevel = document.getElementById('modalWaterLevel');
            var modalHumidity = document.getElementById('modalHumidity');
            var modalTemperature = document.getElementById('modalTemperature');
            var alertDetails = document.getElementById('alertDetails');
            
            // Format the date and time
            var dt = new Date(dateTime);
            var formattedDate = dt.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Determine warning level and description
            var warningLevel, description, statusColor;

            if (waterLevel >= 30) {
                warningLevel = "RED WARNING (Force Evacuation)";
                description = "Water levels have rapidly risen to dangerous levels, threatening to flood critical areas. Immediate evacuation is necessary to ensure safety. Stay updated and follow local emergency guidelines.";
                statusColor = "red";
            } else if (waterLevel >= 20) {
                warningLevel = "ORANGE WARNING (Evacuation)";
                description = "Water levels are rising significantly. Evacuation may be necessary in low-lying areas. Be prepared to relocate if instructed by local authorities.";
                statusColor = "orange";
            } else if (waterLevel >= 10) {
                warningLevel = "YELLOW WARNING (Ready)";
                description = "Water levels are elevated. Stay vigilant and prepare for possible evacuation if levels continue to rise. Monitor official announcements closely.";
                statusColor = "yellow";
            } else {
                warningLevel = "BLUE (Normal)";
                description = "Water levels are within normal range. No immediate flood risk, but continue to monitor updates during rainy season.";
                statusColor = "blue";
            }

            // Set modal content
            modalBarangay.textContent = barangay;
            modalDateTime.textContent = formattedDate;
            modalWarningLevel.textContent = warningLevel;
            modalDescription.textContent = description;
            modalWaterLevel.textContent = waterLevel;
            modalHumidity.textContent = humidity;
            modalTemperature.textContent = temperature;

            // Set appropriate styling for alert box
            alertDetails.className = "alert-details " + statusColor + "-alert";

            // Show the modal
            modal.style.display = "block";

            // Prevent background scrolling
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            var modal = document.getElementById('alertModal');
            modal.style.display = "none";

            // Allow background scrolling again
            document.body.classList.remove('modal-open');
        }

    
  </script>


</body>
</html>
