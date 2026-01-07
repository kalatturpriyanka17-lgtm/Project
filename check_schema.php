<?php
header("Content-Type: application/json");
require_once 'config.php';

try {
    $tables = ['users', 'doctor_alerts', 'patient_history', 'hypertension_history', 'fetal_growth_history'];
    $result = [];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("DESCRIBE $table");
        $stmt->execute();
        $result[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode(["status" => "success", "schema" => $result]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
