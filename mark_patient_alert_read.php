<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_username = $_POST['username'] ?? '';

    if (empty($patient_username)) {
        echo json_encode(["status" => "error", "message" => "Username required"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE patient_alerts SET is_read = 1 WHERE patient_username = :pat AND is_read = 0");
        $stmt->bindParam(':pat', $patient_username);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Alerts marked as read"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update alerts"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Db Error: " . $e->getMessage()]);
    }
}
?>
