<?php
// Include your database connection
session_start();
include_once '../server/connect.php';
include_once '../pages/log_activity.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the category ID and new category name from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    $categoryId = isset($data['id']) ? $data['id'] : null;
    $newCategoryName = isset($data['name']) ? $data['name'] : null;

    // Validate the category ID and new category name
    if (empty($categoryId) || empty($newCategoryName)) {
        echo json_encode(['success' => false, 'message' => 'Category ID and new category name are required.']);
        exit();
    }

    // Check if the new category name already exists
    $sql = "SELECT COUNT(*) AS category_count FROM categories WHERE name = :name AND categoryid != :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $newCategoryName, PDO::PARAM_STR);
    $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['category_count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Category name already exists.']);
        exit();
    }

    // Update the category name in the database
    $sql = "UPDATE categories SET name = :name WHERE categoryid = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
    $stmt->bindParam(':name', $newCategoryName, PDO::PARAM_STR);

    try {
        if ($stmt->execute()) {
            // Log the activity
            $userId = $_SESSION['userid'];  
            $usertype = $_SESSION['usertype'];
            $fullname = $_SESSION['fullName']; 
            $action = "Edited Emergency Manual categories.";
            logActivity($conn, $userId, $usertype, $fullname, $action);
            echo json_encode(['success' => true, 'message' => 'Category updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update category.']);
        }
    } catch (PDOException $e) {
        error_log('Error updating category: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    // Close the statement and connection
    $stmt = null;
    $conn = null;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
