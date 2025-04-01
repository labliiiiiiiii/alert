<?php
session_start();
include_once '../server/connect.php';
include_once '../component/popupmsg.php';
include_once '../pages/log_activity.php'; // Include activity log functionality

header('Content-Type: text/plain'); // Set header for plain text response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_SESSION['userid'] ?? null;
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (!$userid) {
        echo "User not authenticated.";
        exit();
    }

    if (empty($currentPassword) || empty($newPassword)) {
        echo "All fields are required.";
        exit();
    }

    // Validate the new password
    $passwordPattern = "/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?\":{}|<>])(?=.*\d).{8,}$/";
    if (!preg_match($passwordPattern, $newPassword)) {
        echo "Password must contain at least 1 uppercase letter, 1 special character, 1 number, and be 8 characters or longer.";
        exit();
    }

    try {
        // Fetch the current hashed password
        $stmt = $conn->prepare("SELECT password FROM brgystaffinfotb WHERE userid = :userid");
        $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "User not found.";
            exit();
        }

        if (!password_verify($currentPassword, $user['password'])) {
            echo "Incorrect current password.";
            exit();
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the password in the database
        $updateStmt = $conn->prepare("UPDATE brgystaffinfotb SET password = :password, updated_at = NOW() WHERE userid = :userid");
        $updateStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $updateStmt->bindParam(':userid', $userid, PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            echo "Password updated successfully.";

            // Log the activity
            $userId = $_SESSION['userid'];  
            $usertype = $_SESSION['usertype'];
            $fullname = $_SESSION['fullName']; 
            $action = "Updated barangay account password.";
            logActivity($conn, $userId, $usertype, $fullname, $action);
        } else {
            echo "Failed to update password.";
        }
    } catch (PDOException $e) {
        echo "A server error occurred. Please try again later.";
    }
}
?>
