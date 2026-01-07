<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $gestational_age = $_POST['gestational_age'] ?? 0;
    $fetal_weight = $_POST['fetal_weight'] ?? 0;
    $severity = $_POST['severity'] ?? '';

    if (empty($username)) {
        echo json_encode(["status" => "error", "message" => "Missing username"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO fetal_growth_history (username, gestational_age, fetal_weight, severity) VALUES (:username, :ga, :fw, :severity)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':ga', $gestational_age);
        $stmt->bindParam(':fw', $fetal_weight);
        $stmt->bindParam(':severity', $severity);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Record saved successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save record"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
