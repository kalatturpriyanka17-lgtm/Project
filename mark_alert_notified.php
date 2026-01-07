<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $alert_id = $_REQUEST['alert_id'] ?? '';

    if (empty($alert_id)) {
        echo json_encode(["status" => "error", "message" => "Alert ID is required"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE doctor_alerts SET is_notified = 1 WHERE id = :id");
        $stmt->bindParam(':id', $alert_id);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Alert marked as notified"]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
