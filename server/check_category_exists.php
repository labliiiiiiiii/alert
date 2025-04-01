<?php
// check_category_exists.php

include_once '../server/connect.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $categoryId = isset($data['id']) ? $data['id'] : null;
    $newCategoryName = isset($data['name']) ? $data['name'] : null;

    // Validate the input
    if (empty($categoryId) || empty($newCategoryName)) {
        echo json_encode(['success' => false, 'message' => 'Category ID and new category name are required.']);
        exit();
    }

    // Check if the new category name already exists, excluding the current category being edited
    $sql = "SELECT COUNT(*) AS category_count FROM categories WHERE name = :name AND categoryid != :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $newCategoryName, PDO::PARAM_STR);
    $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['category_count'] > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
