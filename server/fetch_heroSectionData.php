<?php
// Start session and include the database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../server/connect.php'; // Ensure $conn is a PDO object
include_once '../pages/log_activity.php'; // Include activity log function

// Handle form submission for updating hero section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $heading = trim($_POST['heading']);
    $description = trim($_POST['description']);

    // Validate input
    if (empty($heading) || empty($description)) {
        $_SESSION['error'] = "Fields are required.";
        header("Location: ../pages/landingeditorpage.php");
        exit();
    }

    try {
        // Prepare the SQL query to update the landing_homepage_section
        $query = "UPDATE landing_homepage_section 
                  SET heading = :heading, paragraph = :paragraph, updated_at = CURRENT_TIMESTAMP 
                  WHERE section_id = :section_id";
        $stmt = $conn->prepare($query);

        // Bind parameters
        $sectionId = 'hero'; // Assuming 'hero' as the section_id for the hero section
        $stmt->bindValue(':heading', $heading, PDO::PARAM_STR);
        $stmt->bindValue(':paragraph', $description, PDO::PARAM_STR);
        $stmt->bindValue(':section_id', $sectionId, PDO::PARAM_STR);

        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['success'] = "Home Page Updated Successfully.";

            // Log the activity after successful update
            $userId = $_SESSION['userid'];  
            $usertype = $_SESSION['usertype'];
            $fullname = $_SESSION['fullName']; 
            $action = "Modified home page landing section.";
            logActivity($conn, $userId, $usertype, $fullname, $action);
        } else {
            $_SESSION['error'] = "Failed to update the hero section.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: ../pages/landingeditorpage.php");
    exit();
}

// Fetch the hero section data to display
try {
    $sql = "SELECT * FROM landing_homepage_section WHERE section_id = 'hero' LIMIT 1";
    $stmt = $conn->query($sql);
    $heroSectionData = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the data as an associative array

    // Check if no data is found
    if ($heroSectionData === false) {
        $heroSectionData = []; // Return an empty array if no data is found
    }
} catch (PDOException $e) {
    $heroSectionData = ["error" => "Error fetching data: " . $e->getMessage()]; // Handle database error
}
?>

<!-- Your HTML code here, using $heroSectionData safely -->
