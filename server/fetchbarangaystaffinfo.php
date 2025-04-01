<?php
// Include the database connection
include '../server/connect.php';

// Get the current page and entries per page from the request
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Default to page 1
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 5; // Default to 5 entries per page

// Calculate the offset for the SQL query
$offset = ($current_page - 1) * $entries_per_page;

try {
    // Fetch the total number of entries for pagination
    $total_sql = "SELECT COUNT(*) AS total_entries FROM brgystaffinfotb";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->execute();
    $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
    $total_entries = $total_result['total_entries'];

    // Fetch the specific page of barangay staff data with full name
    $sql = "SELECT 
                userid,
                BrgyId, 
                username, 
                BrgyName, 
                CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', surname) AS fullname, 
                email, 
                position, 
                contacts, 
                street, 
                barangay, 
                municipality, 
                province, 
                region, 
                postal_code, 
                img, 
                status 
            FROM brgystaffinfotb 
            LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $barangayStaff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return both the data and pagination information
    return[
        'data' => $barangayStaff,
        'total_entries' => $total_entries,
        'current_page' => $current_page,
        'entries_per_page' => $entries_per_page
    ];
} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => "Error fetching data: " . $e->getMessage()
    ]);
    exit;
}
?>
