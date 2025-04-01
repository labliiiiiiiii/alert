<?php
include('../server/connect.php');

require '../vendor/autoload.php'; // Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check for email parameter in either POST or GET
$email = isset($_POST['email']) ? $_POST['email'] : (isset($_GET['email']) ? $_GET['email'] : null);

if ($email) {
    // Prepare and execute the query for admintb
    $stmt = $conn->prepare("SELECT * FROM admintb WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // If no user found in admintb, check brgystaffinfotb
    if (!$user) {
        $stmt = $conn->prepare("SELECT * FROM brgystaffinfotb WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
    }

    // Check if the user is found in either table
    if ($user) {
        // Generate token
        $token = bin2hex(random_bytes(50));
        $expires = time() + 3600;  // Token expires in 1 hour

        // Determine the table to update based on where the user was found
        if (isset($user['email']) && $user['email']) {
            if ($stmt->rowCount() > 0) {
                $table = 'admintb'; // User found in admintb
            } else {
                $table = 'brgystaffinfotb'; // User found in brgystaffinfotb
            }
        }

        // Save the token and expiration time
        $stmt = $conn->prepare("UPDATE $table SET reset_token = :token, reset_expires = :expires WHERE email = :email");

        // Check if the update is successful
        if ($stmt->execute(['token' => $token, 'expires' => $expires, 'email' => $email])) {
            // Generate the reset link
            $reset_link = "https://ivory-stinkbug-712526.hostingersite.com/pages/resetPass.php?token=" . urlencode($token);

            // Load the HTML email template
            $message = file_get_contents('../pages/FPemail_template.php');

            // Create PHPMailer instance
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();  // Set mailer to use SMTP
                $mail->Host = 'smtp.gmail.com';  // Set the SMTP server to Gmail
                $mail->SMTPAuth = true;  // Enable SMTP authentication
                $mail->Username = 'evileignlauren@gmail.com';  // Your Gmail address
                $mail->Password = 'zagt vzwa rbgu hgfa';  // Use an App Password if 2FA is enabled
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // TLS encryption
                $mail->Port = 587;  // Use port 587 for TLS

                $mail->setFrom('noreply@yourdomain.com', 'i-Alert');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';

                // Replace placeholders in the HTML template
                $message = str_replace('{{username}}', htmlspecialchars($user['username']), $message);
                $message = str_replace('{{reset_link}}', $reset_link, $message);

                $mail->Body = $message;

                // Send email
                $mail->send();

                // Redirect to the confirmation page
                header("Location: ../pages/FPconfirmemail.php?email=" . urlencode($email));
                exit();
            } catch (Exception $e) {
                // Log the error message instead of echoing it
                error_log("Error: {$mail->ErrorInfo}");
            }
        }
    } else {
        // Set the error message in the session
        session_start();
        $_SESSION['error'] = "No account found with that email address.";
        header("Location: ../pages/sendInstruction");
        exit();
    }
}
?>
