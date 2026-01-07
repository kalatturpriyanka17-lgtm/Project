<?php
require_once 'config.php';
try {
    $conn->exec("ALTER TABLE doctor_alerts ADD COLUMN is_notified BOOLEAN DEFAULT FALSE AFTER is_read");
    echo json_encode(["status" => "success", "message" => "Column is_notified added"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
