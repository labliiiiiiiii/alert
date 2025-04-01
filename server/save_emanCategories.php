<?php
session_start();  // Start the session
include_once '../server/connect.php'; // Include your database connection
include_once '../pages/log_activity.php'; // Include the log activity function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $categoryName = $data['name'];

    // Validate the category name
    if (empty($categoryName)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required.']);
        exit();
    }

    // Check if the category already exists
    $sql = "SELECT COUNT(*) AS category_count FROM categories WHERE name = :name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $categoryName);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['category_count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Category already exists.']);
        exit();
    }

    // Get the maximum sort_order value
    $sql = "SELECT MAX(sort_order) AS max_sort_order FROM categories";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $maxSortOrder = $result['max_sort_order'];

    // Calculate the new sort_order value
    $newSortOrder = $maxSortOrder + 1;

    // Insert the new category into the database
    $sql = "INSERT INTO categories (name, sort_order) VALUES (:name, :sort_order)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $categoryName);
    $stmt->bindParam(':sort_order', $newSortOrder);

    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category added successfully.']);

            // Log the activity after adding the category
            $userId = $_SESSION['userid'];  
            $usertype = $_SESSION['usertype'];
            $fullname = $_SESSION['fullName']; 
            $action = "Added a new category to the Emergency Manual.";
            logActivity($conn, $userId, $usertype, $fullname, $action);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add category.']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    // Clean up resources
    $stmt = null;
    $conn = null;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
