<?php
session_start();
include 'connect.php';
include('../component/popupmsg.php'); // Include the popup message component

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['code']; // User-submitted code
    $email = $_SESSION['email']; // Email stored in session
    $password = $_SESSION['password']; // Password stored in session

    // Hash the password before saving it to the database
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Fetch the stored verification code and expiration time from the database
    $stmt = $conn->prepare("SELECT verification_code, verification_code_expiration FROM admintb WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stored_code = $user['verification_code'];
        $expiration_time = $user['verification_code_expiration'];

        // Check if the verification code matches and has not expired
        if ($entered_code == $stored_code && strtotime($expiration_time) > time()) {
            // Verification successful, insert the email and password into the database
            try {
                // Insert the user data into the admintb table
                $stmt = $conn->prepare("UPDATE admintb SET password = :password, status = 'active' WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->execute();

                // Optionally, clear the verification code and expiration to prevent reuse
                $stmt = $conn->prepare("UPDATE admintb SET verification_code = NULL, verification_code_expiration = NULL WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();


                unset($_SESSION['email']); // Clear email from session
                unset($_SESSION['password']); // Clear password from session
                $_SESSION['email'] = $email; // Store email for future use (e.g., saving username)
                header("Location: ../pages/create_username"); // Redirect to the create username page
                exit();
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header("Location: ../pages/verify_code.php"); // Redirect to the verification page with error
                exit();
            }
        } else {
            // If the code is expired, redirect the user with a proper message
            if (strtotime($expiration_time) < time()) {
                $_SESSION['verification_expired'] = true; // Set a flag to show the resend button
                header("Location: ../pages/verify_code"); // Redirect back to the verify code page
                exit();
            } else {
                $_SESSION['error'] = "Invalid verification code!";
                header("Location: ../pages/verify_code"); // Redirect back with error
                exit();
            }
        }
    } else {
        $_SESSION['error'] = "No verification code found for this email.";
        header("Location: ../pages/verify_code"); // Redirect to the verification page with error
        exit();
    }
}
?>
