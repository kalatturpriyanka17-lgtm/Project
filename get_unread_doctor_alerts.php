<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_username = $_POST['doctor_username'] ?? '';

    if (empty($doctor_username)) {
        echo json_encode(["status" => "error", "message" => "Doctor username is required"]);
        exit;
    }

    try {
        // Fetch only UNNOTIFIED alerts
        $stmt = $conn->prepare("SELECT * FROM doctor_alerts WHERE doctor_username = :doctor AND is_notified = 0 ORDER BY timestamp DESC");
        $stmt->bindParam(':doctor', $doctor_username);
        $stmt->execute();
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "alerts" => $alerts]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}
?>
