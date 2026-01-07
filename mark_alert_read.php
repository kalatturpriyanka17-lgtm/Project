<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert_id = $_POST['alert_id'] ?? '';

    if (empty($alert_id)) {
        echo json_encode(["status" => "error", "message" => "Alert ID is required"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE doctor_alerts SET is_read = 1 WHERE id = :id");
        $stmt->bindParam(':id', $alert_id);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Alert marked as read"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update alert"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
