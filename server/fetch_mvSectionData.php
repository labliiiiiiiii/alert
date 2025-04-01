<?php
// Start session and include the database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../server/connect.php'; // Ensure $conn is a PDO object
include_once '../pages/log_activity.php'; // Include activity log function

// Handle form submission for updating the mission and vision section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $missionTitle = trim($_POST['missionTitle']);
    $missionDescription = trim($_POST['missionDescription']);
    $visionTitle = trim($_POST['visionTitle']);
    $visionDescription = trim($_POST['visionDescription']);

    // Validate input
    if (empty($missionTitle) || empty($missionDescription) || empty($visionTitle) || empty($visionDescription)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../pages/settingslandingMISSIONVISSION.php");
        exit();
    }

    try {
        // Prepare the SQL query to update the landing_mv_section
        $query = "UPDATE landing_mv_section 
                  SET mission_title = :missionTitle, 
                      mission_description = :missionDescription, 
                      vision_title = :visionTitle, 
                      vision_description = :visionDescription, 
                      updated_at = CURRENT_TIMESTAMP";
        $stmt = $conn->prepare($query);

        // Bind parameters
        $stmt->bindValue(':missionTitle', $missionTitle, PDO::PARAM_STR);
        $stmt->bindValue(':missionDescription', $missionDescription, PDO::PARAM_STR);
        $stmt->bindValue(':visionTitle', $visionTitle, PDO::PARAM_STR);
        $stmt->bindValue(':visionDescription', $visionDescription, PDO::PARAM_STR);

        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['success'] = "Mission and Vision Updated Successfully.";

            // Log the activity after successful update
            $userId = $_SESSION['userid'];  
            $usertype = $_SESSION['usertype'];
            $fullname = $_SESSION['fullName']; 
            $action = "Updated mission and vision statement.";
            logActivity($conn, $userId, $usertype, $fullname, $action);
        } else {
            $_SESSION['error'] = "Failed to update the Mission and Vision section.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: ../pages/settingslandingMISSIONVISSION.php");
    exit();
}

// Fetch the mission and vision section data to display
try {
    $sql = "SELECT * FROM landing_mv_section LIMIT 1";
    $stmt = $conn->query($sql);
    $mvSectionData = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the data as an associative array

    // Check if no data is found
    if ($mvSectionData === false) {
        $mvSectionData = []; // Return an empty array if no data is found
    }
} catch (PDOException $e) {
    $mvSectionData = ["error" => "Error fetching data: " . $e->getMessage()]; // Handle database error
}
?>

<!-- Your HTML code here, using $mvSectionData safely -->
