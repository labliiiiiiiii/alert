<?php
include_once '../pages/auth_check.php'; // Validate session

// Example: Restrict certain pages to admins only
if ($_SESSION['usertype'] !== 'admin') {
  header("Location: ../pages/loginpage.php");
  exit();
}

// homepage.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the component
include '../component/adminsidebar.php';
include_once '../component/navbar.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>i-Alert: Flood Monitoring and Alert System</title>
<!-- 
  <link rel="stylesheet" href="../style/detailsModal.css?v=<?php echo time(); ?>"> -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Leaflet.js - Updated to use HTTPS CDN links -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

  <script src="https://cdn.socket.io/4.6.0/socket.io.min.js" integrity="sha384-c79GN5VsunZvi+Q/WObgk2in0CbZsHnjEqvFxC5DxHn9lTfNce2WW6h2pH6u/kF+" crossorigin=""></script>

  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #FCFAFB;
    }

    .content {
      margin-left: 240px; /* Matches the width of the sidebar */
      padding: 20px;
      height: 100%;
    }
  
    
    #map {
        height: 500px; /* Adjust this value as needed */
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 10px;
        margin-top: -20px;
    }







    .modalDetails {
        display: none; /* Hidden by default */
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4); /* Semi-transparent background */
        overflow: auto;
        padding-top: 60px;
    }

    #detailsModal .modal-container {
        width: 500px; /* Fixed width for the container */
        margin: 50px auto;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        text-align: center;
    }

    #detailsModal .modal-header {
        background: url("../img/Stay informed, stay prepared.png") no-repeat center center;
        height: 150px;
        background-size: cover;
        padding: 10px;
        position: relative;
        color: white;
        font-size: 18px;
        font-weight: bold;
    }

    #detailsModal .close-button {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        color: white;
        border: none;
        border-radius: 10px;
        width: 40px;  /* Adjust size of the button */
        height: 40px; /* Adjust size of the button */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    #detailsModal .close-button img {
        width: 24px;  /* Size of the icon */
        height: 24px; /* Size of the icon */
    }

    #detailsModal .close-button:hover {
        background-color: white;
    }


    #detailsModal .modal-body {
        padding: 20px;
    }

    #detailsModal .header-info {
        display: flex;                /* Use flexbox to align items horizontally */
        justify-content: space-between; /* Space between text container and logo */
        align-items: flex-start;      /* Align the items to the top */
        margin-bottom: 15px;          /* Space below the header */
    }

    #detailsModal .text-container {
        display: flex;                /* Stack name and date vertically */
        flex-direction: column;       /* Make the name and date appear in a column */
        justify-content: center;      /* Center content vertically within the text container */
        margin-right: 15px;           /* Space between the text and logo */
    }

    #detailsModal .modal-body h2 {
        font-size: 1em;
        color: #1F1F29;
        font-weight: bold;
        text-align: left;
        margin-top: 0;

    }

    #dateText {
        font-size: .7em;               /* Adjust the font size */
        color: #1F1F29B3;                   /* Set a custom color */
        margin-top: -10px;                              /* Add some padding */
        text-align: left;            /* Center align the text */
    }

    #barangayLogo {
        height: 60px;                 /* Set the logo height */
        align-self: flex-start;       /* Align logo to the top */
    }

    .modal-body p {
        text-align: left;
        font-size: .8em;
        font-weight: 600;
        color: #1F1F29;
    }


    #detailsModal .modal-footer {
        padding: 10px;
        width: 500px;
        text-align: center;
        margin: -50px auto; /* Center the footer */
    }

    #detailsModal .modal-footer button {
        font-family: 'Poppins', sans-serif;
        background: #2B3467;
        color: white;
        font-size: 0.75em;
        font-weight: 600;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    #detailsModal .modal-footer button:hover {
        background-color: #1F2947;
    }

    .no-scroll {
        overflow: hidden;
    }




    
    .detailed-modal {
      display: none;
      position: fixed;
      z-index: 1200;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.7);
    }
    
    .detailed-modal-content {
        width: 700px; /* Fixed width for the container */
        margin: 50px auto;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        text-align: center;
    }

    #detailedModal .detailed-modal-header {
        background: url("../img/Stay informed, stay prepared.png") no-repeat center center;
        height: 120px;
        background-size: cover;
        padding: 10px;
        position: relative;
        color: white;
        font-size: 18px;
        font-weight: bold;
    }

    #detailedModal .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        color: white;
        border: none;
        border-radius: 10px;
        width: 40px;  /* Adjust size of the button */
        height: 40px; /* Adjust size of the button */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    #detailedModal .close-btn:hover {
        background-color: white;
    }

    #detailedModal .close-btn img {
        width: 24px;  /* Size of the icon */
        height: 24px; /* Size of the icon */
    }

    #detailedModal .detailed-modal-header h2 {
        font-size: 1em;
        color: #1F1F29;
        font-weight: bold;
        text-align: left;
        margin-top: 60px;
        margin-left: 30px;
    }

    #detailedModal .detailed-modal-header p {
        text-align: left;
        font-size: .7em;
        font-weight: 500;
        color: #1F1F29;
        margin-top: -10px;
        margin-left: 30px;
    }

    
    .status-indicators {
        display: flex;
        justify-content: space-between;
        margin: 40px;
        padding: 10px;  /* Optional: adds padding inside the border */
        border: 2px solid #ccc; /* Adds a light gray border */
        margin-top: 20px;
    }

    .status-indicators .indicator span {
        font-size: .7em;  /* Adjust font size */
        font-weight: 500;  /* Make the text bold */
        color: #333;  /* Set a default text color */
        margin-left: 8px;  /* Adds some space between the dot and the text */
    }

    
    .indicator {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-left: 50px;
      margin-right: 50px;
    }
    
    .indicator-dot {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      margin-bottom: 5px;
      
      
    }
    
    .metrics {
      display: flex;
      justify-content: space-between;
      text-align: center;
      border: 2px solid #ccc; /* Adds a light gray border */
      margin: 40px;
      padding: 10px;  /* Optional: adds padding inside the border */
      margin-top: -20px;
    }
    
    .metric {
      flex: 1;
      padding: 15px;
      border-right: 2px solid #ccc;
    }
    
    .metric:last-child {
      border-right: none;
    }
    
    .metric-value {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 5px;
    }
    
    .metric-label {
      font-size: 12px;
      text-transform: uppercase;
      color: #1F1F29;
    }
    
    .flood-status {
      display: flex;
      align-items: center;
      
      margin: 40px;
      margin-top: -20px;
      margin-bottom: 50px;
      text-align: left;
    }
    

    /* Style the bold text (Flood Status and Water Level) */
    .flood-status b {
        font-size: .8em;
        font-weight: 600;
        color: #1F1F29;  /* Set a dark gray color */
    }

    /* Style the spans inside the flood status */
    .flood-status span {
        font-size: .7em;  /* Adjust font size */
        color: #1F1F29;  /* Set a color for the text */
        font-weight: normal;  /* Make the text weight normal */
    }

    /* Style the small text (Water Level Description) */
    .flood-status small {
        font-size: 0.6em;  /* Smaller text size */
        color: #888;  /* Lighter gray color */
        font-style: italic;  /* Italicize the description text */
    }
    
    .status-dot {
      width: 15px;
      height: 15px;
      border-radius: 50%;
      margin-right: 10px;
    }
    
    
    
    .legend-container {
      position: absolute;
      top: 210px;
      right: 50px;
      background: white;
      padding: 10px;
      border-radius: 5px;
      z-index: 900;
    }
    
    .view-passable-roads-btn {
      background: #28a745;
      color: white;
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
      display: none;
    }
    
    .view-passable-roads-btn:hover {
      background: #218838;
    }

    .popup-text {
        font-family: 'Poppins', sans-serif;  /* Change to your preferred font */
        font-size: .8em;                   /* Adjust size as needed */
        font-weight: bold;                 /* Optional: Make text bold */
        color: #333;                       /* Optional: Change text color */
    }

    .passable-roads {
      margin-top: 15px;
      border-top: 1px solid #eee;
      padding-top: 15px;
    }
    
    .passable-roads ul {
      text-align: left;
      margin-top: 10px;
    }

    

  </style>
