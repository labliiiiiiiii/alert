<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'connect.php';
header('Content-Type: application/json');

// Start output buffering to prevent unexpected output breaking the JSON response
ob_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    include_once '../pages/log_activity.php';  // Include the activity logging function

    // Retrieve session variables
    $userId = $_SESSION['userid'] ?? null;
    $userPosition = $_SESSION['position'] ?? null;

    // Retrieve input data
    $announcementId = isset($_POST['announcement_id']) ? intval($_POST['announcement_id']) : null;
    $text = isset($_POST['text']) ? trim($_POST['text']) : null;
    $mediaToDelete = isset($_POST['media_to_delete']) ? $_POST['media_to_delete'] : [];  // List of media IDs to delete

    // Debug: Log session and input data
    error_log("UserID: $userId, Position: $userPosition");
    error_log("Announcement ID: $announcementId, Text: $text, Media to Delete: " . print_r($mediaToDelete, true));

    // Validate user session
    if (!$userId || !$userPosition) {
        ob_end_clean(); // Clear output buffer
        echo json_encode([
            'error' => true,
            'message' => 'Unauthorized access. Please log in.',
        ]);
        exit();
    }

    // Validate inputs
    if (empty($text) || ($announcementId !== null && $announcementId <= 0)) {
        ob_end_clean(); // Clear output buffer
        echo json_encode([
            'error' => true,
            'message' => 'Invalid announcement ID or text. Please ensure both fields are provided.',
        ]);
        exit();
    }

    try {
        // Check if editing or creating
        if ($announcementId) {
            // Validate ownership of announcement
            $stmt = $conn->prepare("SELECT creator_id, creator_type FROM announcementtb WHERE announcement_id = :announcement_id");
            $stmt->bindParam(':announcement_id', $announcementId, PDO::PARAM_INT);
            $stmt->execute();
            $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug: Log the fetched announcement
            error_log("Fetched Announcement for ID $announcementId: " . print_r($announcement, true));

            if (!$announcement) {
                ob_end_clean(); // Clear output buffer
                echo json_encode([
                    'error' => true,
                    'message' => 'Announcement not found.',
                ]);
                exit();
            }

            // Authorization check
            error_log("Authorization Check - User Position: $userPosition, Creator Type: {$announcement['creator_type']}, Creator ID: {$announcement['creator_id']}, User ID: $userId");

            if (!(
                ($userPosition === 'MDRRMO Cainta' && $announcement['creator_type'] === 'admin' && $announcement['creator_id'] == $userId) ||
                ($userPosition === 'BRGY Staff' && $announcement['creator_type'] === 'brgyhead' && $announcement['creator_id'] == $userId)
            )) {
                error_log("Authorization failed for UserID $userId");
                ob_end_clean(); // Clear output buffer
                echo json_encode([
                    'error' => true,
                    'message' => 'You are not authorized to edit this announcement.',
                ]);
                exit();
            }

            // Update announcement text
            $stmt = $conn->prepare("UPDATE announcementtb SET caption = :caption, updated_at = NOW() WHERE announcement_id = :announcement_id");
            $stmt->bindParam(':caption', $text, PDO::PARAM_STR);
            $stmt->bindParam(':announcement_id', $announcementId, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                error_log("SQL Error: " . implode(" ", $stmt->errorInfo()));
                ob_end_clean(); // Clear output buffer
                echo json_encode([
                    'error' => true,
                    'message' => 'Failed to update announcement.',
                ]);
                exit();
            }

            // Delete media if any IDs were provided
            if (!empty($mediaToDelete)) {
                $placeholders = implode(',', array_fill(0, count($mediaToDelete), '?'));
                $stmt = $conn->prepare("DELETE FROM announcement_media WHERE media_id IN ($placeholders) AND announcement_id = :announcement_id");
                $stmt->bindParam(':announcement_id', $announcementId, PDO::PARAM_INT);
                foreach ($mediaToDelete as $index => $mediaId) {
                    $stmt->bindValue(($index + 1), $mediaId, PDO::PARAM_INT);
                }
                if (!$stmt->execute()) {
                    error_log("Failed to delete media: " . implode(" ", $stmt->errorInfo()));
                    ob_end_clean(); // Clear output buffer
                    echo json_encode([
                        'error' => true,
                        'message' => 'Failed to delete selected media.',
                    ]);
                    exit();
                }
            }

            // Log activity for editing announcement
            $userId = $_SESSION['userid'];
            $usertype = $_SESSION['usertype'];
            $fullname = $_SESSION['fullName'];
            $action = "Edited an announcement.";
            logActivity($conn, $userId, $usertype, $fullname, $action);

            ob_end_clean(); // Clear output buffer
            echo json_encode([
                'error' => false,
                'message' => 'Announcement updated successfully.',
            ]);
        } else {
            // Insert new announcement
            $stmt = $conn->prepare("INSERT INTO announcementtb (creator_id, creator_type, caption, created_at) VALUES (:creator_id, :creator_type, :caption, NOW())");
            $stmt->bindParam(':creator_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':creator_type', $userPosition, PDO::PARAM_STR);
            $stmt->bindParam(':caption', $text, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                error_log("Insert failed: " . implode(" ", $stmt->errorInfo()));
                throw new Exception("Failed to create announcement.");
            }
            $announcementId = $conn->lastInsertId();
        }

        // Handle media files (for creating new media)
        if (isset($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
            foreach ($_FILES['media']['tmp_name'] as $key => $tmpName) {
                if (!empty($tmpName)) {
                    $fileType = $_FILES['media']['type'][$key];
                    $fileData = file_get_contents($tmpName);

                    // Validate file type
                    if (!in_array($fileType, ['image/jpeg', 'image/png', 'video/mp4'])) {
                        ob_end_clean(); // Clear output buffer
                        echo json_encode([
                            'error' => true,
                            'message' => 'Invalid media type. Only JPG, PNG, and MP4 are allowed.',
                        ]);
                        exit();
                    }

                    // Insert media
                    $stmt = $conn->prepare("
                        INSERT INTO announcement_media (announcement_id, media_type, media_data, created_at)
                        VALUES (:announcement_id, :media_type, :media_data, NOW())
                    ");
                    $stmt->bindParam(':announcement_id', $announcementId, PDO::PARAM_INT);
                    $stmt->bindParam(':media_type', $fileType === 'video/mp4' ? 'video' : 'image', PDO::PARAM_STR);
                    $stmt->bindParam(':media_data', $fileData, PDO::PARAM_LOB);
                    if (!$stmt->execute()) {
                        error_log("Media insert failed: " . implode(" ", $stmt->errorInfo()));
                        throw new Exception("Failed to upload media.");
                    }
                }
            }
        }

        ob_end_clean(); // Clear output buffer
        echo json_encode([
            'error' => false,
            'message' => $announcementId ? 'Announcement updated successfully.' : 'Announcement created successfully.',
        ]);
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        ob_end_clean(); // Clear output buffer
        echo json_encode([
            'error' => true,
            'message' => 'An error occurred. Please try again.',
        ]);
    }
} else {
    ob_end_clean(); // Clear output buffer
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request method. Only POST requests are allowed.',
    ]);
}
