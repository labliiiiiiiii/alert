<?php
// Include the database connection file
require_once 'connect.php';

// Function to fetch resident information along with the Barangay name
function fetchResidentInfo($conn) {
    try {
        // SQL query to fetch all resident information along with the Barangay name
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
        ";
    
        
        // Execute the query
        $stmt = $conn->query($sql);

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
$residents = fetchResidentInfo($conn);

?>
