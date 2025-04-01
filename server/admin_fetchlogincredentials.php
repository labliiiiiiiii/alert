<?php
// Start session for user authentication
include_once 'connect.php'; // Include your database connection file
include_once '../pages/log_activity.php'; // Include the log activity function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any existing session data
    session_unset();
    session_destroy();
    session_start();

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username and password are required.';
        header("Location: ../pages/adminloginpage");
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM admintb WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($password, $user['password'])) {
                $_SESSION['error'] = 'The password is wrong.';
                header("Location: ../pages/adminloginpage");
                exit();
            }

            // Set session variables for admin login
            $_SESSION['userid'] = $user['userid'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['usertype'] = 'admin';
            $_SESSION['position'] = 'MDRRMO Cainta';
            $_SESSION['fullName'] = $user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['surname'];
            $_SESSION['img'] = !empty($user['img']) ? base64_encode($user['img']) : 'No Logo Available';
            $_SESSION['BrgyId'] = $user['BrgyId'] ?? '';
            $_SESSION['BrgyName'] = $user['BrgyName'] ?? '';
            session_regenerate_id(true);

            // Log the admin login activity
            logActivity($conn, $_SESSION['userid'], $_SESSION['usertype'], $_SESSION['fullName'], "Admin logged into the system.");

            header("Location: ../pages/caintamappage"); // Redirect to admin dashboard
            exit();
        } else {
            $_SESSION['error'] = 'Invalid username or password.';
            header("Location: ../pages/adminloginpage");
            exit();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage(), 3, __DIR__ . '/error.log');
        $_SESSION['error'] = 'An unexpected error occurred. Please try again later.';
        header("Location: ../pages/adminloginpage");
        exit();
    }
}
?>
