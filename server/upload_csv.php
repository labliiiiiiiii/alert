<?php
include_once '../server/connect.php';
include_once '../pages/log_activity.php';

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    $csvFile = $_FILES['csvFile'];
    $fileTmpPath = $csvFile['tmp_name'];
    $fileName = $csvFile['name'];
    $fileSize = $csvFile['size'];
    $fileType = $csvFile['type'];
    $fileError = $csvFile['error'];

    // Check for errors
    if ($fileError !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Error uploading file.";
        echo "Error uploading file.";
        exit();
    }

    // Check file type
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    if ($fileExt !== 'csv') {
        $_SESSION['error'] = "Only CSV files are allowed.";
        echo "Only CSV files are allowed.";
        exit();
    }

    // Get barangay ID from POST request
    $barangayId = isset($_POST['barangay_id']) ? $_POST['barangay_id'] : 1;  // Default to 1 if not set

    // Get the user ID from the session
    $added_by = $_SESSION['userid']; // Use the userid from the session

    // Read the CSV file
    $file = fopen($fileTmpPath, 'r');
    $header = fgetcsv($file, 1000, ','); // Read the header row

    while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {
        // Skip rows with incorrect column count
        if (count($data) !== count($header)) {
            continue;
        }

        // Assign values from the CSV to variables and check if any field is empty
        $fullname = !empty($data[0]) ? $data[0] : null;
        $sex = !empty($data[1]) ? $data[1] : null;
        $birthdate = !empty($data[2]) ? $data[2] : null;
        $age = !empty($data[3]) ? $data[3] : null;
        $contact = !empty($data[4]) ? $data[4] : null;
        $province = !empty($data[5]) ? $data[5] : null;
        $municipal = !empty($data[6]) ? $data[6] : null;
        $address = !empty($data[8]) ? $data[8] : null;

        // If any field is empty, skip the row (since all fields are required)
        if (is_null($fullname) || is_null($sex) || is_null($birthdate) || is_null($age) || is_null($contact) || is_null($province) || is_null($municipal) || is_null($address)) {
            continue; // Skip the row if any required field is empty
        }

        // Insert data into the database using PDO
        $sql = "INSERT INTO residentinfo (fullname, sex, birthdate, age, contact, province, municipal, barangay, address, added_by)
                VALUES (:fullname, :sex, :birthdate, :age, :contact, :province, :municipal, :barangay, :address, :added_by)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':fullname' => $fullname,
            ':sex' => $sex,
            ':birthdate' => $birthdate,
            ':age' => $age,
            ':contact' => $contact,
            ':province' => $province,
            ':municipal' => $municipal,
            ':barangay' => $barangayId, // Use the dynamic barangay ID
            ':address' => $address,
            ':added_by' => $added_by // Bind the added_by parameter
        ]);
    }
    fclose($file);

    $_SESSION['success'] = "CSV file uploaded and data saved successfully.";
    echo "CSV file uploaded and data saved successfully.";

    // Log the activity
    $userId = $_SESSION['userid'];  
    $usertype = $_SESSION['usertype'];
    $fullname = $_SESSION['fullName']; 
    $action = "Uploaded a CSV file to add new resident records.";
    logActivity($conn, $userId, $usertype, $fullname, $action);
} else {
    $_SESSION['error'] = "No file uploaded.";
    echo "No file uploaded.";
}
?>
