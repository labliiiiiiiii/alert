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
        header("Location: ../pages/brgyloginpage");
        exit();
    }

    try {
        // Fetch the barangay staff data from the database
        $stmt = $conn->prepare("SELECT * FROM brgystaffinfotb WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($password, $user['password'])) {
                $_SESSION['error'] = 'The password is wrong.';
                header("Location: ../pages/brgyloginpage");
                exit();
            }

            // Set session variables for barangay user login
            $_SESSION['userid'] = $user['userid'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['usertype'] = 'brgyhead';
            $_SESSION['position'] = 'BRGY Staff';
            $_SESSION['fullName'] = $user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['surname'];
            $_SESSION['img'] = !empty($user['img']) ? base64_encode($user['img']) : 'No Logo Available';
            $_SESSION['BrgyId'] = $user['BrgyId'] ?? '';
            $_SESSION['BrgyName'] = $user['BrgyName'] ?? '';
            session_regenerate_id(true);

            // Log the barangay staff login activity
            logActivity($conn, $_SESSION['userid'], $_SESSION['usertype'], $_SESSION['fullName'], "Barangay staff logged into the system.");

            header("Location: ../pages/BRGYcaintamappage.php"); // Redirect to barangay dashboard
            exit();
        } else {
            $_SESSION['error'] = 'Invalid username or password.';
            header("Location: ../pages/brgyloginpage");
            exit();
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage(), 3, __DIR__ . '/error.log');
        $_SESSION['error'] = 'An unexpected error occurred. Please try again later.';
        header("Location: ../pages/brgyloginpage");
        exit();
    }
}
?>
