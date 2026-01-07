<?php
require_once 'config.php';

try {
    // Check if column exists
    $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER health_issues");
        echo json_encode(["status" => "success", "message" => "Column 'profile_image' added successfully"]);
    } else {
        echo json_encode(["status" => "success", "message" => "Column 'profile_image' already exists"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
