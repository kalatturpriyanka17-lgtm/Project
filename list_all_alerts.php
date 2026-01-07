<?php
header("Content-Type: application/json");
require_once 'config.php';
try {
    $stmt = $conn->prepare("SELECT * FROM doctor_alerts ORDER BY id DESC LIMIT 50");
    $stmt->execute();
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "count" => count($alerts), "alerts" => $alerts]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
