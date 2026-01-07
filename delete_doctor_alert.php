<?php
header("Content-Type: application/json");
require_once 'config.php';

$alert_id = $_POST['alert_id'] ?? '';

if (empty($alert_id)) {
    echo json_encode(["status" => "error", "message" => "Alert ID is required"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM doctor_alerts WHERE id = :id");
    $stmt->bindParam(':id', $alert_id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Alert removed"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to remove alert"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
