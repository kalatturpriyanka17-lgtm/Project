<?php
header("Content-Type: application/json");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_username = $_REQUEST['doctor_username'] ?? '';

    if (empty($doctor_username)) {
        echo json_encode(["status" => "error", "message" => "Doctor username is required"]);
        exit;
    }

    try {
        // Fetch alerts for this doctor, ordered by newest first
        $stmt = $conn->prepare("SELECT * FROM doctor_alerts WHERE doctor_username = :doctor ORDER BY timestamp DESC");
        $stmt->bindParam(':doctor', $doctor_username);
        $stmt->execute();
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "alerts" => $alerts]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
