<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection and log activity function
require_once 'connect.php';
include_once '../pages/log_activity.php';

// Set the header for JSON response
header('Content-Type: application/json');

try {
    // Check if the request is for a single resident or multiple residents
    if (isset($_POST['fullname'])) {
        // Single resident entry
        saveSingleResident($conn);
    } elseif (isset($_POST['residents'])) {
        // Multiple resident entries
        saveMultipleResidents($conn);
    } else {
        $_SESSION['error'] = "Missing required fields";
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit; // Stop further execution
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

// Close the connection
$conn = null;

// Function to save a single resident's information
function saveSingleResident($conn) {
    // Get form data
    $fullname = ucwords(strtolower($_POST['fullname']));
    $sex = $_POST['sex'];
    $birthdate = $_POST['birthdate'];
    $age = $_POST['age'];
    $contact = $_POST['contact'];
    $province = ucwords(strtolower($_POST['province']));
    $municipal = ucwords(strtolower($_POST['municipal']));
    $barangay_id = $_POST['barangay_id']; // Get the barangay ID
    $address = ucwords(strtolower($_POST['address']));
    $added_by = $_SESSION['userid']; // Use the userid from the session

    // Prepare the SQL statement
    $sql = "INSERT INTO residentinfo (fullname, sex, birthdate, age, contact, province, municipal, barangay, address, added_by)
            VALUES (:fullname, :sex, :birthdate, :age, :contact, :province, :municipal, :barangay, :address, :added_by)";
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':sex', $sex);
    $stmt->bindParam(':birthdate', $birthdate);
    $stmt->bindParam(':age', $age);
    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':province', $province);
    $stmt->bindParam(':municipal', $municipal);
    $stmt->bindParam(':barangay', $barangay_id); // Bind the barangay ID
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':added_by', $added_by);

    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['success'] = "Resident information saved successfully";
        echo json_encode(["success" => true, "message" => "Resident information saved successfully"]);

        // Log the activity
        $userId = $_SESSION['userid'];
        $usertype = $_SESSION['usertype'];
        $fullname = $_SESSION['fullName'];
        $action = "Added a new resident record.";
        logActivity($conn, $userId, $usertype, $fullname, $action);

    } else {
        $_SESSION['error'] = "Error saving resident information";
        echo json_encode(["success" => false, "message" => "Error saving resident information"]);
    }
}

// Function to save multiple residents' information
function saveMultipleResidents($conn) {
    // Get the residents data
    $residents = json_decode($_POST['residents'], true);
    $added_by = $_SESSION['userid']; // Use the userid from the session

    // Prepare the SQL statement
    $sql = "INSERT INTO residentinfo (fullname, sex, birthdate, age, contact, province, municipal, barangay, address, added_by)
            VALUES (:fullname, :sex, :birthdate, :age, :contact, :province, :municipal, :barangay, :address, :added_by)";
    $stmt = $conn->prepare($sql);

    // Loop through the residents and insert each one
    foreach ($residents as $resident) {
        // Capitalize the first letter of each word
        $fullname = ucwords(strtolower($resident['fullname']));
        $province = ucwords(strtolower($resident['province']));
        $municipal = ucwords(strtolower($resident['municipal']));
        $address = ucwords(strtolower($resident['address']));

        // Bind parameters
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':sex', $resident['sex']);
        $stmt->bindParam(':birthdate', $resident['birthday']);
        $stmt->bindParam(':age', $resident['age']);
        $stmt->bindParam(':contact', $resident['contact']);
        $stmt->bindParam(':province', $province);
        $stmt->bindParam(':municipal', $municipal);
        $stmt->bindParam(':barangay', $resident['barangay_id']); // Bind the barangay ID
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':added_by', $added_by);

        // Execute the statement
        if (!$stmt->execute()) {
            $_SESSION['error'] = "Error saving resident information";
            echo json_encode(["success" => false, "message" => "Error saving resident information"]);
            return;
        }
    }

    $_SESSION['success'] = "Resident information saved successfully";
    echo json_encode(["success" => true, "message" => "Resident information saved successfully"]);

    // Log the activity
    $userId = $_SESSION['userid'];
    $usertype = $_SESSION['usertype'];
    $fullname = $_SESSION['fullName'];
    $action = "Added multiple resident records.";
    logActivity($conn, $userId, $usertype, $fullname, $action);
}
?>
