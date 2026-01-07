<?php
header("Content-Type: application/json");
require_once 'config.php';

$doctor = $_GET['username'] ?? '';
if (empty($doctor)) {
    echo json_encode(["status" => "error", "message" => "Provide username"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM doctor_alerts WHERE doctor_username = :doctor");
    $stmt->bindParam(':doctor', $doctor);
    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $types = [];
    foreach ($alerts as $a) {
        $type = $a['alert_type'] ?? 'unknown';
        $types[$type] = ($types[$type] ?? 0) + 1;
    }
    
    echo json_encode([
        "status" => "success",
        "total" => count($alerts),
        "types" => $types,
        "alerts" => $alerts
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
