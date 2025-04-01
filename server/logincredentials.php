<?php
session_start(); 
include_once 'connect.php'; 
include_once '../pages/log_activity.php'; // Include log activity script

if (!isset($_SESSION['userid'])) {
    error_log("Session userid not set in fetchlogincredentials.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any existing session data
    session_unset();
    session_destroy();
    session_start();

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? ''); // Plaintext password from login form

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username and password are required.';
        header("Location: ../MAIN_LOGIN");
        exit();
    }

    try {
        $user = null; // Placeholder for user data
        $position = 'Unknown Role'; // Default position
        $redirectPage = '../MAIN_LOGIN'; // Default redirect

        // Check in the admin table
        $stmt = $conn->prepare("SELECT * FROM admintb WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $user['usertype'] = 'admin'; // Identify user type

            $_SESSION['usertype'] = 'admin'; // Save usertype to session
            $position = 'MDRRMO Cainta';
            $redirectPage = '../pages/adminlandingpage'; 
        }

        // Check in the staff table if not found in admin table
        if (!$user) {
            $stmt = $conn->prepare("SELECT * FROM brgystaffinfotb WHERE username = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $user['usertype'] = 'brgyhead'; // Identify user type
                $_SESSION['usertype'] = 'brgyhead'; // Save usertype to session
                $position = 'BRGY Staff';
                $redirectPage = '../pages/BRGYcaintamappage'; // Staff dashboard
            }
        }

        // If no user found or password does not match
        if (!$user) {
            $_SESSION['error'] = 'Invalid username.';
            header("Location: ../MAIN_LOGIN");
            exit();
        } elseif (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'The password is wrong.';
            header("Location: ../MAIN_LOGIN");
            exit();
        }

        // Set session variables for valid login
        $_SESSION['userid'] = $user['userid'];
        $_SESSION['username'] = $user['username'];

        $_SESSION['position'] = $position; // Save dynamic role/position

        // Set Barangay details
        $_SESSION['BrgyId'] = $user['BrgyId'] ?? '';
        $_SESSION['BrgyName'] = $user['BrgyName'] ?? '';

        // Set individual name components in the session
        $_SESSION['firstname'] = $user['firstname'] ?? 'Guest';
        $_SESSION['middlename'] = $user['middlename'] ?? '';
        $_SESSION['surname'] = $user['surname'] ?? '';

        // Create full name from first, middle, and surname
        $firstName = $user['firstname'] ?? '';
        $middleName = $user['middlename'] ?? '';
        $surname = $user['surname'] ?? '';
        $fullName = trim("$firstName $middleName $surname");

        $_SESSION['fullName'] = $fullName; // Store full name in session

        // Fetch and encode profile image if available
        if (!empty($user['img'])) {
            $_SESSION['img'] = base64_encode($user['img']); // Convert image to base64
        } else {
            $_SESSION['img'] = 'No Logo Available'; // Fallback for no image
        }

        // Prevent session fixation attacks
        session_regenerate_id(true);

        // Log the login activity
        logActivity($conn, $_SESSION['userid'], $_SESSION['usertype'], $_SESSION['fullName'], "User logged into the system.");
        
        // Redirect to the user-specific dashboard
        header("Location: $redirectPage");
        exit();
    } catch (PDOException $e) {
        // Log database errors
        error_log('Database error: ' . $e->getMessage(), 3, __DIR__ . '/error.log');

        // Redirect with a generic error
        $_SESSION['error'] = 'An unexpected error occurred. Please try again later.';
        header("Location: ../MAIN_LOGIN");
        exit();
    }
}
?>
