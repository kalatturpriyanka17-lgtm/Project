<?php
header("Content-Type: application/json");
require_once 'config.php';

$username = $_GET['username'] ?? '';

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Missing username"]);
    exit;
}

try {
    // Fetch last 10 records for the graph, ordered by date
    $stmt = $conn->prepare("SELECT systolic, diastolic, severity, created_at FROM hypertension_records WHERE username = :u ORDER BY created_at ASC LIMIT 20");
    $stmt->bindParam(':u', $username);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "history" => $history]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
}
?>
