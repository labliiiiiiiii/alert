<?php
// Include the database connection file
include('connect.php');

// Start the session to access user data (assuming user role and ID are stored in the session)
session_start();

// Assuming the session has been started and the role is saved in $_SESSION['position']
$currentUserId = $_SESSION['userid'] ?? null;
$currentUserType = strtoupper(trim($_SESSION['position'])); // Convert to uppercase for consistency

// Set the content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in and has a valid role
if (!$currentUserId || !$currentUserType) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to archive a post."]);
    exit;
}

// Get the post ID from the request
$announcement_id = intval($_GET['announcement_id']); // Assuming the post ID is passed via GET

// Check if the post ID is valid
if (!$announcement_id) {
    echo json_encode(["status" => "error", "message" => "Invalid announcement ID."]);
    exit;
}

// Check if the user has permission to archive the post
if ($currentUserType == 'MDRRMO CAINTA') {
    // Admin can archive any post
    $check_creator_query = "SELECT creator_id FROM announcementtb WHERE announcement_id = :announcement_id";
} elseif ($currentUserType == 'BRGY STAFF') {
    // Brgy head can only archive their own posts
    $check_creator_query = "SELECT creator_id FROM announcementtb WHERE announcement_id = :announcement_id AND creator_id = :user_id";
} else {
    echo json_encode(["status" => "error", "message" => "Invalid user role."]);
    exit;
}

// Prepare and execute the check query
$stmt = $conn->prepare($check_creator_query);
$stmt->bindParam(':announcement_id', $announcement_id, PDO::PARAM_INT);
if ($currentUserType == 'BRGY STAFF') {
    $stmt->bindParam(':user_id', $currentUserId, PDO::PARAM_INT);
}
$stmt->execute();

// Fetch the result
$creator_id = $stmt->fetchColumn();

// If no creator is found or the creator ID doesn't match, deny access
if (!$creator_id || ($creator_id != $currentUserId && $currentUserType !== 'MDRRMO CAINTA')) {
    echo json_encode(["status" => "error", "message" => "You do not have permission to archive this post."]);
    exit;
}

// If authorized, return success status
echo json_encode(["status" => "success", "message" => "User authorized to archive this post."]);
