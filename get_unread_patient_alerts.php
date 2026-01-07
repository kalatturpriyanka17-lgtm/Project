<?php
header("Content-Type: application/json");
require_once 'config.php';

$patient_username = $_GET['username'] ?? '';

if (empty($patient_username)) {
    echo json_encode(["status" => "error", "message" => "Username required"]);
    exit;
}

try {
    // Count unread alerts for this patient
    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM patient_alerts WHERE patient_username = :pat AND is_read = 0");
    $stmt->bindParam(':pat', $patient_username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Also fetch the alerts themselves if needed (optional for summary but good for consistency)
    $listStmt = $conn->prepare("SELECT * FROM patient_alerts WHERE patient_username = :pat ORDER BY created_at DESC LIMIT 10");
    $listStmt->bindParam(':pat', $patient_username);
    $listStmt->execute();
    $alerts = $listStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "unread_count" => (int)$result['unread_count'],
        "alerts" => $alerts
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Db Error: " . $e->getMessage()]);
}
?>
