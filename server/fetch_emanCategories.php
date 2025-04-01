<?php
// Include your connection file
include('connect.php');

try {
    // SQL query to fetch category ids and names from the 'categories' table
    $sql = "SELECT categoryid, name FROM categories ORDER BY sort_order";
    $stmt = $conn->prepare($sql);

    // Execute the query
    $stmt->execute();

    // Fetch the result as an associative array
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are any categories returned
    if ($categories) {
        // Output the categories as JSON
        echo json_encode($categories);
    } else {
        // Output a message if no categories are found
        echo json_encode(array("message" => "No categories found"));
    }
} catch (PDOException $e) {
    // Output any database errors
    echo json_encode(array("error" => "Database error: " . $e->getMessage()));
}
?>
