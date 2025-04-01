<?php
// update_userData.php
include '../server/connect.php'; // Include your database connection file
include_once '../pages/log_activity.php'; // Include activity log functionality

header('Content-Type: text/plain'); // Ensure plain text response

// Start session for setting success/error messages
session_start();

// Check for POST request with user data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userId'])) {
    $userId = intval($_POST['userId']);
    $username = $_POST['username'] ?? null;
    $firstname = $_POST['firstname'] ?? null;
    $middlename = $_POST['middlename'] ?? null; // Optional field
    $surname = $_POST['surname'] ?? null;
    $contacts = $_POST['contacts'] ?? null;
    $email = $_POST['email'] ?? null;
    $street = $_POST['street'] ?? null;
    $barangay = $_POST['barangay'] ?? null;
    $municipality = $_POST['municipality'] ?? null;
    $province = $_POST['province'] ?? null;
    $region = $_POST['region'] ?? null;
    $postal_code = $_POST['postal_code'] ?? null;

    // Validate required fields (exclude middlename from this list)
    $requiredFields = [
        'username', 'firstname', 'surname', 'contacts',
        'email', 'street', 'barangay', 'municipality',
        'province', 'region', 'postal_code'
    ];

    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        // If any required fields are missing, log and return an error response
        error_log("Missing Fields: " . implode(', ', $missingFields));
        echo "Error: All fields required. Missing: " . implode(', ', $missingFields);
        exit;
    }

    // Handle file upload for logo (profile picture)
    $logoData = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logoData = file_get_contents($_FILES['logo']['tmp_name']);
    }

    if (!$userId) {
        error_log('Error: Missing or invalid userId.');
        echo "Error: Missing userId in the request.";
        exit;
    }

    error_log("Received userId: $userId"); // Log the received userId

    try {
        // Check if the email is unique in both tables (admintb and brgystaffinfotb)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admintb WHERE email = :email AND userid != :userId");
        $stmt->execute(['email' => $email, 'userId' => $userId]);
        $adminEmailExists = $stmt->fetchColumn();

        if ($adminEmailExists > 0) {
            echo "Error: This email is already in use in the admin table.";
            exit;
        }

        $stmt = $conn->prepare("SELECT COUNT(*) FROM brgystaffinfotb WHERE email = :email AND userid != :userId");
        $stmt->execute(['email' => $email, 'userId' => $userId]);
        $staffEmailExists = $stmt->fetchColumn();

        if ($staffEmailExists > 0) {
            echo "Error: This email is already in use in the staff table.";
            exit;
        }

        // Determine if the user is admin or staff
        $stmt = $conn->prepare("SELECT * FROM admintb WHERE userid = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Build SQL based on whether a new logo is uploaded
        if ($user) {
            $sql = "UPDATE admintb SET 
                        username = :username,
                        firstname = :firstname,
                        middlename = :middlename,
                        surname = :surname,
                        contacts = :contacts,
                        email = :email,
                        street = :street,
                        barangay = :barangay,
                        municipality = :municipality,
                        province = :province,
                        region = :region,
                        postal_code = :postal_code,
                        updated_at = CURRENT_TIMESTAMP";
            if ($logoData) {
                $sql .= ", img = :img";
            }
            $sql .= " WHERE userid = :userId";
        } else {
            $sql = "UPDATE brgystaffinfotb SET 
                        username = :username,
                        firstname = :firstname,
                        middlename = :middlename,
                        surname = :surname,
                        contacts = :contacts,
                        email = :email,
                        street = :street,
                        barangay = :barangay,
                        municipality = :municipality,
                        province = :province,
                        region = :region,
                        postal_code = :postal_code,
                        updated_at = CURRENT_TIMESTAMP";
            if ($logoData) {
                $sql .= ", img = :img";
            }
            $sql .= " WHERE userid = :userId";
        }

        // Prepare the SQL statement for updating
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':middlename', $middlename); // Bind even if optional
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':contacts', $contacts);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':street', $street);
        $stmt->bindParam(':barangay', $barangay);
        $stmt->bindParam(':municipality', $municipality);
        $stmt->bindParam(':province', $province);
        $stmt->bindParam(':region', $region);
        $stmt->bindParam(':postal_code', $postal_code);
        $stmt->bindParam(':userId', $userId);

        if ($logoData) {
            $stmt->bindParam(':img', $logoData, PDO::PARAM_LOB);
        }

        // Execute the query
        if ($stmt->execute()) {
            // Update session with the new full name
            $fullName = trim("$firstname $middlename $surname");
            $_SESSION['fullName'] = $fullName;

            // Update session image if a new logo is uploaded
            if ($logoData) {
                $_SESSION['img'] = base64_encode($logoData); // Save the image as base64 in session
            }

            // Log the activity
            $userId = $_SESSION['userid'];  
            $usertype = $_SESSION['usertype'];
            $fullname = $_SESSION['fullName']; 
            $action = "Updated user information.";
            logActivity($conn, $userId, $usertype, $fullname, $action);

            echo "Success: User profile updated successfully.";
        } else {
            // Error handling
            error_log('Error: Failed to execute SQL update.');
            echo "Error: Failed to update user profile.";
        }
    } catch (PDOException $e) {
        // Return server error response
        error_log('PDO Exception: ' . $e->getMessage());
        echo "Error: A server error occurred: " . $e->getMessage();
    }
} else {
    error_log('Error: Invalid request or userId not provided.');
    echo "Error: Invalid request: No userId provided.";
    exit;
}
?>
