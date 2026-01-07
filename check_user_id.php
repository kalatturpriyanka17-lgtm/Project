<?php
header("Content-Type: application/json");
require_once 'config.php';

$patient_id = isset($_GET['id']) ? $_GET['id'] : '';

try {
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = :id");
    $stmt->bindParam(':id', $patient_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "status" => "success", 
        "input_id" => $patient_id,
        "found" => ($user ? true : false),
        "user" => $user
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
