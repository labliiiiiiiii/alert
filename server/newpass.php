<?php
session_start();
require '../server/connect.php'; // Include your database connection

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$token = $_POST['token'] ?? null; // Get the token from the form submission

if ($token) {
    // Query the database to find the user with the given token
    $stmt = $conn->prepare("SELECT * FROM admintb WHERE reset_token = :token AND reset_expires > :now");
    $stmt->execute(['token' => $token, 'now' => time()]);
    $user = $stmt->fetch();

    // If user is not found in admintb, check brgystaffinfotb
    if (!$user) {
        $stmt = $conn->prepare("SELECT * FROM brgystaffinfotb WHERE reset_token = :token AND reset_expires > :now");
        $stmt->execute(['token' => $token, 'now' => time()]);
        $user = $stmt->fetch();
    }

    if ($user) {
        // Proceed with password reset if token is valid
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $newPassword = $_POST['password'] ?? '';

            if ($newPassword) {
                $errors = [];

                // Check for at least 1 uppercase letter
                if (!preg_match('/[A-Z]/', $newPassword)) {
                    $errors[] = "Password must contain at least 1 uppercase letter.";
                }

                // Check for at least 1 special character
                if (!preg_match('/[\W_]/', $newPassword)) {
                    $errors[] = "Password must contain at least 1 special character.";
                }

                // Check for at least 1 number
                if (!preg_match('/\d/', $newPassword)) {
                    $errors[] = "Password must contain at least 1 number.";
                }

                // Check for at least 8 characters
                if (strlen($newPassword) < 8) {
                    $errors[] = "Password must be at least 8 characters long.";
                }

                // If there are any password validation errors, store them in the session for the popup
                if (!empty($errors)) {
                    // Create error messages with newline characters
                    $_SESSION['password_error'] = implode("\n", array_map('htmlspecialchars', $errors));
                    header("Location: ../pages/resetPass.php?token=$token");
                    exit();
                }

                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                try {
                    // Update the password in the database
                    $table = $user['BrgyId'] ? 'brgystaffinfotb' : 'admintb';
                    $stmt = $conn->prepare("UPDATE $table SET password = :password, reset_token = NULL, reset_expires = NULL WHERE reset_token = :token");
                    $stmt->execute(['password' => $hashedPassword, 'token' => $token]);


                    header("Location: ../pages/loginpage.php");
                    exit();
                } catch (PDOException $e) {
                    // Handle database error
                    $_SESSION['error'] = "Database error: " . $e->getMessage();
                    header("Location: ../pages/resetPass.php?token=$token");
                    exit();
                }
            } else {
                // Handle case where no password is entered
                $_SESSION['error'] = "Please enter a new password.";
                header("Location: ../pages/resetPass.php?token=$token");
                exit();
            }
        }
    } else {
        // Invalid or expired token
        $_SESSION['error'] = "Token is invalid or has expired.";
        header("Location: ../pages/resetPass.php");
        exit();
    }
} else {
    // Token is missing
    $_SESSION['error'] = "Token is missing.";
    header("Location: ../pages/resetPass.php");
    exit();
}
?>
