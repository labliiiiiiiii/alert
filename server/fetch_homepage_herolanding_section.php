<?php
// Include the database connection file
require_once 'connect.php';

try {
    // Fetch content for the 'hero' section from the database
    $sql = "SELECT * FROM landing_homepage_section WHERE section_id = 'hero' LIMIT 1";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Return the result as JSON
        echo json_encode($result);
    } else {
        // If no result is found
        echo json_encode(["error" => "No content found for the hero section."]);
    }
} catch (PDOException $e) {
    // Return error message if there's an issue with the query
    echo json_encode(["error" => "Error fetching data: " . $e->getMessage()]);
}
?>
