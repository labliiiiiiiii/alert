<?php
session_start();
include '../server/connect.php';

$brgy = $_POST['barangay'];
$start = $_POST['startDate'];
$end = $_POST['endDate'];
$userType = $_SESSION['usertype'];
$sessionBrgy = $_SESSION['BrgyName'];

$query = "SELECT brgyName, waterLevel, humidity, temperature, dateTime FROM alerttb WHERE 1=1";

// Filter by user type (brgyhead)
if ($userType == 'brgyhead') {
    $query .= " AND brgyName = :sessionBrgy";
}

// Filter by barangay if it's not 'All' and the user is admin
if ($brgy !== "All" && $userType == 'admin') {
    $query .= " AND brgyName = :brgy";
}

// Filter by start date if provided
if (!empty($start)) {
    $query .= " AND DATE(dateTime) >= :start";
}

// Filter by end date if provided
if (!empty($end)) {
    $query .= " AND DATE(dateTime) <= :end";
}

// Always filter for today's data if no date range is given
if (empty($start) && empty($end)) {
    $query .= " AND DATE(dateTime) = CURDATE()"; // Only get today's data
}

$query .= " ORDER BY dateTime ASC";
$stmt = $conn->prepare($query);

// Bind parameters
if ($userType == 'brgyhead') {
    $stmt->bindValue(':sessionBrgy', $sessionBrgy);
}

if ($brgy !== "All" && $userType == 'admin') {
    $stmt->bindValue(':brgy', $brgy);
}

if (!empty($start)) {
    $stmt->bindValue(':start', $start);
}

if (!empty($end)) {
    $stmt->bindValue(':end', $end);
}

try {
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($data);
} catch (PDOException $e) {
    // Handle any errors with the database query
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
}
?>
