<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require_once 'connect.php';

// Function to fetch resident information by ID along with the Barangay name
function fetchResidentById($conn, $residentId) {
    try {
        // SQL query to fetch resident information by ID along with the Barangay name
        $sql = "
            SELECT
                ra.*,
                s.BrgyName
            FROM
                residentinfo_archive ra
            LEFT JOIN
                barangaytb b ON ra.barangay = b.brgyid
            LEFT JOIN
                brgystaffinfotb s ON b.staff_userid = s.userid
            WHERE
                ra.residentid = :residentId
        ";

        // Prepare the statement
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':residentId', $residentId, PDO::PARAM_INT);

        // Execute the query
        $stmt->execute();

        // Fetch the result as an associative array
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the result
        return $result ? $result : ["error" => "Resident not found"];
    } catch (PDOException $e) {
        // Handle any errors
        return ["error" => "Database query failed: " . $e->getMessage()];
    }
}

// Check if a resident ID is provided in the GET request
if (isset($_GET['id'])) {
    $residentId = $_GET['id'];
    $resident = fetchResidentById($conn, $residentId);

    // Return the result as JSON
    header('Content-Type: application/json');
    echo json_encode($resident);
} else {
    // Return an error if no ID is provided
    header('Content-Type: application/json');
    echo json_encode(["error" => "No resident ID provided"]);
}
?>
