<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Start session only if it hasn't already been started
}


// Debugging: Check session variables
if (!isset($_SESSION['userid'], $_SESSION['position'])) {
    error_log('Session variables are missing');
    echo json_encode(['error' => 'Session variables are missing']);
    exit;
}

// Normalize session variables
$userId = $_SESSION['userid'];
$userType = strtoupper(trim($_SESSION['position'])); // Convert to uppercase for consistency

// Include database connection
include_once '../server/connect.php';
if (!$conn) {
    error_log('Database connection failed');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Function to fetch resident information along with the Barangay name for the logged-in user
function fetchResidentInfo($conn, $userId) {
    try {
        // SQL query to fetch resident information along with the Barangay name
        // The query filters based on the brgyid matching the userid of the logged-in user
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
                b.brgyid = :userId
        ";
    
        // Prepare and execute the query with the bound userId parameter
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all results as an associative array
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the results as an array
        return $results;
    } catch (PDOException $e) {
        // Handle any errors
        return ["error" => "Database query failed: " . $e->getMessage()];
    }
}


// Call the function to fetch resident information
$residents = fetchResidentInfo($conn, $userId);

?>
