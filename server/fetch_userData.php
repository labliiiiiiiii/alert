<?php
// fetch_user_data.php
include '../server/connect.php'; // Database connection file

if (isset($_GET['userId'])) {
    $userId = intval($_GET['userId']); // Sanitize the input

    try {
        // Initialize user data variable
        $user = null;

        // First, check in admin table
        $stmt = $conn->prepare("SELECT * FROM admintb WHERE userid = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If not found in admin table, check in staff table
        if (!$user) {
            $stmt = $conn->prepare("SELECT * FROM brgystaffinfotb WHERE userid = :userId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $user['usertype'] = 'staff'; // Mark as staff
                $user['position'] = 'Staff Member'; // Optional: Add user position
            }
        } else {
            $user['usertype'] = 'admin'; // Mark as admin
            $user['position'] = 'MDRRMO Admin'; // Optional: Add user position
        }

        if ($user) {
            // Mask password for security
            $user['password'] = str_repeat('*', 12);

            // Include email field
            $user['email'] = $user['email'] ?? 'Not provided';  // If email is not available, set a default message

            // If contact information exists, include it
            $user['contacts'] = $user['contacts'] ?? 'No contact information available';

            // Fetch address details (street, barangay, municipality, province, region, postal_code)
            $user['address'] = [
                'street' => $user['street'] ?? 'Not provided',
                'barangay' => $user['barangay'] ?? 'Not provided',
                'municipality' => $user['municipality'] ?? 'Not provided',
                'province' => $user['province'] ?? 'Not provided',
                'region' => $user['region'] ?? 'Not provided', // Include region
                'postal_code' => $user['postal_code'] ?? 'Not provided', // Include postal code
            ];

            // If image exists, encode it to base64
            if (!empty($user['img'])) {
                $user['img'] = base64_encode($user['img']);
            } else {
                $user['img'] = null; // Ensure null if no image
            }

            // Return user data as a JSON response
            echo json_encode($user);
        } else {
            echo json_encode(['error' => 'User not found']);
        }
    } catch (PDOException $e) {
        // Return a JSON error message on database exception
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Return a JSON error message for invalid requests
    echo json_encode(['error' => 'Invalid request']);
}
?>
