<?php
// Include the database connection
require_once 'connect.php';

try {
    // Prepare and execute the SQL query to fetch mission and vision
    $sql = "SELECT mission_title, mission_description, vision_title, vision_description FROM landing_mv_section LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch the result as an associative array
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Return the data as JSON
        echo json_encode([
            'status' => 'success',
            'data' => $result
        ]);
    } else {
        // No data found
        echo json_encode([
            'status' => 'error',
            'message' => 'No mission and vision found in the database.'
        ]);
    }
} catch (PDOException $e) {
    // Handle any errors
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching mission and vision: ' . $e->getMessage()
    ]);
}
?>
