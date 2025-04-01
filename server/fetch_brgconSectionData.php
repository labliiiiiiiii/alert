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
    $total_sql = "SELECT COUNT(*) AS total_entries FROM landing_brgcontact_section";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->execute();
    $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
    $total_entries = $total_result['total_entries'];

    // Fetch the specific page of barangay contact data
    $sql = "SELECT id, barangay_name, punong_barangay, contact_number, email, address, logo, created_at, updated_at 
            FROM landing_brgcontact_section 
            LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $barangayContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return both the data and pagination information
    return [
        'data' => $barangayContacts,
        'total_entries' => $total_entries,
        'current_page' => $current_page,
        'entries_per_page' => $entries_per_page
    ];
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>
