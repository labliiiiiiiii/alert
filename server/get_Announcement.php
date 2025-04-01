<?php
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
    $announcementId = intval($_GET['id']); // Sanitize the announcement ID

    try {
        // Fetch the announcement details
        $stmt = $conn->prepare("
            SELECT
                announcement_id,
                creator_id,
                creator_type,
                caption,
                created_at,
                updated_at
            FROM announcementtb
            WHERE announcement_id = :announcement_id
        ");
        $stmt->bindParam(':announcement_id', $announcementId, PDO::PARAM_INT);
        $stmt->execute();
        $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($announcement) {
            // Authorization check
            if (
                ($userPosition === 'MDRRMO Cainta' && $announcement['creator_type'] === 'admin') || // Admin can edit all admin posts
                ($userPosition === 'BRGY Staff' && $announcement['creator_type'] === 'brgyhead' && $announcement['creator_id'] == $userId) // Brgy staff can only edit their own posts
            ) {
                // Fetch associated media
                $mediaStmt = $conn->prepare("
                    SELECT
                        media_id,
                        media_data,
                        media_type,
                        mime_type,
                        file_size,
                        created_at
                    FROM announcement_media
                    WHERE announcement_id = :announcement_id
                ");
                $mediaStmt->bindParam(':announcement_id', $announcementId, PDO::PARAM_INT);
                $mediaStmt->execute();
                $media = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

                // Encode media_data to Base64 for transfer
                foreach ($media as &$mediaItem) {
                    $mediaItem['media_data'] = base64_encode($mediaItem['media_data']);
                }

                echo json_encode([
                    'error' => false,
                    'data' => [
                        'announcement' => $announcement,
                        'media' => $media,
                    ],
                ]);
            } else {
                echo json_encode([
                    'error' => true,
                    'message' => 'You are not authorized to edit this announcement.',
                ]);
            }
        } else {
            echo json_encode([
                'error' => true,
                'message' => 'Announcement not found.',
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
        'message' => 'Invalid request. Please provide a valid announcement ID.',
    ]);
}
?>
