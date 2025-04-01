<?php
session_start();
include '../server/connect.php';

// Include components and database connection
include '../component/adminsidebar.php';
include '../component/navbar.php';

if ($_SESSION['position'] === 'MDRRMO Cainta') {
    include '../component/adminsidebar.php'; // Admin sidebar
} elseif ($_SESSION['position'] === 'BRGY Staff') {
    include '../component/brgysidebar.php'; // Barangay sidebar
} else {
    // Handle unknown user types
    echo "Invalid user type!";
    exit();
}


// Get the current day and time
$currentDateTime = date('l, F j, Y, g:i A');

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>i-Alert: Flood Monitoring and Alert System</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    .chart-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(550px, 1fr));
        gap: 90px;
        justify-content: center;
        padding: 10px;
        margin-top: 20px;
    }

    .chart-card {
        background: #1F1F290D;
        border-radius: 10px;
        padding: 25px;
        text-align: center;
        width: 800px;
        margin: auto;
    }

    /* Styling for form labels */
    #filterForm label {
        font-family: 'Poppins', sans-serif; /* Set font family */
        font-size: 0.75em;
    }

    /* Styling for form inputs (text, date, select) */
    #filterForm input[type="date"],
    #filterForm select {
        padding: 8px;
        border: 2px solid rgba(31, 31, 41, 0.15);
        background-color: #FCFAFB;
        border-radius: 5px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.75em;
        font-weight: 400;
        width: 150px;
        margin-right: 10px;
        outline: none;
    }

    /* Hover effect for select input */
    #filterForm select:hover,
    #filterForm input[type="date"]:hover {
        background-color: #e9ecef !important;
        font-weight: 500 !important;
    }

    /* Focus effect for inputs */
    #filterForm input[type="date"]:focus,
    #filterForm select:focus {
        background-color: white !important;
        outline: none; /* Remove outline */
        border: 2px solid #2B3467 !important; /* Active border color on focus */
        font-weight: 500; /* Optional: Maintain font weight on focus */
    }

    /* Styling for the Filter button */
    #filterForm .filter-btn {
        padding: 10px 10px;
        border: none;
        border-radius: 4px;
        background-color: #2B3467;
        color: white;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 0.75em;
        cursor: pointer;
        width: 80px;
    }

    /* Hover effect for the Filter button */
    #filterForm .filter-btn:hover {
        background-color: #1f264e;
    }

    /* Styling for the Reset button */
    #filterForm .reset-btn {
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

    #filterForm .reset-btn:hover {
        color: #1F1F29;
    }

    

  </style>
</head>
<body>

<?php
renderUnifiedComponent(
    '../img/iconpages/analytics.png', // $iconPath
    'Analytics', // $sectionTitle
    'Provide insights, summaries, or analyses of system performance, operations, or activities.', // $sectionDescription
    'Analytics Data', // $title (optional)
    [
        ['label' => 'Pages', 'link' => '#'],
        ['label' => 'Analytics', 'link' => '#'],
    ] // $breadcrumb (optional)
);
?>

<div class="container">
    <div class="loob">
      <div class="tabheader">
        <div class="text-container">
          <h2>Analytics Data</h2>
          <p>Records arduino fetch data, actions and system events, providing detailed analytics for tracking and accountability.</p>
          <p style="margin-top: 30px;">Current Day: <?php echo $currentDateTime; ?></p> <!-- Display the current day -->
        </div>
      </div>

      <form id="filterForm">
        <?php if ($_SESSION['position'] === 'MDRRMO Cainta'): ?>
            <label for="barangay">Select Barangay:</label>
            <select name="barangay" id="barangay">
                <option value="All" selected>All Barangay</option>
                <?php
                try {
                    $stmt = $conn->prepare("SELECT DISTINCT brgyName FROM alerttb ORDER BY brgyName ASC");
                    $stmt->execute();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($row['brgyName']) . "'>" . htmlspecialchars($row['brgyName']) . "</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value=''>Error Loading Barangays</option>";
                }
                ?>
            </select>
        <?php else: ?>
            <input type="hidden" name="barangay" id="barangay" value="<?= $_SESSION['BrgyName'] ?>">
            <p>Barangay: <?= $_SESSION['BrgyName'] ?></p>
        <?php endif; ?>

        <label for="startDate">Start Date:</label>
        <input type="date" name="startDate" id="startDate">

        <label for="endDate">End Date:</label>
        <input type="date" name="endDate" id="endDate">

        <button type="button" class="filter-btn" onclick="fetchData()">Filter</button>
        <button type="reset" class="reset-btn" onclick="resetFilters()">Reset</button>
      </form>

    

    <div class="chart-container">
        <div class="chart-card">
            <h3>Water Level</h3>
            <canvas id="waterChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Humidity</h3>
            <canvas id="humidityChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Temperature</h3>
            <canvas id="tempChart"></canvas>
        </div>
    </div>
</div>



<script src="../js/analytics.js"></script>
</body>
</html>