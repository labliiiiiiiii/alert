<?php
session_start();
include 'connect.php'; // Database connection

// Load PHPMailer using Composer autoload
require '../vendor/autoload.php'; // Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email']; // Retrieve the email from the session

    // Generate a new verification code
    $verification_code = rand(100000, 999999); // 6-digit code
    $expiration_time = date('Y-m-d H:i:s', strtotime('+1 hour')); // Set expiration time to 1 hour

    try {
        // Update the database with the new verification code and expiration time
        $stmt = $conn->prepare("UPDATE admintb SET verification_code = :verification_code, verification_code_expiration = :expiration_time WHERE email = :email");
        $stmt->bindParam(':verification_code', $verification_code);
        $stmt->bindParam(':expiration_time', $expiration_time);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Send the new verification code to the user's email using PHPMailer
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
            $mail->addAddress($email); // Recipient email

            $mail->Subject = 'Your New Verification Code';
            $mail->Body = "Hello,\n\n" . 
                          "Your new verification code is: $verification_code.\n\n" . 
                          "The code was requested from the i-Alert webite. It will be valid for 1 hours.\n\n" .
                          "Thank you for using our services!\n\n" . 
                          "Best regards,\n" . 
                          "CTRL FREAKS";

            // Send Email
            $mail->send();

            // Set success message and redirect to verification page
            header("Location: ../pages/verify_code");
            exit();

        } catch (Exception $e) {
            // If there’s an error while sending the email
            $_SESSION['error'] = "Email could not be sent. Error: {$mail->ErrorInfo}";
            header("Location: ../pages/verify_code");
            exit();
        }
    } catch (PDOException $e) {
        // Handle any database errors
        $_SESSION['error'] = "Error updating the verification code in the database. Please try again.";
        header("Location: ../pages/verify_code");
        exit();
    }
}
?>
