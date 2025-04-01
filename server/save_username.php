<?php
session_start();
include 'connect.php'; // Database connection
include_once '../pages/log_activity.php'; // Include activity log functionality

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; // User-submitted username
    $email = $_SESSION['email']; // Retrieve the email from the session

    // Check if the username already exists in the 'admintb' table
    $stmt = $conn->prepare("SELECT * FROM admintb WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    // Check if the username already exists in the 'brgystaffinfotb' table
    $stmt2 = $conn->prepare("SELECT * FROM brgystaffinfotb WHERE username = :username");
    $stmt2->bindParam(':username', $username);
    $stmt2->execute();

    if ($stmt->rowCount() > 0 || $stmt2->rowCount() > 0) {
        // Username exists in either table, set an error message
        $_SESSION['error'] = "The username is already taken. Please choose another one.";
        header("Location: ../pages/create_username.php"); // Redirect back to the username creation page
        exit();
    }

    try {
        // Update the user's username in the 'admintb' table
        $stmt = $conn->prepare("UPDATE admintb SET username = :username WHERE email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email); // Use email from session
        $stmt->execute();

        // Log the activity
        $userId = $_SESSION['userid'];  
        $usertype = $_SESSION['usertype'];
        $fullname = $_SESSION['fullName']; 
        $action = "Updated username.";
        logActivity($conn, $userId, $usertype, $fullname, $action);

        // Set success message and redirect
        header("Location: ../pages/loginpage.php"); // Redirect to the login page or dashboard
        exit();
    } catch (PDOException $e) {
        // Error during update
        $_SESSION['error'] = "An error occurred while saving your username. Please try again.";
        header("Location: ../pages/create_username.php"); // Redirect back with error
        exit();
    }
}
?>
