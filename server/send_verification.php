<?php
session_start();
include 'connect.php';

// Load PHPMailer using Composer autoload
require '../vendor/autoload.php'; // Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $verification_code = rand(100000, 999999);  // Generate a random verification code
    $expiration_time = date('Y-m-d H:i:s', strtotime('+1 hours')); // Set expiration time to 1 hours

    // Store email and verification details in session
    $_SESSION['email'] = $email;
    $_SESSION['password'] = $password;

    // Initialize an array to store error or success messages
    $messages = [];

    // Check if the email already exists in the admintb table
    $stmt = $conn->prepare("SELECT * FROM admintb WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $messages[] = ['text' => 'The email address is already registered.', 'type' => 'error'];
    }

    // Check if the email already exists in the brgystaffinfotb table
    $stmt = $conn->prepare("SELECT * FROM brgystaffinfotb WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $messages[] = ['text' => 'The email address is already registered in the system.', 'type' => 'error'];
    }

    // If there are errors, store them in session and redirect
    if (!empty($messages)) {
        $_SESSION['message'] = $messages;  // Store all messages (error or success) in the session
        header("Location: ../pages/signuppage"); // Redirect back to the sign-up page with messages
        exit();
    }

    try {
        // Insert email temporarily into the admintb database (no password yet)
        $stmt = $conn->prepare("INSERT INTO admintb (email, password, status, verification_code, verification_code_expiration)
                               VALUES (:email, NULL, 'inactive', :verification_code, :expiration_time)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':verification_code', $verification_code);
        $stmt->bindParam(':expiration_time', $expiration_time);
        $stmt->execute();

        // Send the verification code to the user's email
        $mail = new PHPMailer(true);
        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'evileignlauren@gmail.com'; // Your Gmail
            $mail->Password = 'zagt vzwa rbgu hgfa'; // Your App Password from Google
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Content
            $mail->setFrom('noreply@yourdomain.com', 'i-Alert');
            $mail->addAddress($email);
            $mail->Subject = 'Your Verification Code';
            $mail->Body = "Hello,\n\n" .
                          "$verification_code is your one-time password (OTP) for the i-Alert website.\n\n" .
                          "The code was requested from the i-Alert webite. It will be valid for 1 hours.\n\n" .
                          "Thank you for using our services!\n\n" . 
                          "Best regards,\n" .
                          "CTRL FREAKS";

            // Send Email
            $mail->send();

            // Success message after sending email
            $_SESSION['message'] = [['text' => 'A verification code has been sent to your email.', 'type' => 'success']];
            header("Location: ../pages/verify_code"); // Redirect to the verification page
            exit();
        } catch (Exception $e) {
            // Set error message in session to display in popup
            $_SESSION['message'] = [['text' => "Email could not be sent. Error: {$mail->ErrorInfo}", 'type' => 'error']];
            header("Location: ../pages/signuppage"); // Redirect to the sign-up page with error
            exit();
        }
    } catch (PDOException $e) {
        // Set error message in session to display in popup
        $_SESSION['message'] = [['text' => "Database error: " . $e->getMessage(), 'type' => 'error']];
        header("Location: ../pages/signuppage"); // Redirect to the sign-up page with error
        exit();
    }
}
?>
