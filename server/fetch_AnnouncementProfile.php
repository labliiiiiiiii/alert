<?php

include_once '../server/connect.php';

session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Assuming the session has been started and the role is saved in $_SESSION['position']
$currentUserId = $_SESSION['userid'] ?? null;
$currentUserType = strtoupper(trim($_SESSION['position'])); // Convert to uppercase for consistency

try {
    // Modify SQL query to handle filtering based on user role
    if ($currentUserType === 'MDRRMO CAINTA') {
        // For admin, show all admin posts (no filtering by barangay)
        $sql = "
            SELECT 
                a.announcement_id, 
                a.creator_id, 
                a.creator_type, 
                a.caption, 
                a.created_at, 
                DATE_FORMAT(a.created_at, '%b %d, %Y') AS created_at_for_sorting,
                m.media_id,
                m.media_data, 
                m.media_type, 
                m.created_at AS media_created_at, 
                m.file_size, 
                m.mime_type,
                
                -- Conditionally set creator name and logo based on creator type
                CASE 
                    WHEN a.creator_type = 'admin' THEN 'MDRRMO Cainta'
                    ELSE 'Unknown Creator'
                END AS creator_name,
                
                CASE 
                    WHEN a.creator_type = 'admin' THEN '../img/LOGO/MDRRMO1.png'
                    ELSE '../img/default_logo.png'
                END AS creator_logo

            FROM 
                announcementtb a
            LEFT JOIN 
                announcement_media m ON a.announcement_id = m.announcement_id
            LEFT JOIN 
                admintb ad ON a.creator_id = ad.userid AND a.creator_type = 'admin'
            WHERE 
                a.creator_type = 'admin'  -- Only show admin posts for MDRRMO Cainta
            ORDER BY 
                a.created_at DESC
        ";
    
    } else if ($currentUserType === 'BRGY STAFF') {
        // For brgyhead, show only posts by the logged-in user
        $sql = "
            SELECT 
                a.announcement_id, 
                a.creator_id, 
                a.creator_type, 
                a.caption, 
                a.created_at, 
                DATE_FORMAT(a.created_at, '%b %d, %Y') AS created_at_for_sorting,
                m.media_id,
                m.media_data, 
                m.media_type, 
                m.created_at AS media_created_at, 
                m.file_size, 
                m.mime_type,
                bsi.BrgyName AS creator_name,
                CASE
                    WHEN bsi.img IS NOT NULL THEN CONCAT('data:image/png;base64,', TO_BASE64(bsi.img))
                    ELSE '../img/default_brgyhead_logo.png'
                END AS creator_logo
            FROM 
                announcementtb a 
            LEFT JOIN 
                announcement_media m ON a.announcement_id = m.announcement_id 
            LEFT JOIN 
                brgystaffinfotb bsi ON a.creator_id = bsi.userid AND a.creator_type = 'brgyhead'
            WHERE 
                a.creator_id = :currentUserId  -- Filter posts by the logged-in user
            ORDER BY 
                a.created_at DESC
        ";
    }

    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);

    if ($currentUserType === 'BRGY STAFF') {
        // For brgyhead, bind currentUserId from the session to filter announcements by logged-in user
        $stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging: Log the raw response before checking for errors
    error_log("SQL Result: " . print_r($rows, true));

    if (empty($rows)) {
        echo json_encode(['error' => false, 'message' => 'No announcements found', 'data' => []]);
        exit;
    }

    // Prepare the announcements data
    $announcements = [];
    foreach ($rows as $row) {
        $announcementId = $row['announcement_id'] ?? 'No ID';
        $creatorId = $row['creator_id'] ?? 'Unknown';
        $creatorType = $row['creator_type'] ?? 'Unknown';
        $creatorName = $row['creator_name'] ?? 'Unknown';
        $creatorLogo = $row['creator_logo'] ?? 'No Logo';

        // Fetch the caption and process it for line breaks and bold hashtags
        $caption = nl2br(htmlspecialchars($row['caption'] ?? 'No caption available'));
        $formattedCaption = preg_replace('/(#\w+)/', '<strong>$1</strong>', $caption);  // Add bold styling to hashtags

        $createdAt = date("M d, Y H:i A", strtotime($row['created_at'] ?? 'Unknown Date'));
        $createdAtForSorting = $row['created_at_for_sorting'] ?? 'Unknown';

        $announcements[$announcementId] = [
            'announcement_id' => $announcementId,
            'creator_id' => $creatorId,
            'creator_type' => $creatorType,
            'creator_name' => $creatorName,
            'creator_logo' => $creatorLogo,
            'caption' => $formattedCaption,  // Assign the processed caption
            'created_at' => date("M d, Y h:i A", strtotime($row['created_at'] ?? 'Unknown Date')),  // Updated to 12-hour format with AM/PM
            'created_at_for_sorting' => $createdAtForSorting,
            'media' => [], // Initialize media array
            'canEdit' => ($currentUserId === $creatorId || $currentUserType === 'MDRRMO CAINTA'),
            'canDelete' => ($currentUserId === $creatorId || $currentUserType === 'MDRRMO CAINTA'),
        ];
        
        // Adding media details with formatted time as well
        if (!empty($row['media_data'])) {
            $base64Media = 'data:' . $row['mime_type'] . ';base64,' . base64_encode($row['media_data']);
            $announcements[$announcementId]['media'][] = [
                'media_id' => $row['media_id'],
                'data' => $base64Media,
                'type' => $row['media_type'],
                'created_at' => date("M d, Y h:i A", strtotime($row['media_created_at'])),  // Updated to 12-hour format with AM/PM
                'file_size' => $row['file_size'],
                'mime_type' => $row['mime_type']
            ];
        
        }
    }

    echo json_encode(['error' => false, 'data' => array_values($announcements)]);

} catch (PDOException $e) {
    error_log("Error fetching announcements: " . $e->getMessage());
    echo json_encode(['error' => true, 'message' => 'Error fetching announcements.']);
}

?>
