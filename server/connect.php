<?php
// Database credentials
$servername = "localhost";
$username = "u280323898_ewan";
$password = "Ewankonalang@12345";
$database = "u280323898_ewan";

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$database;charset=utf8", $username, $password);
    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Test query
    $sql = "SELECT DATABASE()";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
