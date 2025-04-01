<?php
// Include your connection file
require_once '../server/connect.php'; // Adjust the path as necessary
include_once '../pages/log_activity.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start(); // Start the session
    try {
        // Check if $conn is set
        if (!isset($conn)) {
            throw new Exception("Database connection is not established.");
        }

        // Retrieve form data, including position
        $BrgyName = $_POST['barangay_name'];
        $firstname = $_POST['firstname'];
        $middlename = isset($_POST['middlename']) ? $_POST['middlename'] : null;
        $surname = $_POST['surname'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Set default position if not provided
        $position = isset($_POST['position']) ? $_POST['position'] : 'staff'; // Default to 'Staff Member'

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if the username already exists in brgystaffinfotb
        $checkUsernameQuery = "SELECT COUNT(*) FROM brgystaffinfotb WHERE username = :username";
        $checkUsernameStmt = $conn->prepare($checkUsernameQuery);
        $checkUsernameStmt->bindParam(':username', $username);
        $checkUsernameStmt->execute();
        $usernameCountBrgy = $checkUsernameStmt->fetchColumn();

        // Check if the username already exists in admintb
        $checkUsernameQueryAdmin = "SELECT COUNT(*) FROM admintb WHERE username = :username";
        $checkUsernameStmtAdmin = $conn->prepare($checkUsernameQueryAdmin);
        $checkUsernameStmtAdmin->bindParam(':username', $username);
        $checkUsernameStmtAdmin->execute();
        $usernameCountAdmin = $checkUsernameStmtAdmin->fetchColumn();

        // If username exists in either table, return an error
        if ($usernameCountBrgy > 0 || $usernameCountAdmin > 0) {
            $_SESSION['error'] = "Username already exists.";
        } else {
            // Generate BrgyId (like 'BRGY01', 'BRGY02', etc.)
            $maxIdQuery = "SELECT MAX(CAST(SUBSTRING(BrgyId, 5) AS UNSIGNED)) AS max_id FROM brgystaffinfotb WHERE BrgyId LIKE 'BRGY%'";
            $maxIdStmt = $conn->prepare($maxIdQuery);
            $maxIdStmt->execute();
            $maxIdResult = $maxIdStmt->fetch(PDO::FETCH_ASSOC);
            $max_id = $maxIdResult['max_id'] ? $maxIdResult['max_id'] : 0;
            $newBrgyId = 'BRGY' . str_pad($max_id + 1, 2, '0', STR_PAD_LEFT);

            // SQL insert statement for brgystaffinfotb, including the position
            $sql = "INSERT INTO brgystaffinfotb (
                BrgyId, BrgyName, firstname, middlename, surname, username, password, position
            ) VALUES (
                :BrgyId, :BrgyName, :firstname, :middlename, :surname, :username, :password, :position
            )";

            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':BrgyId', $newBrgyId);
            $stmt->bindParam(':BrgyName', $BrgyName);
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':middlename', $middlename, PDO::PARAM_STR | PDO::PARAM_NULL);
            $stmt->bindParam(':surname', $surname);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword); // Bind the hashed password
            $stmt->bindParam(':position', $position); // Bind the position (now defaulted to 'Staff Member')

            // Execute the statement
            if ($stmt->execute()) {
                // Retrieve the last inserted userid
                $userid = $conn->lastInsertId();

                // SQL insert statement for barangaytb
                $sqlBarangay = "INSERT INTO barangaytb (staff_userid) VALUES (:userid)";
                $stmtBarangay = $conn->prepare($sqlBarangay);
                $stmtBarangay->bindParam(':userid', $userid);

                // Execute the statement for barangaytb
                if ($stmtBarangay->execute()) {
                    $_SESSION['success'] = "New record created successfully.";
                    $userId = $_SESSION['userid'];
                    $usertype = $_SESSION['usertype'];
                    $fullname = $_SESSION['fullName'];
                    $action = "Added a new barangay account.";
                    logActivity($conn, $userId, $usertype, $fullname, $action);
                } else {
                    $_SESSION['error'] = "Error: Unable to create record in barangaytb.";
                }
            } else {
                $_SESSION['error'] = "Error: Unable to create record in brgystaffinfotb.";
            }
        }

    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Close the connection
    $conn = null;

    // Redirect back to the form page
    header("Location: ../pages/settingspage.php");
    exit();
}
?>
