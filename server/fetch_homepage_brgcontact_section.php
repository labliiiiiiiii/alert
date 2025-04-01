<?php
// Start session and include database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../server/connect.php';

header('Content-Type: application/json'); // Set content type to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch the data from the form
    $barangay_name = trim($_POST['barangay_name'] ?? '');
    $punong_barangay = trim($_POST['punong_barangay'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Handle logo file upload
    $logo = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logo = file_get_contents($_FILES['logo']['tmp_name']);
    }

    // Validate inputs: ensure no fields are empty
    if (empty($barangay_name) || empty($punong_barangay) || empty($contact_number) || empty($email) || empty($address)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: your-form-page.php'); // Redirect back to the form page
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format.';
        header('Location: your-form-page.php'); // Redirect back to the form page
        exit();
    }

    try {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Handle update if ID is provided
            $id = $_POST['id'];

            $sql = "UPDATE landing_brgcontact_section
                    SET barangay_name = :barangay_name,
                        punong_barangay = :punong_barangay,
                        contact_number = :contact_number,
                        email = :email,
                        address = :address";
            if ($logo) {
                $sql .= ", logo = :logo";
            }
            $sql .= " WHERE id = :id";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':barangay_name', $barangay_name, PDO::PARAM_STR);
            $stmt->bindParam(':punong_barangay', $punong_barangay, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $contact_number, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($logo) {
                $stmt->bindParam(':logo', $logo, PDO::PARAM_LOB);
            }

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Record updated successfully.';
            } else {
                $_SESSION['error'] = 'Error updating record.';
            }
        } else {
            // Handle insert if ID is not provided
            $sql = "INSERT INTO landing_brgcontact_section (barangay_name, punong_barangay, contact_number, email, address, logo)
                    VALUES (:barangay_name, :punong_barangay, :contact_number, :email, :address, :logo)";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':barangay_name', $barangay_name, PDO::PARAM_STR);
            $stmt->bindParam(':punong_barangay', $punong_barangay, PDO::PARAM_STR);
            $stmt->bindParam(':contact_number', $contact_number, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            if ($logo) {
                $stmt->bindParam(':logo', $logo, PDO::PARAM_LOB);
            } else {
                $stmt->bindValue(':logo', null, PDO::PARAM_NULL); // Set logo to NULL if not uploaded
            }

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Record added successfully.';
            } else {
                $_SESSION['error'] = 'Error adding record.';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }

    header('Location: ../pages/settingslandingCAINTACONTACTLIST.php'); // Redirect back to the form page
    exit();
}

?>
