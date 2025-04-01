<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session
header('Content-Type: application/json');

// Debugging: Check session variables
if (!isset($_SESSION['userid'], $_SESSION['position'])) {
    error_log('Session variables are missing');
    echo json_encode(['error' => 'Session variables are missing']);
    exit;
}

// Normalize session variables
$userId = $_SESSION['userid'];
$userType = strtoupper(trim($_SESSION['position'])); // Convert to uppercase for consistency

// Include database connection
include_once '../server/connect.php';
if (!$conn) {
    error_log('Database connection failed');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    $creatorName = '';
    $logoPath = '';

    error_log("User type: $userType");
    error_log("User ID: $userId");

    if ($userType === 'MDRRMO CAINTA') {
        $creatorName = 'MDRRMO Cainta';
        $logoPath = '../img/LOGO/MDRRMO1.png';
    } elseif ($userType === 'BRGY STAFF') {
        // Correct query to fetch BrgyName
        $stmt = $conn->prepare("SELECT BrgyName, img FROM brgystaffinfotb WHERE userid = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Use BrgyName without parentheses
            $creatorName = 'Barangay ' . (!empty($row['BrgyName']) ? htmlspecialchars($row['BrgyName']) : 'Unknown');

            // Use the logo image if it exists, or fall back to a default
            $logoPath = !empty($row['img']) ? 'data:image/png;base64,' . base64_encode($row['img']) : '../img/LOGO/default-brgy-logo.png';
        } else {
            error_log("No barangay staff found for user ID: $userId");
            echo json_encode(['error' => 'Barangay staff not found']);
            exit;
        }
    } else {
        error_log("Invalid user type: $userType");
        echo json_encode(['error' => 'Invalid user type', 'received' => $userType]);
        exit;
    }

    $response = [
        'creatorName' => $creatorName,
        'logoPath' => $logoPath,
    ];
    echo json_encode($response);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
} catch (Exception $e) {
    error_log('Server error: ' . $e->getMessage());
    echo json_encode(['error' => 'Server error']);
}
