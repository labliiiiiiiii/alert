<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'connect.php'; // Include your database connection file
include_once '../pages/log_activity.php';
header('Content-Type: application/json'); // Ensure the response is JSON

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session if it's not already started
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is authenticated
    if (!isset($_SESSION['userid'], $_SESSION['position'])) {
        echo json_encode(['error' => true, 'message' => 'Unauthorized access.']);
        exit();
    }

    // Retrieve session variables and form data
    $creator_id = $_SESSION['userid']; // User ID from the session
    $creator_type = $_SESSION['position'] === 'MDRRMO Cainta' ? 'admin' : ($_SESSION['position'] === 'BRGY Staff' ? 'brgyhead' : 'unknown');
    $caption = trim($_POST['text'] ?? ''); // Text from the form
    $mediaFiles = $_FILES['media'] ?? []; // Files from the form

    // Validate the user's creator type
    if ($creator_type === 'unknown') {
        echo json_encode(['error' => true, 'message' => 'Invalid creator type.']);
        exit();
    }

    // Check if either caption or media is provided
    $isMediaUploaded = !empty($mediaFiles['tmp_name'][0]);
    if (empty($caption) && !$isMediaUploaded) {
        echo json_encode(['error' => true, 'message' => 'Caption or media must be provided.']);
        exit();
    }

    try {
        // Begin a database transaction
        $conn->beginTransaction();

        // Insert the announcement into the `announcementtb` table
        $stmt = $conn->prepare("
            INSERT INTO announcementtb (creator_id, creator_type, caption)
            VALUES (:creator_id, :creator_type, :caption)
        ");
        $stmt->execute([
            ':creator_id' => $creator_id,
            ':creator_type' => $creator_type,
            ':caption' => $caption,
        ]);

        // Get the ID of the newly inserted announcement
        $announcement_id = $conn->lastInsertId();

        // Handle file uploads if there are any files
        if ($isMediaUploaded) {
            foreach ($mediaFiles['tmp_name'] as $index => $tmpName) {
                // Check if the temporary file exists
                if (!file_exists($tmpName)) {
                    error_log("Temporary file not found: $tmpName");
                    continue;
                }

                $fileName = basename($mediaFiles['name'][$index]);
                $fileSize = $mediaFiles['size'][$index];
                $mimeType = $mediaFiles['type'][$index];
                $mediaType = strpos($mimeType, 'image/') !== false ? 'image' : 'video';

                // Validate file size and type
                $maxFileSize = 500 * 1024 * 1024; // 500 MB limit
                if ($fileSize > $maxFileSize) {
                    error_log("File too large: $fileName ($fileSize bytes)");
                    continue;
                }

                // Allow more video MIME types
                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png',
                    'video/mp4',
                    'video/mpeg',
                    'video/avi',
                    'video/quicktime',
                    'video/x-ms-wmv',
                    'video/x-msvideo',
                    'video/x-flv',
                    'video/webm',
                    'video/ogg'
                ];
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    error_log("Invalid file type: $mimeType for file $fileName");
                    continue;
                }

                // Read file content as binary
                $fileData = file_get_contents($tmpName);
                if ($fileData === false) {
                    error_log("Failed to read file content for: $fileName");
                    continue;
                }

                // Insert into database
                $mediaStmt = $conn->prepare("
                    INSERT INTO announcement_media (announcement_id, media_data, media_type, file_size, mime_type)
                    VALUES (:announcement_id, :media_data, :media_type, :file_size, :mime_type)
                ");

                if (!$mediaStmt->execute([
                    ':announcement_id' => $announcement_id,
                    ':media_data' => $fileData,
                    ':media_type' => $mediaType,
                    ':file_size' => $fileSize,
                    ':mime_type' => $mimeType,
                ])) {
                    error_log('Database Insert Error: ' . print_r($mediaStmt->errorInfo(), true));
                } else {
                    error_log("Media inserted successfully for file: $fileName");
                }
            }
        }

        // Commit the transaction
        $conn->commit();

        echo json_encode(['error' => false, 'message' => 'Announcement posted successfully!']);

        // Log the activity
        $userId = $_SESSION['userid'];
        $usertype = $_SESSION['usertype'];
        $fullname = $_SESSION['fullName'];
        $action = "Published a new announcement.";
        logActivity($conn, $userId, $usertype, $fullname, $action);

    } catch (Exception $e) {
        // Roll back the transaction in case of an error
        $conn->rollBack();
        echo json_encode(['error' => true, 'message' => 'Error posting announcement: ' . $e->getMessage()]);
    }
} else {
    // Handle invalid request methods
    echo json_encode(['error' => true, 'message' => 'Invalid request method.']);
}
?>
