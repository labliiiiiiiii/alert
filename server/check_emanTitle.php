<?php
// Include the database connection file
include_once '../server/connect.php';

// Get the title from the request body
$data = json_decode(file_get_contents('php://input'), true);
$title = isset($data['title']) ? $data['title'] : '';

// Check if the title is not empty
if (empty($title)) {
    echo json_encode(['exists' => false, 'message' => 'Title is required']);
    exit();
}

try {
    // Prepare and execute the SQL query to check if the title exists
    $sql = "SELECT COUNT(*) as count FROM emanual_tb WHERE title = :title";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check the count of rows with the given title
    if ($result['count'] > 0) {
        echo json_encode(['exists' => true, 'message' => 'Title already exists']);
    } else {
        echo json_encode(['exists' => false, 'message' => 'Title is available']);
    }

} catch (PDOException $e) {
    echo json_encode(['exists' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
