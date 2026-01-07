<?php
header("Content-Type: application/json");
require_once 'config.php';
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM doctor_alerts");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $conn->prepare("SELECT doctor_username, COUNT(*) as count FROM doctor_alerts GROUP BY doctor_username");
    $stmt->execute();
    $by_doctor = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "total_overall" => $total, "by_doctor" => $by_doctor]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
