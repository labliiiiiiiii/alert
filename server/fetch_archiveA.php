<?php
// Start session and include necessary files
include_once '../server/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session if it's not already started
}

// Assuming session has already started
$currentUserId = $_SESSION['userid'] ?? null;
$currentUserType = strtoupper(trim($_SESSION['position'])); // Convert to uppercase for consistency

// Base query to fetch posts with media details
$query = "
SELECT 
    a.archiveA_id,  -- Include the archiveA_id in the select statement
    a.announcement_id, 
    a.caption, 
    a.creator_type, 
    a.created_at, 
    a.archived_at, 
    u.BrgyName,
    u.img,  -- Assuming the column for logo is called 'logo' in brgystaffinfotb
    GROUP_CONCAT(am.media_id) AS media_ids,  -- Concatenate media ids into a single field
    GROUP_CONCAT(am.media_type) AS media_types,  -- Concatenate media types into a single field
    GROUP_CONCAT(am.media_data) AS media_data,  -- Concatenate media data into a single field
    GROUP_CONCAT(am.mime_type) AS mime_types,  -- Concatenate mime types into a single field
    GROUP_CONCAT(am.file_size) AS file_sizes,  -- Concatenate file sizes into a single field
    GROUP_CONCAT(am.archived_at) AS media_archived_ats  -- Concatenate media archived dates into a single field
FROM 
    announcementtb_archive a
LEFT JOIN 
    brgystaffinfotb u ON a.creator_id = u.userid
LEFT JOIN
    announcementmedia_archive am ON a.announcement_id = am.announcement_id
";

// Modify query logic based on user role
if ($currentUserType == 'MDRRMO CAINTA') {
    // MDRRMO Cainta can see all posts
    $query .= " GROUP BY a.announcement_id, a.caption, a.creator_type, a.created_at, a.archived_at, u.BrgyName, u.img ORDER BY a.archived_at DESC, a.created_at DESC";
} elseif ($currentUserType == 'BRGY STAFF') {
    // BRGY STAFF can only see their own posts
    if ($currentUserId) {
        $query .= " WHERE a.creator_id = :user_id GROUP BY a.announcement_id, a.caption, a.creator_type, a.created_at, a.archived_at, u.BrgyName, u.img ORDER BY a.archived_at DESC, a.created_at DESC";
    } else {
        echo json_encode(["status" => "error", "message" => "User ID is not valid."]);
        exit;
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid user role."]);
    exit;
}

// Prepare and execute the query
$stmt = $conn->prepare($query);

if ($currentUserType == 'BRGY STAFF') {
    // Bind the user ID if the user is BRGY STAFF
    $stmt->bindParam(':user_id', $currentUserId, PDO::PARAM_INT);
}

$stmt->execute();

// Fetch the results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group the posts by archived_at date
$grouped_posts = [];
foreach ($result as $row) {
    $archived_at = date('F j, Y', strtotime($row['archived_at'])); // Format archived date
    $grouped_posts[$archived_at][] = $row; // Group posts by the archived date
}

// Now $grouped_posts contains the grouped data with the archiveA_id for each post

?>
