<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file
require_once 'connect.php';
include_once '../pages/log_activity.php';

// Set the header for JSON response
header('Content-Type: application/json');

// Get the selected resident IDs from the POST request
$residentIds = json_decode($_POST['residentids']);

// Check if any resident IDs are selected
if (is_array($residentIds) && count($residentIds) > 0) {
    try {
        foreach ($residentIds as $residentId) {
            // Fetch the resident's details from the residentinfo table
            $stmt = $conn->prepare("SELECT * FROM residentinfo WHERE residentid = :residentid");
            $stmt->bindParam(':residentid', $residentId, PDO::PARAM_INT);
            $stmt->execute();
            $resident = $stmt->fetch(PDO::FETCH_ASSOC);

            // If no resident is found, skip this iteration
            if (!$resident) {
                continue;
            }

            // Insert the resident data into the archive table
            $insertStmt = $conn->prepare("INSERT INTO residentinfo_archive
                (residentid, fullname, sex, age, birthdate, contact, province, municipal, barangay, added_by, address, created_at, updated_at, archived_at, archived_by)
                VALUES
                (:residentid, :fullname, :sex, :age, :birthdate, :contact, :province, :municipal, :barangay, :added_by, :address, :created_at, :updated_at, :archived_at, :archived_by)");

            $archivedAt = date('Y-m-d H:i:s'); // Current timestamp for archived_at
            $archivedBy = $_SESSION['userid']; // Use the userid from the session

            $insertStmt->bindParam(':residentid', $resident['residentid'], PDO::PARAM_INT);
            $insertStmt->bindParam(':fullname', $resident['fullname'], PDO::PARAM_STR);
            $insertStmt->bindParam(':sex', $resident['sex'], PDO::PARAM_STR);
            $insertStmt->bindParam(':age', $resident['age'], PDO::PARAM_INT);
            $insertStmt->bindParam(':birthdate', $resident['birthdate'], PDO::PARAM_STR);
            $insertStmt->bindParam(':contact', $resident['contact'], PDO::PARAM_INT);
            $insertStmt->bindParam(':province', $resident['province'], PDO::PARAM_STR);
            $insertStmt->bindParam(':municipal', $resident['municipal'], PDO::PARAM_STR);
            $insertStmt->bindParam(':barangay', $resident['barangay'], PDO::PARAM_INT);
            $insertStmt->bindParam(':added_by', $resident['added_by'], PDO::PARAM_STR);
            $insertStmt->bindParam(':address', $resident['address'], PDO::PARAM_STR);
            $insertStmt->bindParam(':created_at', $resident['created_at'], PDO::PARAM_STR);
            $insertStmt->bindParam(':updated_at', $resident['updated_at'], PDO::PARAM_STR);
            $insertStmt->bindParam(':archived_at', $archivedAt, PDO::PARAM_STR);
            $insertStmt->bindParam(':archived_by', $archivedBy, PDO::PARAM_STR);

            $insertStmt->execute();

            // Now, delete the resident from the original table
            $deleteStmt = $conn->prepare("DELETE FROM residentinfo WHERE residentid = :residentid");
            $deleteStmt->bindParam(':residentid', $residentId, PDO::PARAM_INT);
            $deleteStmt->execute();

            // Log the activity
            $userId = $_SESSION['userid'];
            $usertype = $_SESSION['usertype'];
            $fullname = $_SESSION['fullName'];
            $action = "Archived a resident record.";
            logActivity($conn, $userId, $usertype, $fullname, $action);
        }

        // Return a JSON response indicating success
        echo json_encode(['status' => 'success', 'message' => 'Residents archived successfully.']);
    } catch (Exception $e) {
        // Return an error response if any exception occurs
        echo json_encode(['status' => 'error', 'message' => 'Error archiving residents: ' . $e->getMessage()]);
    }
} else {
    // Return error if no residents are selected
    echo json_encode(['status' => 'error', 'message' => 'No residents selected.']);
}
?>