</head>
<body>
  
  <?php
    renderUnifiedComponent(
      '../img/iconpages/maps.png', // Icon path
      'Create Announcement', // Section title
      'To inform, raise awareness, and guide action.', // Section description
      'Cainta Maps', // Title (optional)
        [
          ['label' => 'Pages', 'link' => '#'],
          ['label' => 'Maps', 'link' => '#'],
        ] // Breadcrumb (optional)
      );
  ?>
  <div class="content">

    <div id="map"></div>
    
    <div class="legend-container">
      <div>
        <b>Water Level:</b>
        <ul style="list-style-type: none; padding-left: 10px; margin: 5px 0;">
          <li><span style="color: blue;">●</span> Normal (0-9 cm)</li>
          <li><span style="color: yellow;">●</span> Monitor (10-19 cm)</li>
          <li><span style="color: orange;">●</span> Watch (20-29 cm)</li>
          <li><span style="color: red;">●</span> Warning (30+ cm)</li>
        </ul>
      </div>
      <div>
        <b>Roads:</b>
        <ul style="list-style-type: none; padding-left: 10px; margin: 5px 0;">
          <li><span style="color: green;">―――</span> Passable</li>
          <li><span style="color: red;">―――</span> Not Recommended</li>
        </ul>
      </div>
    </div>

    <!-- Modal -->
    <div id="detailsModal" class="modalDetails">
        <div class="modal-container">
            <div class="modal-header">
                <button class="close-button" onclick="closeModal()">
                    <img src="../img/plus/closeD.png" alt="Close" width="24" height="24">  <!-- Add your image here -->
                </button>
            </div>

            <div class="modal-body">
                <div class="header-info">
                    <div class="text-container">
                        <h2 id="barangayName"></h2>
                        <p id="dateText"></p>
                    </div>
                    <img id="barangayLogo" src="" alt="Barangay Logo" width="100">
                </div>
                <p>TEMPERATURE: <span id="temperatureText"></span> °C</p>
                <p>HUMIDITY: <span id="humidityText"></span> %</p>
                <p>WATER LEVEL: <span id="basicWaterLevelText"></span> cm</p>

            </div>
        </div>

        <div class="modal-footer">
                <button id="viewPassableRoadsBtn" class="view-passable-roads-btn" onclick="showPassableRoads()">View Passable Roads</button>
                <button class="more-details-btn" onclick="showDetailedModal()">More Details</button>
        </div>
    </div>



    <!-- Detailed Modal -->
    <div id="detailedModal" class="detailed-modal">
      <div class="detailed-modal-content">
        <div class="detailed-modal-header">
            <button class="close-btn" id="closeDetailedModalBtn" onclick="closeDetailedModal()">
                <img src="../img/plus/closeD.png" alt="Close" width="24" height="24">  <!-- Add your image here -->
            </button>
            <h2 id="detailedBarangayName"></h2>
            <p><span id="detailedDateText"></span></p>
        </div>

       
        <div class="status-indicators">
            <div class="indicator">
                <div class="indicator-dot" style="background-color: blue;"></div>
                <span>Normal</span>
            </div>
            <div class="indicator">
                <div class="indicator-dot" style="background-color: yellow;"></div>
                <span>Monitor</span>
            </div>
            <div class="indicator">
                <div class="indicator-dot" style="background-color: orange;"></div>
                <span>Watch</span>
            </div>
            <div class="indicator">
                <div class="indicator-dot" style="background-color: red;"></div>
                <span>Warning</span>
            </div>
        </div>

        <div class="metrics">
          <div class="metric">
            <div class="metric-value" id="detailedTemperature">°C</div>
            <div class="metric-label">TEMPERATURE</div>
          </div>
          <div class="metric">
            <div class="metric-value" id="detailedHumidity">%</div>
            <div class="metric-label">HUMIDITY</div>
          </div>
          <div class="metric">
            <div class="metric-value" id="detailedWaterLevel">0 cm</div>
            <div class="metric-label">WATER LEVEL</div>
          </div>
        </div>

        <div class="flood-status">
          <div class="status-dot" id="floodStatusDot" style="background-color: blue; display: none;"></div>
          <div>
            <div><b>Flood Status:</b> <span id="floodStatusText">Normal</span></div>
            <div><b>Water Level:</b> <span id="waterLevelText">0.0 cm</span></div>
            <div><small id="waterLevelDescription">Normal Level</small></div>
          </div>
        </div>

        <div id="passableRoadsSection" class="passable-roads" style="display: none;">
          <h3>Passable Roads for Vehicles</h3>
          <p>The following roads are currently passable for cars:</p>
          <ul id="passableRoadsList">
            <!-- Roads will be added here dynamically -->
          </ul>
        </div>

      </div>
    </div>

  </div>

    <script>
        var map = L.map('map').setView([14.5786, 121.1226], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var barangays = [
            { name: "San Andres", coords: [14.5869, 121.1296], radius: 500 },
            { name: "San Isidro", coords: [14.5714, 121.1308], radius: 400 },
            { name: "San Juan", coords: [14.5855, 121.1227], radius: 300 },
            { name: "San Roque", coords: [14.5805, 121.1269], radius: 450 },
            { name: "Santo Domingo", coords: [14.5762, 121.1234], radius: 350 },
            { name: "Santa Rosa", coords: [14.5734, 121.1197], radius: 400 },
            { name: "Sto. Niño", coords: [14.5725, 121.1270], radius: 500 }
        ];

        var barangayCircles = {};
        var barangayData = {};
        var roadLayers = {};
        var currentRoadLayers = [];

        // Define the roads for each barangay
        var barangayRoads = {
            "San Andres": [
                { 
                    name: "Emerald Avenue", 
                    path: [[14.5880, 121.1290], [14.5865, 121.1320]],
                    passable: true
                },
                { 
                    name: "Ruby Street", 
                    path: [[14.5890, 121.1280], [14.5865, 121.1275]],
                    passable: true
                },
                { 
                    name: "Pearl Road", 
                    path: [[14.5850, 121.1300], [14.5870, 121.1330]],
                    passable: false
                }
            ],
            "San Isidro": [
                { 
                    name: "Mabini Street", 
                    path: [[14.5710, 121.1290], [14.5730, 121.1325]],
                    passable: true
                },
                { 
                    name: "Rizal Avenue", 
                    path: [[14.5700, 121.1310], [14.5725, 121.1300]],
                    passable: true
                },
                { 
                    name: "Bonifacio Road", 
                    path: [[14.5715, 121.1330], [14.5695, 121.1310]],
                    passable: false
                }
            ],
            "San Juan": [
                { 
                    name: "Felix Avenue", 
                    path: [[14.5840, 121.1210], [14.5865, 121.1240]],
                    passable: true
                },
                { 
                    name: "A. Bonifacio Street", 
                    path: [[14.5850, 121.1220], [14.5835, 121.1250]],
                    passable: true
                },
                { 
                    name: "Don Mariano Marcos Avenue", 
                    path: [[14.5870, 121.1200], [14.5855, 121.1235]],
                    passable: false
                }
            ],
            "San Roque": [
                { 
                    name: "F. Legaspi Street", 
                    path: [[14.5795, 121.1250], [14.5815, 121.1280]],
                    passable: true
                },
                { 
                    name: "Sumulong Highway", 
                    path: [[14.5805, 121.1260], [14.5825, 121.1245]],
                    passable: true
                },
                { 
                    name: "J.P. Rizal Street", 
                    path: [[14.5790, 121.1280], [14.5810, 121.1295]],
                    passable: false
                }
            ],
            "Santo Domingo": [
                { 
                    name: "C. Raymundo Avenue", 
                    path: [[14.5750, 121.1220], [14.5775, 121.1245]],
                    passable: true
                },
                { 
                    name: "Ortigas Avenue Extension", 
                    path: [[14.5765, 121.1225], [14.5785, 121.1215]],
                    passable: true
                },
                { 
                    name: "Floodway Road", 
                    path: [[14.5745, 121.1250], [14.5765, 121.1270]],
                    passable: false
                }
            ],
            "Santa Rosa": [
                { 
                    name: "Imelda Avenue", 
                    path: [[14.5725, 121.1180], [14.5750, 121.1210]],
                    passable: true
                },
                { 
                    name: "San Guillermo Street", 
                    path: [[14.5720, 121.1190], [14.5740, 121.1215]],
                    passable: true
                },
                { 
                    name: "Villa Santa Road", 
                    path: [[14.5735, 121.1170], [14.5755, 121.1190]],
                    passable: false
                }
            ],
            "Sto. Niño": [
                { 
                    name: "Valley Golf Avenue", 
                    path: [[14.5715, 121.1250], [14.5735, 121.1280]],
                    passable: true
                },
                { 
                    name: "Governor's Drive", 
                    path: [[14.5710, 121.1270], [14.5730, 121.1255]],
                    passable: true
                },
                { 
                    name: "Riverside Street", 
                    path: [[14.5720, 121.1290], [14.5745, 121.1275]],
                    passable: false
                }
            ]
        };

        function getCircleColor(waterLevel) {
            if (waterLevel >= 30) return { color: "red", fillColor: "red" };
            if (waterLevel >= 20) return { color: "orange", fillColor: "orange" };
            if (waterLevel >= 10) return { color: "yellow", fillColor: "yellow" };
            return { color: "blue", fillColor: "#3a95ff" };
        }

        function getStatusText(waterLevel) {
            if (waterLevel >= 30) return "Warning";
            if (waterLevel >= 20) return "Watch";
            if (waterLevel >= 10) return "Monitor";
            return "Normal";
        }

        function getStatusColor(waterLevel) {
            if (waterLevel >= 30) return "red";
            if (waterLevel >= 20) return "orange";
            if (waterLevel >= 10) return "yellow";
            return "blue";
        }

        function getWaterLevelDescription(waterLevel) {
            if (waterLevel >= 30) return "Critical Level - Evacuation May Be Needed";
            if (waterLevel >= 20) return "Rising Level - Prepare for Possible Evacuation";
            if (waterLevel >= 10) return "Elevated Level - Monitor Closely";
            return "Normal Level";
        }

        barangays.forEach(barangay => {
            let marker = L.marker(barangay.coords).addTo(map).bindPopup(`<span class="popup-text">${barangay.name}</span>`);
            
            barangayCircles[barangay.name] = L.circle(barangay.coords, {
                radius: barangay.radius,
                color: "blue",
                fillColor: "#3a95ff",
                fillOpacity: 0.3
            }).addTo(map);

            marker.on('click', function() {
                openModal(barangay.name);
            });
        });


        var evacuationIcon = L.icon({
            iconUrl: '../img/house.png',
            iconSize: [20, 20],
            iconAnchor: [15, 30],
            popupAnchor: [0, -30]
        });

        var evacuationCenters = [
            { name: "Planters Elementary School", coords: [14.5805, 121.1258] },
            { name: "San Francisco Elementary School", coords: [14.5780, 121.1285] },
            { name: "FP Felix Unit 1 Elementary School", coords: [14.5754, 121.1247] },
            { name: "Kabisig Elementary School", coords: [14.5738, 121.1221] },
            { name: "Arinda Elementary School", coords: [14.5716, 121.1260] }
        ];

        evacuationCenters.forEach(center => {
            L.marker(center.coords, { icon: evacuationIcon })
                .addTo(map)
                .bindPopup(`<span class="popup-text">${center.name}</span><br><span class="popup-text">Evacuation Center</span>`);
        });


        // Connect to WebSocket server with error handling
        var socket = io('http://localhost:3000', {
            transports: ['polling', 'websocket'],
            reconnectionAttempts: 5,
            reconnectionDelay: 1000
        });

        // Add error handling
        socket.on('connect_error', function(error) {
            console.error('Connection Error:', error);
        });

        socket.on('connect', function() {
            console.log('Connected to server');
        });

        socket.on('disconnect', function(reason) {
            console.log('Disconnected from server:', reason);
        });

        // Listen for real-time sensor data
        socket.on('sensorData', function(data) {
            console.log('Received sensor data:', data);
            
            // Store the received data in the barangayData object
            if (data.barangay) {
                // Remove quotes if they exist
                let barangayName = data.barangay.replace(/['"]+/g, '');
                
                barangayData[barangayName] = {
                    waterLevel: parseFloat(data.waterLevel),
                    temperature: parseFloat(data.temperature),
                    humidity: parseFloat(data.humidity),
                    alertColor: data.alertColor || getStatusColor(parseFloat(data.waterLevel))
                };
                
                // Update the circle for this barangay
                let barangay = barangays.find(b => b.name === barangayName);
                if (barangay && barangayCircles[barangayName]) {
                    let circleStyle = getCircleColor(parseFloat(data.waterLevel));
                    barangayCircles[barangayName].setStyle(circleStyle);
                    
                    // If water level is in "Monitor" status (10-19 cm), show passable roads
                    if (data.waterLevel >= 10 && data.waterLevel < 20) {
                        showRoadsForBarangay(barangayName);
                    } else {
                        hideRoadsForBarangay(barangayName);
                    }
                }
                
                // If modal is open and showing this barangay, update the data
                if (window.selectedBarangay === barangayName) {
                    updateModalData(barangayName);
                }
            }
        });

        // Function to update modal data when it's open
        function updateModalData(barangayName) {
            // Check if basic modal is open
            if (document.getElementById("detailsModal").style.display === "block") {
                document.getElementById("temperatureText").textContent = barangayData[barangayName].temperature;
                document.getElementById("humidityText").textContent = barangayData[barangayName].humidity;
                document.getElementById("basicWaterLevelText").textContent = barangayData[barangayName].waterLevel;
                
                // Show "View Passable Roads" button only if water level is in Monitor status (10-19 cm)
                let viewPassableRoadsBtn = document.getElementById("viewPassableRoadsBtn");
                if (barangayData[barangayName].waterLevel >= 10 && barangayData[barangayName].waterLevel < 20) {
                    viewPassableRoadsBtn.style.display = "inline-block";
                } else {
                    viewPassableRoadsBtn.style.display = "none";
                }
            }
            
            // Check if detailed modal is open
            if (document.getElementById("detailedModal").style.display === "block") {
                let waterLevel = barangayData[barangayName].waterLevel;
                
                document.getElementById("detailedTemperature").textContent = barangayData[barangayName].temperature + "°C";
                document.getElementById("detailedHumidity").textContent = barangayData[barangayName].humidity + "%";
                document.getElementById("detailedWaterLevel").textContent = waterLevel + " cm";
                
                document.getElementById("waterLevelText").textContent = waterLevel + " cm";
                document.getElementById("floodStatusText").textContent = getStatusText(waterLevel);
                document.getElementById("floodStatusDot").style.backgroundColor = getStatusColor(waterLevel);
                document.getElementById("waterLevelDescription").textContent = getWaterLevelDescription(waterLevel);
                
                // Show/hide passable roads section based on water level
                let passableRoadsSection = document.getElementById("passableRoadsSection");
                if (waterLevel >= 10 && waterLevel < 20) {
                    passableRoadsSection.style.display = "block";
                    populatePassableRoads(barangayName);
                } else {
                    passableRoadsSection.style.display = "none";
                }
            }
        }

        function updateCircles(data) {
            barangays.forEach(barangay => {
                if (!data[barangay.name]) return;
                let circleStyle = getCircleColor(data[barangay.name].waterLevel);
                barangayCircles[barangay.name].setStyle(circleStyle);
                
                // If water level is in "Monitor" status (10-19 cm), show passable roads
                if (data[barangay.name].waterLevel >= 10 && data[barangay.name].waterLevel < 20) {
                    showRoadsForBarangay(barangay.name);
                } else {
                    hideRoadsForBarangay(barangay.name);
                }
            });
        }

        function showRoadsForBarangay(barangayName) {
            // Clear existing road layers for this barangay
            if (roadLayers[barangayName]) {
                roadLayers[barangayName].forEach(layer => map.removeLayer(layer));
                roadLayers[barangayName] = [];
            } else {
                roadLayers[barangayName] = [];
            }
            
            // Add road polylines to the map
            if (barangayRoads[barangayName]) {
                barangayRoads[barangayName].forEach(road => {
                    let roadLayer = L.polyline(road.path, {
                        color: road.passable ? 'green' : 'red',
                        weight: 3,
                        opacity: 0.7,
                        dashArray: road.passable ? '' : '5, 10'
                    }).addTo(map);
                    
                    roadLayer.bindPopup(`<b>${road.name}</b><br>${road.passable ? 'Passable for vehicles' : 'Not recommended for vehicles'}`);
                    roadLayers[barangayName].push(roadLayer);
                });
            }
        }

        function hideRoadsForBarangay(barangayName) {
            if (roadLayers[barangayName]) {
                roadLayers[barangayName].forEach(layer => map.removeLayer(layer));
                roadLayers[barangayName] = [];
            }
        }

        function showRoadsOnMap(barangayName) {
                // First clear any currently displayed roads
                clearCurrentRoads();
                
                // Then display roads for the selected barangay
                if (barangayRoads[barangayName]) {
                    barangayRoads[barangayName].forEach(road => {
                        let roadLayer = L.polyline(road.path, {
                            color: road.passable ? 'green' : 'red',
                            weight: 4,
                            opacity: 0.8,
                            dashArray: road.passable ? '' : '5, 10'
                        }).addTo(map);
                        
                        roadLayer.bindPopup(`<b>${road.name}</b><br>${road.passable ? 'Passable for vehicles' : 'Not recommended for vehicles'}`);
                        currentRoadLayers.push(roadLayer);
                    });
                    
                    // Center the map on the barangay
                    let barangay = barangays.find(b => b.name === barangayName);
                    if (barangay) {
                        map.setView(barangay.coords, 15);
                    }
                }
            }

            function clearCurrentRoads() {
                currentRoadLayers.forEach(layer => map.removeLayer(layer));
                currentRoadLayers = [];
            }

            function openModal(barangayName) {
                let modal = document.getElementById("detailsModal");
                document.getElementById("barangayName").textContent = barangayName + ", Cainta Rizal";

                // Set logo based on barangay (using a placeholder path)
                document.getElementById("barangayLogo").src = `../img/LOGO/${barangayName.toLowerCase().replace(/\s/g, '_')}.png`;

                // Set current date
                let currentDate = new Date();
                let dateStr = currentDate.toLocaleDateString('en-US', {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
                document.getElementById("dateText").textContent = dateStr;

                // Set data if available (from Arduino via server)
                if (barangayData[barangayName]) {
                    let waterLevel = barangayData[barangayName].waterLevel;
                    document.getElementById("temperatureText").textContent = barangayData[barangayName].temperature;
                    document.getElementById("humidityText").textContent = barangayData[barangayName].humidity;
                    document.getElementById("basicWaterLevelText").textContent = waterLevel;

                    // Show "View Passable Roads" button only if water level is in Monitor status (10-19 cm)
                    let viewPassableRoadsBtn = document.getElementById("viewPassableRoadsBtn");
                    if (waterLevel >= 10 && waterLevel < 20) {
                        viewPassableRoadsBtn.style.display = "inline-block";
                    } else {
                        viewPassableRoadsBtn.style.display = "none";
                    }
                } else {
                    // Fallback data if real data not available
                    document.getElementById("temperatureText").textContent = "";
                    document.getElementById("humidityText").textContent = "";
                    document.getElementById("basicWaterLevelText").textContent = "";
                    document.getElementById("viewPassableRoadsBtn").style.display = "none";
                }

                modal.style.display = "block";
                document.body.classList.add('no-scroll'); // Add this line

                // Store selected barangay for detailed modal
                window.selectedBarangay = barangayName;
            }

            function closeModal() {
                document.getElementById("detailsModal").style.display = "none";
                document.body.classList.remove('no-scroll'); // Add this line
                clearCurrentRoads();
            }

            
            function showDetailedModal() {
                let barangayName = window.selectedBarangay;
                let detailedModal = document.getElementById("detailedModal");
                
                // Set header information
                document.getElementById("detailedBarangayName").textContent = barangayName + ", Cainta Rizal";
                
                let currentDate = new Date();
                let dateStr = currentDate.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    month: 'long', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
                document.getElementById("detailedDateText").textContent = dateStr;
                
                // Set metrics (from Arduino via server)
                if (barangayData[barangayName]) {
                    let temperature = barangayData[barangayName].temperature;
                    let humidity = barangayData[barangayName].humidity;
                    let waterLevel = barangayData[barangayName].waterLevel;
                    
                    document.getElementById("detailedTemperature").textContent = temperature + "°C";
                    document.getElementById("detailedHumidity").textContent = humidity + "%";
                    document.getElementById("detailedWaterLevel").textContent = waterLevel + " cm";
                    
                    document.getElementById("waterLevelText").textContent = waterLevel + " cm";
                    document.getElementById("floodStatusText").textContent = getStatusText(waterLevel);
                    document.getElementById("floodStatusDot").style.backgroundColor = getStatusColor(waterLevel);
                    document.getElementById("waterLevelDescription").textContent = getWaterLevelDescription(waterLevel);
                    
                    // Show/hide passable roads section based on water level
                    let passableRoadsSection = document.getElementById("passableRoadsSection");
                    if (waterLevel >= 10 && waterLevel < 20) {
                        passableRoadsSection.style.display = "block";
                        populatePassableRoads(barangayName);
                    } else {
                        passableRoadsSection.style.display = "none";
                    }
                } else {
                    // Fallback data if real data not available
                    document.getElementById("detailedTemperature").textContent = "°C";
                    document.getElementById("detailedHumidity").textContent = "%";
                    document.getElementById("detailedWaterLevel").textContent = " cm";
                    document.getElementById("waterLevelText").textContent = " cm";
                    document.getElementById("floodStatusText").textContent = "Normal";
                    document.getElementById("floodStatusDot").style.backgroundColor = "blue";
                    document.getElementById("waterLevelDescription").textContent = "Normal Level";
                    document.getElementById("passableRoadsSection").style.display = "none";
                }
                
                detailedModal.style.display = "block";
            }
            
            function populatePassableRoads(barangayName) {
                let roadsList = document.getElementById("passableRoadsList");
                roadsList.innerHTML = "";
                
                if (barangayRoads[barangayName]) {
                    barangayRoads[barangayName].forEach(road => {
                        if (road.passable) {
                            let listItem = document.createElement("li");
                            listItem.textContent = road.name;
                            roadsList.appendChild(listItem);
                        }
                    });
                    
                    if (roadsList.children.length === 0) {
                        let listItem = document.createElement("li");
                        listItem.textContent = "No passable roads available at this time.";
                        roadsList.appendChild(listItem);
                    }
                } else {
                    let listItem = document.createElement("li");
                    listItem.textContent = "Road data not available for this area.";
                    roadsList.appendChild(listItem);
                }
            }
            
            function showPassableRoads() {
                let barangayName = window.selectedBarangay;
                showRoadsOnMap(barangayName);
            }
            
            function closeDetailedModal() {
                document.getElementById("detailedModal").style.display = "none";
            }

            // Close modals if user clicks outside the modal content
            function closeDetailedModal() {
            document.getElementById("detailedModal").style.display = "none";
        }

        function populatePassableRoads(barangayName) {
            let roadsContainer = document.getElementById("passableRoadsList");
            roadsContainer.innerHTML = "";

            if (barangayRoads[barangayName]) {
                barangayRoads[barangayName].forEach(road => {
                    if (road.passable) {
                        let roadItem = document.createElement("li");
                        roadItem.textContent = road.name;
                        roadsContainer.appendChild(roadItem);
                    }
                });
            }
        }

        document.getElementById("closeDetailedModalBtn").addEventListener("click", function() {
            closeDetailedModal();
        });

        
    </script>
</body>
</html>