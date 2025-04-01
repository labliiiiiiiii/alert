<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable error reporting for debugging

header('Content-Type: application/json'); // Ensure the response is JSON

include_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    session_start();

    // Validate if user is logged in
    if (!isset($_SESSION['userid']) || !isset($_SESSION['position'])) {
        echo json_encode([
            'error' => true,
            'message' => 'Unauthorized access.',
        ]);
        exit();
    }

    $userId = $_SESSION['userid']; // Logged-in user ID
    $userPosition = $_SESSION['position']; // User position ('MDRRMO Cainta', 'BRGY Staff')
    $archiveA_id = intval($_GET['id']); // Sanitize the archiveA_id (archived announcement ID)

    try {
        // Fetch the archived announcement details and creator info using CASE for creator
        $stmt = $conn->prepare("
            SELECT 
                archiveA_id, 
                announcement_id, 
                creator_id, 
                creator_type, 
                caption, 
                created_at, 
                updated_at, 
                archived_at,
                -- Use CASE to determine the creator name and logo path
                CASE
                    WHEN creator_type = 'admin' THEN 'MDRRMO Cainta'
                    WHEN creator_type = 'brgyhead' THEN (SELECT BrgyName FROM brgystaffinfotb WHERE userid = creator_id)
                    ELSE 'Unknown'
                END AS creator_name,
                CASE
                    WHEN creator_type = 'admin' THEN '../img/LOGO/MDRRMO1.png'
                    WHEN creator_type = 'brgyhead' THEN (SELECT img FROM brgystaffinfotb WHERE userid = creator_id)
                    ELSE '../img/LOGO/default-logo.png'
                END AS logo_path
            FROM announcementtb_archive 
            WHERE archiveA_id = :archiveA_id
        ");

        $stmt->bindParam(':archiveA_id', $archiveA_id, PDO::PARAM_INT);
        $stmt->execute();
        $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($announcement) {
            // If creator is 'brgyhead' and image is available, encode it to base64
            if ($announcement['creator_type'] === 'brgyhead' && !empty($announcement['logo_path'])) {
                $announcement['logo_path'] = 'data:image/png;base64,' . base64_encode($announcement['logo_path']);
            }

            // Fetch associated media from the archived table
            $mediaStmt = $conn->prepare("
                SELECT 
                    archiveM_id, 
                    media_id, 
                    media_data, 
                    media_type, 
                    mime_type, 
                    file_size, 
                    created_at, 
                    archived_at 
                FROM announcementmedia_archive 
                WHERE archiveA_id = :archiveA_id
            ");
            $mediaStmt->bindParam(':archiveA_id', $archiveA_id, PDO::PARAM_INT);
            $mediaStmt->execute();
            $media = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

            // Encode media_data to Base64 for transfer
            foreach ($media as &$mediaItem) {
                $mediaItem['media_data'] = base64_encode($mediaItem['media_data']);
            }

            // Return the JSON response with all details
            echo json_encode([
                'error' => false,
                'data' => [
                    'announcement' => $announcement,
                    'creator' => [
                        'creator_name' => $announcement['creator_name'],
                        'logo_path' => $announcement['logo_path'],
                    ],
                    'media' => $media,
                ],
            ]);
        } else {
            echo json_encode([
                'error' => true,
                'message' => 'Archived announcement not found.',
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'error' => true,
            'message' => 'An error occurred: ' . $e->getMessage(),
        ]);
    }
} else {
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request. Please provide a valid archiveA_id.',
    ]);
}
?>
