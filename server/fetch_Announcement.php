<?php
include_once '../server/connect.php';

session_start();
header('Content-Type: application/json');

try {
    $currentUserId = $_SESSION['userid'] ?? null;
    $currentUserType = $_SESSION['userRole'] ?? null; // Use 'admin' or 'brgyhead'

    // SQL query to retrieve announcements with their related media, creator info, and logos
    $sql = "
        SELECT 
            a.announcement_id, 
            a.creator_id, 
            a.creator_type, 
            a.caption, 
            a.created_at, 
             -- New field for sorting, formatted as M d, Y (e.g., Jan 30, 2020)
            DATE_FORMAT(a.created_at, '%b %d, %Y') AS created_at_for_sorting,
            m.media_id,
            m.media_data, 
            m.media_type, 
            m.created_at AS media_created_at, 
            m.file_size, 
            m.mime_type,
            CASE 
                WHEN a.creator_type = 'admin' THEN 'MDRRMO Cainta'
                WHEN a.creator_type = 'brgyhead' THEN bsi.BrgyName
            END AS creator_name,
            CASE 
                WHEN a.creator_type = 'admin' THEN NULL
                WHEN a.creator_type = 'brgyhead' THEN NULL
            END AS creator_surname,
            CASE
                WHEN a.creator_type = 'admin' THEN '../img/LOGO/MDRRMO1.png'
                WHEN a.creator_type = 'brgyhead' AND bsi.img IS NOT NULL THEN CONCAT('data:image/png;base64,', TO_BASE64(bsi.img))
                WHEN a.creator_type = 'brgyhead' THEN '../img/default_brgyhead_logo.png'
            END AS creator_logo
        FROM 
            announcementtb a 
        LEFT JOIN 
            announcement_media m 
        ON 
            a.announcement_id = m.announcement_id 
        LEFT JOIN 
            admintb ad 
        ON 
            a.creator_id = ad.userid AND a.creator_type = 'admin'
        LEFT JOIN 
            brgystaffinfotb bsi 
        ON 
            a.creator_id = bsi.userid AND a.creator_type = 'brgyhead'
        ORDER BY 
            a.created_at DESC
    ";

    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if no data is returned
    if (empty($rows)) {
        echo json_encode(['error' => false, 'message' => 'No announcements found', 'data' => []]);
        exit;
    }

    $announcements = [];
    foreach ($rows as $row) {
        $announcementId = $row['announcement_id'];

        // Initialize the announcement if not already set
        if (!isset($announcements[$announcementId])) {
            // Process caption for line breaks and bold hashtags
            $formattedCaption = nl2br(htmlspecialchars($row['caption']));
            $formattedCaption = preg_replace('/(#\w+)/', '<strong>$1</strong>', $formattedCaption);

            $announcements[$announcementId] = [
                'announcement_id' => $row['announcement_id'],
                'creator_id' => $row['creator_id'],
                'creator_type' => $row['creator_type'],
                'creator_name' => $row['creator_name'],
                'creator_surname' => $row['creator_surname'],
                'creator_logo' => $row['creator_logo'], // Add the logo
                'caption' => $formattedCaption, // The caption with bold hashtags
                'created_at' => date("M d, Y h:i A", strtotime($row['created_at'])), // Updated time format
                'created_at_for_sorting' => $row['created_at_for_sorting'], // New field for sorting in "Jan 30, 2020" format
                'media' => [], // Initialize media as an empty array
                // Add canEdit and canDelete flags
                'canEdit' => ($currentUserId === $row['creator_id'] || $currentUserType === 'admin'),
                'canDelete' => ($currentUserId === $row['creator_id'] || $currentUserType === 'admin'),
            ];
            
        }

        // Add media details only if media data is available
        if (!empty($row['media_data'])) {
            $base64Media = 'data:' . $row['mime_type'] . ';base64,' . base64_encode($row['media_data']);
            $announcements[$announcementId]['media'][] = [
                'media_id' => $row['media_id'],
                'data' => $base64Media,
                'type' => $row['media_type'],
                'created_at' => date("M d, Y h:i A", strtotime($row['media_created_at'])), // Updated media creation time format
                'file_size' => $row['file_size'],
                'mime_type' => $row['mime_type']
            ];
            
        }
    }


    // Convert the announcements associative array to a numeric array for JSON response
    echo json_encode(['error' => false, 'data' => array_values($announcements)]);
} catch (PDOException $e) {
    // Catch database errors and return an appropriate JSON response
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
