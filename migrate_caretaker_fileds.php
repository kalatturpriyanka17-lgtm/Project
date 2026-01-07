<?php
header("Content-Type: application/json");
require_once 'config.php';

try {
    // Check if columns exist first by trying to SELECT one
    $check = $conn->query("SELECT caretaker_name FROM users LIMIT 1");
    if ($check !== false) {
        echo json_encode(["status" => "success", "message" => "Columns already exist."]);
        exit;
    }
} catch (Exception $e) {
    // Column doesn't exist, proceed to alter
}

try {
    $sql = "ALTER TABLE users 
            ADD COLUMN caretaker_name VARCHAR(100) DEFAULT '',
            ADD COLUMN caretaker_relationship VARCHAR(50) DEFAULT '',
            ADD COLUMN caretaker_mobile VARCHAR(15) DEFAULT ''";
    
    $conn->exec($sql);
    echo json_encode(["status" => "success", "message" => "Caretaker columns added successfully."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Migration failed: " . $e->getMessage()]);
}
?>
