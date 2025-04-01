<?php
session_start();
include '../server/connect.php';

header('Content-Type: application/json');

$userType = $_SESSION['usertype'];
$brgyName = $_SESSION['BrgyName'];

try {
    if ($userType == 'admin') {
        $query = "SELECT DISTINCT brgyName FROM alerttb";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $barangays = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($barangays);
    } elseif ($userType == 'brgyhead') {
        echo json_encode([$brgyName]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>