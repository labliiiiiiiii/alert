<?php
// Include the database connection file
include('connect.php');
include_once '../pages/log_activity.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session if it's not already started
}

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

// Get the post ID from the request (e.g., via GET or POST)
$announcement_id = intval($_GET['announcement_id']); // Assuming the post ID is passed via GET

// Check if the post ID is valid
if (!$announcement_id) {
    echo json_encode(["status" => "error", "message" => "Invalid announcement ID."]);
    exit;
}

// Debugging: Print session variables to check
error_log("User ID: " . $currentUserId);
error_log("User Type: " . $currentUserType);

// Step 1: Check if the user has permission to archive the post
// Admin can archive any post; Brgy head can only archive their own posts
if ($currentUserType == 'MDRRMO CAINTA') {
    // Admin can archive any post
    $check_creator_query = "SELECT creator_id FROM announcementtb WHERE announcement_id = :announcement_id";
} elseif ($currentUserType == 'BRGY STAFF') {
    // Brgyhead can only archive their own posts
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

// Step 2: Archive the post if user is authorized
$conn->beginTransaction();

try {
    // Insert the announcement into the archive table and get the archiveA_id
    $insert_announcement = "INSERT INTO announcementtb_archive (announcement_id, creator_id, creator_type, caption, created_at, updated_at, archived_at)
                            SELECT announcement_id, creator_id, creator_type, caption, created_at, updated_at, CURRENT_TIMESTAMP
                            FROM announcementtb WHERE announcement_id = :announcement_id";
    $stmt = $conn->prepare($insert_announcement);
    $stmt->bindParam(':announcement_id', $announcement_id, PDO::PARAM_INT);
    $stmt->execute();

    // Get the last inserted archiveA_id
    $archiveA_id = $conn->lastInsertId();

    // Insert the media into the archive table with the archiveA_id
    $insert_media = "INSERT INTO announcementmedia_archive (media_id, announcement_id, media_data, media_type, created_at, file_size, mime_type, archived_at, archiveA_id)
                     SELECT media_id, announcement_id, media_data, media_type, created_at, file_size, mime_type, CURRENT_TIMESTAMP, :archiveA_id
                     FROM announcement_media WHERE announcement_id = :announcement_id";

    $stmt = $conn->prepare($insert_media);
    $stmt->bindParam(':announcement_id', $announcement_id, PDO::PARAM_INT);
    $stmt->bindParam(':archiveA_id', $archiveA_id, PDO::PARAM_INT);
    $stmt->execute();

    // Delete the announcement and media from the original tables
    $delete_announcement = "DELETE FROM announcementtb WHERE announcement_id = :announcement_id";
    $stmt = $conn->prepare($delete_announcement);
    $stmt->bindParam(':announcement_id', $announcement_id, PDO::PARAM_INT);
    $stmt->execute();

    $delete_media = "DELETE FROM announcement_media WHERE announcement_id = :announcement_id";
    $stmt = $conn->prepare($delete_media);
    $stmt->bindParam(':announcement_id', $announcement_id, PDO::PARAM_INT);
    $stmt->execute();

    // Commit the transaction
    $conn->commit();

    echo json_encode(["status" => "success", "message" => "Announcement successfully archived"]);

    // Log the activity
    $userId = $_SESSION['userid'];
    $usertype = $_SESSION['usertype'];
    $fullname = $_SESSION['fullName'];
    $action = "Archived an announcement.";
    logActivity($conn, $userId, $usertype, $fullname, $action);

} catch (Exception $e) {
    // Rollback the transaction if something goes wrong
    $conn->rollBack();

    echo json_encode(["status" => "error", "message" => "Error archiving announcement: " . $e->getMessage()]);
}
?>
