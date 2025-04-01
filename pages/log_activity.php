<?php
include '../server/connect.php';

/*function logActivity($conn, $userId, $userType, $username, $action) {
    try {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, username, action, log_time) 
                                VALUES (:user_id, :user_type, :username, :action, NOW())");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':user_type', $userType, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Activity Log Error: " . $e->getMessage()); // Logs error for debugging
    }
}*/


function logActivity($conn, $user_id, $usertype, $fullname, $action) {
    $sql = "INSERT INTO activity_logs (user_id, user_type, fullname, action) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $usertype, $fullname, $action]);
}