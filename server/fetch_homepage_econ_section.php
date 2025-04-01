<?php
include_once 'connect.php'; // Include your database connection file

// Fetch emergency contact information from the database
try {
    $stmt = $conn->prepare("
        SELECT
            emergency_id,
            contact_number,
            description,
            contact_number_2,
            description_2,
            created_at,
            updated_at
        FROM landing_emergencycon_section
    ");
    $stmt->execute();
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    echo json_encode(['success' => true, 'data' => $contacts]);
} catch (PDOException $e) {
    // Return an error message as JSON
    echo json_encode(['success' => false, 'message' => 'Error fetching emergency contacts: ' . $e->getMessage()]);
}
?>
