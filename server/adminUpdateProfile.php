<?php
include("../server/connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_POST['userId'] ?? '';
    $username = $_POST['username'] ?? '';
    $emailAdd = $_POST['emailAdd'] ?? '';
    $position = $_POST['position'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $street = $_POST['street'] ?? '';
    $barangay = $_POST['barangay'] ?? '';

    // Validate required fields
    if (empty($userid) || empty($username) || empty($emailAdd) || empty($position) || empty($contact) || empty($street) || empty($barangay)) {
        echo "Error: All fields are required.";
        exit();
    }

    try {
        if (!empty($_POST['password'])) {
            $password = $_POST['password'];
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $query = $conn->prepare("UPDATE mdrrmoinfotb SET 
                username = :username,
                emailAdd = :emailAdd,
                password = :password_hash,
                position = :position,
                contact = :contact,
                street = :street,
                barangay = :barangay    
                WHERE userId = :userId
            ");
            $query->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
        } else {
            $query = $conn->prepare("UPDATE mdrrmoinfotb SET 
                username = :username,
                emailAdd = :emailAdd,
                position = :position,
                contact = :contact,
                street = :street,
                barangay = :barangay
                WHERE userId = :userId
            ");
        }

        $query->bindParam(':userId', $userid, PDO::PARAM_INT);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':emailAdd', $emailAdd, PDO::PARAM_STR);
        $query->bindParam(':position', $position, PDO::PARAM_STR);
        $query->bindParam(':contact', $contact, PDO::PARAM_STR);
        $query->bindParam(':street', $street, PDO::PARAM_STR);
        $query->bindParam(':barangay', $barangay, PDO::PARAM_STR);

        $query->execute();
        echo "Success: Profile updated successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>