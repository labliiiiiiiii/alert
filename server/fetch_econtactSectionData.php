<?php
// Start session and include the database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../server/connect.php'; // Ensure $conn is a PDO object
include_once '../pages/log_activity.php'; // Include activity log function

// Fetch all emergency contact section data to display
try {
    $sql = "SELECT * FROM landing_emergencycon_section";
    $stmt = $conn->query($sql);
    $emergencyContactSectionData = $stmt->fetchAll(PDO::FETCH_ASSOC); // Return all rows as an associative array
} catch (PDOException $e) {
    $emergencyContactSectionData = ["error" => "Error fetching data: " . $e->getMessage()]; // Handle any database error
}

// Handle form submission for updating the emergency contact section
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success = false;

    // Validate and sanitize input data
    $contactNumber = trim($_POST['contactNumber']);
    $description = trim($_POST['description']);
    $contactNumber2 = trim($_POST['contactNumber2']);
    $description2 = trim($_POST['description2']);

    // Check if any field is empty
    if (empty($contactNumber) || empty($description) || empty($contactNumber2) || empty($description2)) {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        try {
            // Prepare the SQL query to update the landing_emergencycon_section
            $query = "UPDATE landing_emergencycon_section
                      SET contact_number = :contactNumber,
                          description = :description,
                          contact_number_2 = :contactNumber2,
                          description_2 = :description2,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE emergency_id = 1"; // Assuming emergency_id is 1
            $stmt = $conn->prepare($query);

            // Bind parameters
            $stmt->bindValue(':contactNumber', $contactNumber, PDO::PARAM_STR);
            $stmt->bindValue(':description', $description, PDO::PARAM_STR);
            $stmt->bindValue(':contactNumber2', $contactNumber2, PDO::PARAM_STR);
            $stmt->bindValue(':description2', $description2, PDO::PARAM_STR);

            // Execute the query
            if ($stmt->execute()) {
                $success = true;

                // Log the activity after successful update
                $userId = $_SESSION['userid'];  
                $usertype = $_SESSION['usertype'];
                $fullname = $_SESSION['fullName']; 
                $action = "Updated emergency contact information.";
                logActivity($conn, $userId, $usertype, $fullname, $action);
            } else {
                $errors[] = "Failed to update the Emergency Contact section.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error updating entry: " . $e->getMessage();
        }
    }

    // Handle success or error messages
    if ($success) {
        $_SESSION['success'] = "Emergency Contact Updated Successfully.";
    }
    if (!empty($errors)) {
        $_SESSION['error'] = implode(" ", $errors);
    }

    header("Location: ../pages/settingslandingEMERCON.php");
    exit();
}
?>
