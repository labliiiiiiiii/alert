<?php
session_start();  // Start the session

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if session variables are set
if (!isset($_SESSION['userid']) || !isset($_SESSION['position'])) {
    $_SESSION['error'] = 'Session variables are not set!';
    header('Location: your_page.php'); // Redirect to your page
    exit();
}

include_once '../server/connect.php';  // Include the database connection

header('Content-Type: application/json');  // Set header for JSON response

// Function to return JSON response
function returnJson($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();  // Ensure nothing is output after this
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnJson(false, 'Invalid request method.');
}

// Check if the required fields are set
if (!isset($_POST['manualTitle'], $_POST['manualCategory'], $_FILES['fileUpload'])) {
    returnJson(false, 'Missing required fields.');
}

$manualTitle = $_POST['manualTitle'];
$manualCategory = $_POST['manualCategory'];
$file = $_FILES['fileUpload'];

// Validate the file upload
$allowedMimeTypes = ['application/pdf'];
$fileMimeType = mime_content_type($file['tmp_name']);

if (!in_array($fileMimeType, $allowedMimeTypes)) {
    returnJson(false, 'Invalid file type. Only PDF files are allowed.');
}

// Define the upload directory and file path
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$fileName = basename($file['name']);
$filePath = $uploadDir . $fileName;

// Move the uploaded file to the upload directory
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    returnJson(false, 'Failed to move the uploaded file.');
}

// Ensure session variables are initialized
if (!isset($_SESSION['userid'], $_SESSION['position'])) {
    returnJson(false, 'User session is not set.');
}

$creatorId = $_SESSION['userid'];  // User ID from the session
$creatorType = $_SESSION['position'] === 'MDRRMO Cainta' ? 'admin' : ($_SESSION['position'] === 'BRGY Staff' ? 'brgyhead' : 'unknown');
$uploadedAt = date('Y-m-d H:i:s');

// Insert the data into the database using PDO (using $conn here)
try {
    $query = "INSERT INTO emanual_tb (categoryid, creator_id, creator_type, title, filepath, created_at)
              VALUES (:categoryid, :creator_id, :creator_type, :title, :filepath, :created_at)";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':categoryid' => $manualCategory,
        ':creator_id' => $creatorId,
        ':creator_type' => $creatorType,
        ':title' => $manualTitle,
        ':filepath' => $filePath,
        ':created_at' => $uploadedAt
    ]);

    $_SESSION['success'] = 'Manual uploaded successfully.';
    returnJson(true, 'Manual uploaded successfully.');
} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to insert data into the database: ' . $e->getMessage();
    returnJson(false, 'Failed to insert data into the database: ' . $e->getMessage());
} catch (Exception $e) {
    $_SESSION['error'] = 'An unexpected error occurred: ' . $e->getMessage();
    returnJson(false, 'An unexpected error occurred: ' . $e->getMessage());
}
?>
