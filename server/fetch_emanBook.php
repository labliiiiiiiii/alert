<?php
header("Content-Type: application/json");
include('../server/connect.php'); // Ensure correct DB connection file

$categoryId = isset($_GET['category']) ? $_GET['category'] : null;

try {
    // Prepare the SQL query
    $sql = "SELECT e.title, e.filepath, c.name AS category_name, e.created_at
            FROM emanual_tb e
            LEFT JOIN categories c ON e.categoryid = c.categoryid";

    if ($categoryId && $categoryId !== 'all_manual') {
        $sql .= " WHERE e.categoryid = :categoryId";
    }

    // Order by created_at in descending order to get the most recent guidebooks first
    $sql .= " ORDER BY e.created_at DESC";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Bind the category ID parameter if it is set
    if ($categoryId && $categoryId !== 'all_manual') {
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
    }

    // Execute the statement
    $stmt->execute();

    // Fetch all the results
    $guidebooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the results as JSON
    if (!empty($guidebooks)) {
        echo json_encode(["success" => true, "guidebooks" => $guidebooks]);
    } else {
        echo json_encode(["success" => false, "message" => "No guidebooks found"]);
    }
} catch (PDOException $e) {
    // Handle any errors
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
